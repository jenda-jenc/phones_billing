<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceBreakdownMail;
use App\Mail\InvoiceDebtorsSummaryMail;
use App\Models\Invoice;
use App\Models\InvoicePerson;
use App\Models\User;
use http\Env;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $perPage = (int) $request->integer('per_page', 10);

        $paginator = Invoice::query()
            ->with([
                'people.person.groups',
                'people.person.phones',
                'people.person.users',
                'people.lines' => fn ($query) => $query
                    ->select('id', 'invoice_person_id', 'price_without_vat', 'price_with_vat'),
            ])
            ->orderByDesc('billing_period')
            ->orderByDesc('created_at')
            ->paginate($perPage > 0 ? $perPage : 10)
            ->withQueryString()
            ->through(fn (Invoice $invoice) => $this->transformInvoiceForUser($invoice, $user));

        return Inertia::render('Dashboard', [
            'invoices' => $paginator,
            'filters' => [
                'per_page' => $perPage,
                'role' => data_get($user, 'role'),
            ],
        ]);
    }

    public function download(Request $request, Invoice $invoice, string $format): StreamedResponse
    {
        $format = strtolower($format);

        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $invoice->loadMissing(['people.person.groups', 'people.person.phones', 'people.lines']);

        /** @var User|null $user */
        $user = $request->user();
        $filteredPeople = $this->filterPeopleForRole($invoice->people, data_get($user, 'role'));
        $invoice->setRelation('people', $filteredPeople);

        $path = sprintf('invoices/%d.%s', $invoice->id, $format);
        $filename = $this->buildDownloadFileName($invoice, $format);

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, $filename);
        }

        return Response::streamDownload(function () use ($invoice, $format) {
            if ($format === 'csv') {
                $this->streamCsv($invoice);

                return;
            }

            echo $this->generateSimplePdf($invoice);
        }, $filename, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/pdf',
        ]);
    }

    public function show(Request $request, InvoicePerson $invoicePerson): InertiaResponse
    {
        $this->authorize('view', $invoicePerson);

        $invoicePerson->loadMissing(['invoice', 'person.groups', 'person.phones', 'lines']);

        return Inertia::render('Invoices/Show', [
            'invoicePerson' => $this->transformInvoicePerson($invoicePerson),
        ]);
    }

    public function email(Request $request, InvoicePerson $invoicePerson): JsonResponse
    {
//        $this->authorize('email', $invoicePerson);

        $invoicePerson->loadMissing(['invoice', 'person.users', 'lines']);

        $validated = $request->validate([
            'email' => ['nullable', 'string', 'email'],
            'name_email' => ['nullable', 'string', 'email'],
        ]);
        $recipientEmail = null;

        if (array_key_exists('email', $validated)) {
            $candidate = trim((string) $validated['email']);

            if ($candidate !== '') {
                $recipientEmail = Str::lower($candidate);
            }
        }

        if ($recipientEmail === null) {
            $recipientEmail = optional($invoicePerson->person)
                ?->users
                ->first()
                ?->email;

            if (empty($recipientEmail)) {
                $message = 'K této osobě není přiřazen žádný uživatel.';

                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'email' => [$message],
                    ],
                ], 422);
            }
        }

        if (getenv('APP_ENV') !== 'production' && $recipientEmail !== getenv('TEST_EMAIL')){
            return response()->json([
                'message' => 'E-mail '.$recipientEmail.' nebyl odeslán(dev)',
                'email' => $recipientEmail,
            ]);
        }

        try {
            Mail::to($recipientEmail)->sendNow(new InvoiceBreakdownMail($invoicePerson));
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'E-mail s vyúčtováním byl odeslán.',
                'email' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'E-mail s vyúčtováním byl odeslán.',
            'email' => $recipientEmail,
        ]);
    }

    public function emailDebtors(Request $request, Invoice $invoice): JsonResponse
    {
//        abort_unless(
//            Gate::forUser($request->user())->allows('viewAny', InvoicePerson::class),
//            403
//        );

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $recipientEmail = Str::lower(trim((string) $validated['email']));

        $invoice->loadMissing([
            'people.person',
            'people.person.phones',
            'people.lines',
        ]);

        $debtors = $invoice->people
            ->filter(fn (InvoicePerson $invoicePerson) => (float) $invoicePerson->payable > 0)
            ->values();

        if ($debtors->isEmpty()) {
            $message = 'Vyúčtování neobsahuje žádné dlužníky k odeslání.';

            return response()->json([
                'message' => $message,
                'errors' => [
                    'email' => [$message],
                ],
            ], 422);
        }

        $invoice->setRelation('people', $debtors);

        Mail::to($recipientEmail)->sendNow(new InvoiceDebtorsSummaryMail($invoice));

        return response()->json([
            'message' => 'Souhrnný e-mail dlužníků byl odeslán.',
            'email' => $recipientEmail,
            'count' => $debtors->count(),
        ]);
    }

    private function transformInvoiceForUser(Invoice $invoice, ?User $user): array
    {
        $people = $this->filterPeopleForRole($invoice->people, data_get($user, 'role'));

        $lineCount = $people->reduce(
            fn (int $carry, InvoicePerson $invoicePerson) => $carry + $invoicePerson->lines->count(),
            0
        );

        $peopleLinks = [];
        $detailUrl = null;

        if ($user !== null) {
            $matchingPerson = $people->first(function (InvoicePerson $invoicePerson) use ($user) {
                $person = $invoicePerson->person;

                if ($person === null) {
                    return false;
                }

                return $person->users->contains(fn ($relatedUser) => (int) $relatedUser->id === (int) $user->id);
            });

            if ($matchingPerson !== null) {
                $detailUrl = route('invoices.show', ['invoicePerson' => $matchingPerson->id]);
            }

            if (Gate::forUser($user)->allows('viewAny', InvoicePerson::class)) {
                $peopleLinks = $invoice->people
                    ->map(function (InvoicePerson $invoicePerson) {
                        $person = $invoicePerson->person;
                        $name = optional($person)->name;

                        if (empty($name)) {
                            $name = $invoicePerson->phone ?: sprintf('Osoba #%d', $invoicePerson->id);
                        }

                        return [
                            'id' => $invoicePerson->id,
                            'name' => $name,
                            'detail_url' => route('invoices.show', ['invoicePerson' => $invoicePerson->id]),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        return [
            'id' => $invoice->id,
            'source_filename' => $invoice->source_filename,
            'billing_period' => $invoice->billing_period,
            'billing_period_label' => $this->formatBillingPeriod($invoice->billing_period),
            'provider' => $invoice->provider,
            'provider_label' => $invoice->provider_label,
            'row_count' => $invoice->row_count,
            'created_at' => optional($invoice->created_at)->toDateTimeString(),
            'people_count' => $people->count(),
            'line_count' => $lineCount,
            'totals' => [
                'without_vat' => round($people->sum('total_without_vat'), 2),
                'with_vat' => round($people->sum('total_with_vat'), 2),
                'limit' => round($people->sum('limit'), 2),
                'payable' => round($people->sum('payable'), 2),
            ],
            'downloads' => [
                'csv' => route('invoices.download', ['invoice' => $invoice->id, 'format' => 'csv']),
                'pdf' => route('invoices.download', ['invoice' => $invoice->id, 'format' => 'pdf']),
            ],
            'detail_url' => $detailUrl,
            'people_links' => $peopleLinks,
        ];
    }

    private function transformInvoicePerson(InvoicePerson $invoicePerson): array
    {
        $person = $invoicePerson->person;
        $invoice = $invoicePerson->invoice;

        return [
            'id' => $invoicePerson->id,
            'phone' => $invoicePerson->phone,
            'vat_rate' => $invoicePerson->vat_rate,
            'total_without_vat' => round((float) $invoicePerson->total_without_vat, 2),
            'total_with_vat' => round((float) $invoicePerson->total_with_vat, 2),
            'limit' => round((float) $invoicePerson->limit, 2),
            'payable' => round((float) $invoicePerson->payable, 2),
            'applied_rules' => $invoicePerson->applied_rules ?? [],
            'default_email' => $this->buildDefaultEmailFromName(optional($person)->name),
            'person' => $person ? [
                'id' => $person->id,
                'name' => $person->name,
                'department' => $person->department,
                'phones' => $person->phones
                    ->map(fn ($phone) => [
                        'id' => $phone->id,
                        'phone' => $phone->phone,
                        'limit' => $phone->limit !== null
                            ? round((float) $phone->limit, 2)
                            : null,
                    ])->all(),
                'groups' => $person->groups
                    ->map(fn ($group) => [
                        'id' => $group->id,
                        'name' => $group->name,
                        'value' => $group->value,
                    ])->all(),
            ] : null,
            'invoice' => $invoice ? [
                'id' => $invoice->id,
                'source_filename' => $invoice->source_filename,
                'billing_period' => $invoice->billing_period,
                'billing_period_label' => $this->formatBillingPeriod($invoice->billing_period),
                'provider' => $invoice->provider,
                'provider_label' => $invoice->provider_label,
                'created_at' => optional($invoice->created_at)->toDateTimeString(),
            ] : null,
            'lines' => $invoicePerson->lines
                ->map(fn ($line) => [
                    'id' => $line->id,
                    'group_name' => $line->group_name,
                    'tariff' => $line->tariff,
                    'service_name' => $line->service_name,
                    'price_without_vat' => round((float) $line->price_without_vat, 2),
                    'price_with_vat' => round((float) $line->price_with_vat, 2),
                ])->all(),
        ];
    }

    private function formatBillingPeriod(?string $billingPeriod): ?string
    {
        if (empty($billingPeriod)) {
            return null;
        }

        try {
            $period = Carbon::createFromFormat('Y-m', $billingPeriod)->startOfMonth();
        } catch (\Exception $exception) {
            return $billingPeriod;
        }

        return $period
            ->locale(app()->getLocale())
            ->translatedFormat('F Y');
    }

    private function filterPeopleForRole(Collection $people, $role): Collection
    {
        if (empty($role) || $role === 'admin') {
            return $people;
        }

        return $people->filter(function (InvoicePerson $invoicePerson) use ($role) {
            $person = $invoicePerson->person;

            if ($person === null) {
                return false;
            }

            return $person->groups->contains(fn ($group) => $group->value === $role);
        })->values();
    }

    private function buildDownloadFileName(Invoice $invoice, string $format): string
    {
        $parts = [];

        if (!empty($invoice->billing_period)) {
            $parts[] = $invoice->billing_period;
        }

        if (!empty($invoice->provider)) {
            $parts[] = $invoice->provider;
        }

        $base = $invoice->source_filename ?: 'invoice-' . $invoice->id;
        $name = pathinfo($base, PATHINFO_FILENAME) ?: $base;

        $parts[] = $name;

        $slug = str(implode('-', $parts))->slug('-');

        return sprintf('%s.%s', $slug, $format);
    }

    private function buildDefaultEmailFromName(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        $normalized = trim(Str::ascii($name));

        if ($normalized === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $normalized);

        if ($parts === false || count($parts) === 0) {
            return null;
        }

        $surname = array_pop($parts);

        if ($surname === null) {
            return null;
        }

        $surname = preg_replace('/[^A-Za-z0-9]/', '', $surname) ?? '';

        if ($surname === '') {
            return null;
        }

        $firstName = $parts[0] ?? $surname;
        $firstInitial = Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $firstName) ?? '', 0, 1);

        if ($firstInitial === null || $firstInitial === '') {
            $firstInitial = Str::substr($surname, 0, 1);
        }

        if ($firstInitial === null || $firstInitial === '') {
            return null;
        }

        $localPart = Str::lower($surname . $firstInitial);

        if ($localPart === '') {
            return null;
        }

        return $localPart . '@senat.cz';
    }

    private function streamCsv(Invoice $invoice): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        fputcsv($handle, [
            'Employee',
            'Phone',
            'Service',
            'Price without VAT',
            'Price with VAT',
        ]);

        foreach ($invoice->people as $person) {
            foreach ($person->lines as $line) {
                fputcsv($handle, [
                    optional($person->person)->name,
                    $person->phone,
                    $line->service_name,
                    number_format((float) $line->price_without_vat, 2, '.', ''),
                    number_format((float) $line->price_with_vat, 2, '.', ''),
                ]);
            }
        }

        fclose($handle);
    }

    private function generateSimplePdf(Invoice $invoice): string
    {
        $lines = [];

        foreach ($invoice->people as $person) {
            foreach ($person->lines as $line) {
                $lines[] = sprintf(
                    '%s - %s: %s / %s',
                    optional($person->person)->name ?? 'Unknown',
                    $line->service_name,
                    number_format((float) $line->price_without_vat, 2),
                    number_format((float) $line->price_with_vat, 2)
                );
            }
        }

        $content = 'Invoice #' . $invoice->id . "\n" . implode("\n", $lines);
        $escaped = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $content);

        $stream = "BT /F1 12 Tf 72 720 Td (" . $escaped . ") Tj ET";
        $length = strlen($stream);

        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n",
            "4 0 obj << /Length {$length} >> stream\n{$stream}\nendstream endobj\n",
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = "xref\n0 6\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $xref .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= $xref;
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n" . strlen($pdf) . "\n%%EOF";

        return $pdf;
    }
}
