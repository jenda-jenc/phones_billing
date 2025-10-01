<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Person;
use App\Services\ImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Import', [
            'tariffs' => \App\Models\Tariff::all(),
            'groups' => \App\Models\Group::with('tariffs')->get(),
        ]);
    }
    public function processImport(Request $request)
    {
        $request->validate([
            'services' => 'required|file|mimes:csv,txt',
            'mapping'  => 'required|string',
            'billing_period' => 'required|date_format:Y-m',
        ]);

        $mapping = json_decode($request->input('mapping'), true);

        // Ošetři, že group nemusí být v mappingu
        if (!isset($mapping['group'])) {
            $mapping['group'] = null; // nebo ['index' => null], dle implementace v service
        }

        $servicesData = [];
        $sourceFilename = $request->file('services')->getClientOriginalName();
        if (($handle = fopen($request->file('services')->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 100000, ';')) !== false) {
                $row = array_map(fn($f) => iconv('windows-1250', 'UTF-8', $f), $row);
                $servicesData[] = $row;
            }
            fclose($handle);
        }

        $persons = Person::with(['groups.tariffs'])->get();

        $importService = new ImportService(
            $servicesData,
            $mapping,
            $persons,
            $sourceFilename,
            (string) $request->input('billing_period')
        );
        $processed = $importService->process();

        return redirect()
            ->route('import.show', $processed['invoice_id'])
            ->with('success', 'Process proběhl v pořádku.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->loadMissing(['people.person', 'people.lines']);

        $importData = [];

        foreach ($invoice->people as $invoicePerson) {
            $name = optional($invoicePerson->person)->name ?? $invoicePerson->phone;
            $phone = $invoicePerson->phone;

            $importData[$name][$phone] = [
                'name' => $name,
                'phone' => $phone,
                'limit' => $invoicePerson->limit,
                'celkem' => round($invoicePerson->total_without_vat, 4),
                'celkem_s_dph' => round($invoicePerson->total_with_vat, 4),
                'zaplati' => round($invoicePerson->payable, 2),
                'invoice_person_id' => $invoicePerson->id,
                'sluzby' => $invoicePerson->lines
                    ->map(fn ($line) => [
                        'skupina' => $line->group_name,
                        'tarif' => $line->tariff,
                        'sluzba' => $line->service_name,
                        'cena' => $line->price_without_vat,
                        'cena_s_dph' => $line->price_with_vat,
                    ])
                    ->values()
                    ->all(),
                'aplikovana_pravidla' => $invoicePerson->applied_rules ?? [],
            ];
        }

        return inertia('ImportTable', [
            'importData' => $importData,
            'invoiceId' => $invoice->id,
        ]);
    }
}
