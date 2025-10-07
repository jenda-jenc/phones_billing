<?php

namespace App\Services;

use App\Data\ImportedPersonData;
use App\Data\ServiceEntry;
use App\Models\Invoice;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportService
{
    private array $mapping;
    private iterable $servicesData;
    private Collection $persons;
    private ?string $sourceFilename;
    private ?string $billingPeriod;
    private ?string $provider;

    public function __construct(
        iterable $servicesData,
        array $mapping,
        ?string $sourceFilename = null,
        ?string $billingPeriod = null,
        ?string $provider = null
    )
    {
        $this->servicesData = $servicesData;
        $this->mapping = $mapping;
        $this->persons = new Collection();
        $this->sourceFilename = $sourceFilename;
        $this->billingPeriod = $billingPeriod;
        $this->provider = $provider;
    }

    public function process(): array
    {
        $mobiles = $this->mapServices();
        $this->persons = $this->loadPersons(array_keys($mobiles));
        $calculation = $this->calculatePersons($mobiles);

        $invoice = DB::transaction(function () use ($calculation) {
            return $this->storeInvoice($calculation['people']);
        });

        return [
            'invoice_id' => $invoice->id,
            'data' => $this->formatResponse($calculation['response']),
        ];
    }

    /**
     * Načti CSV do pole [phone] => ImportedPersonData
     */
    private function mapServices(): array
    {
        $mobiles = [];
        foreach ($this->servicesData as $k => $row) {
            if ($k === 0) {
                continue;
            }

            if (!is_array($row)) {
                continue;
            }

            $phoneIndex = $this->mapping['phone_number']['index'] ?? null;
            $phoneKey = is_numeric($phoneIndex) ? (int) $phoneIndex : null;

            if ($phoneKey === null || !array_key_exists($phoneKey, $row)) {
                continue;
            }

            $phone = $row[$phoneKey];

            if ($phone === null || $phone === '') {
                continue;
            }

            $group = '';
            $groupIndex = $this->mapping['group']['index'] ?? null;
            $groupKey = is_numeric($groupIndex) ? (int) $groupIndex : null;

            if (
                $groupKey !== null
                && array_key_exists($groupKey, $row)
            ) {
                $group = $row[$groupKey];
            }

            $tarifIndex = $this->mapping['tarif']['index'] ?? null;
            $serviceIndex = $this->mapping['service']['index'] ?? null;
            $priceIndex = $this->mapping['price']['index'] ?? null;
            $vatIndex = $this->mapping['vat']['index'] ?? null;

            $tarifKey = is_numeric($tarifIndex) ? (int) $tarifIndex : null;
            $serviceKey = is_numeric($serviceIndex) ? (int) $serviceIndex : null;
            $priceKey = is_numeric($priceIndex) ? (int) $priceIndex : null;
            $vatKey = is_numeric($vatIndex) ? (int) $vatIndex : null;

            if (
                $tarifKey === null
                || $serviceKey === null
                || $priceKey === null
                || $vatKey === null
                || !array_key_exists($tarifKey, $row)
                || !array_key_exists($serviceKey, $row)
                || !array_key_exists($priceKey, $row)
                || !array_key_exists($vatKey, $row)
            ) {
                continue;
            }

            $tarif   = $row[$tarifKey];
            $service = $row[$serviceKey];
            $price   = (float) str_replace(',', '.', (string) $row[$priceKey]);
            $vat     = (float) str_replace(',', '.', (string) $row[$vatKey]);
            $cena_s_dph = $price + ($price * ($vat / 100));

            if (!isset($mobiles[$phone])) {
                $mobiles[$phone] = [
                    'data' => new ImportedPersonData('', $phone),
                    'vat'  => $vat,
                ];
            }

            if ($price > 0) {
                $mobiles[$phone]['data']->addService(
                    new ServiceEntry(
                        $group, $tarif, $service, $price, $cena_s_dph
                    )
                );
            }
            $mobiles[$phone]['vat'] = $vat;
        }
        return $mobiles;
    }

    private function loadPersons(array $phones): Collection
    {
        if ($phones === []) {
            return new Collection();
        }

        return Person::query()
            ->with([
                'groups.tariffs',
                'phones' => static function ($query) use ($phones) {
                    $query->whereIn('phone', $phones);
                },
            ])
            ->whereHas('phones', static function ($query) use ($phones) {
                $query->whereIn('phone', $phones);
            })
            ->get();
    }

    /**
     * Sjednoť data podle osob v DB, naplň jméno/limit a vypočítej "zaplati" a pravidla.
     *
     * @param array $mobiles [phone] => ['data'=>ImportedPersonData, 'vat'=>float]
     * @return array{response: array, people: array<int, array{person: \App\Models\Person, phone: string, data: ImportedPersonData}>}
     */
    private function calculatePersons(array $mobiles): array
    {
        $mapped = [];
        $peopleForPersistence = [];

        foreach ($this->persons as $person) {
            foreach ($person->phones as $personPhone) {
                $phone = $personPhone->phone;

                if (!$phone) {
                    continue;
                }

                $entry = $mobiles[$phone] ?? null;
                if (!$entry) {
                    continue;
                }

                /** @var ImportedPersonData $personData */
                $personData = $entry['data'];
                $vat = $entry['vat'];
                $personData->name = $person->name;
                $personData->phone = $phone;
                $personData->limit = (float) ($personPhone->limit ?? 0);
                $personData->vat = $vat;

                $platiSam = 0.0;

                if (!empty($person->groups)) {
                    $servicesMap = [];
                    foreach ($personData->sluzby as $serviceEntry) {
                        $servicesMap[$serviceEntry->sluzba] = $serviceEntry;
                    }
                    foreach ($person->groups as $group) {
                        foreach ($group->tariffs as $tariff) {
                            $tariffName = $this->unEscape($tariff->name);
                            if (isset($servicesMap[$tariffName])) {
                                $se = $servicesMap[$tariffName];
                                $desc = [
                                    'skupina' => $se->skupina,
                                    'tarif'   => $se->tarif,
                                    'sluzba'  => $se->sluzba,
                                    'akce'    => $tariff->pivot->action,
                                    'cena_bez_dph' => $se->cena,
                                    'cena_s_dph' => $se->cena_s_dph,
                                ];
                                switch ($tariff->pivot->action) {
                                    case 'plati_sam':
                                        $platiSam += $se->cena_s_dph;
                                        $personData->addAplikovanePravidlo($desc + ['popis' => 'Platí sám']);
                                        break;
                                    case 'ignorovat':
                                        $personData->celkem -= $se->cena;
                                        $personData->celkem_s_dph -= $se->cena_s_dph;
                                        $personData->addAplikovanePravidlo($desc + ['popis' => 'Ignorováno']);
                                        break;
                                    default:
                                        $personData->addAplikovanePravidlo($desc + ['popis' => $tariff->pivot->action]);
                                        break;
                                }
                            }
                        }
                    }
                }

                $zaplati =  $personData->celkem_s_dph - $personData->limit ;
                $personData->zaplati = ($zaplati < 0 || $zaplati < $platiSam) ? $platiSam : $zaplati;
                if (!isset($mapped[$person->name])) {
                    $mapped[$person->name] = [];
                }
                $mapped[$person->name][$phone] = $personData;

                $peopleForPersistence[] = [
                    'person' => $person,
                    'phone' => $phone,
                    'data' => $personData,
                ];
            }
        }

        return [
            'response' => $mapped,
            'people' => $peopleForPersistence,
        ];
    }

    private function unEscape(string $value): string
    {
        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }

    /**
     * @param array<int, array{person: \App\Models\Person, phone: string, data: ImportedPersonData}> $peopleData
     */
    private function storeInvoice(array $peopleData): Invoice
    {
        $totalWithoutVat = 0;
        $totalWithVat = 0;

        $linesCount = 0;

        foreach ($peopleData as $entry) {
            $personData = $entry['data'];
            $totalWithoutVat += $personData->celkem;
            $totalWithVat += $personData->celkem_s_dph;
            $linesCount += count($personData->sluzby);
        }

        $invoice = $this->upsertInvoice($linesCount, $totalWithoutVat, $totalWithVat);

        foreach ($peopleData as $entry) {
            $person = $entry['person'];
            $phone = $entry['phone'];
            $personData = $entry['data'];

            $invoicePerson = $invoice->people()->create([
                'person_id' => $person->id,
                'phone' => $phone,
                'vat_rate' => $personData->vat,
                'total_without_vat' => $personData->celkem,
                'total_with_vat' => $personData->celkem_s_dph,
                'limit' => $personData->limit,
                'payable' => $personData->zaplati,
                'applied_rules' => $personData->aplikovanaPravidla ?: null,
            ]);

            $personData->invoicePersonId = $invoicePerson->id;

            foreach ($personData->sluzby as $service) {
                $invoicePerson->lines()->create([
                    'person_id' => $person->id,
                    'group_name' => $service->skupina ?: null,
                    'tariff' => $service->tarif ?: null,
                    'service_name' => $service->sluzba,
                    'price_without_vat' => $service->cena,
                    'price_with_vat' => $service->cena_s_dph,
                ]);
            }
        }

        return $invoice;
    }

    private function upsertInvoice(int $linesCount, float $totalWithoutVat, float $totalWithVat): Invoice
    {
        $data = [
            'billing_period' => $this->billingPeriod,
            'provider' => $this->provider,
            'source_filename' => $this->sourceFilename,
            'mapping' => $this->mapping,
            'row_count' => $linesCount,
            'total_without_vat' => $totalWithoutVat,
            'total_with_vat' => $totalWithVat,
        ];

        if ($this->billingPeriod === null || $this->provider === null) {
            return Invoice::create($data);
        }

        $existingInvoice = Invoice::query()
            ->where('billing_period', $this->billingPeriod)
            ->where('provider', $this->provider)
            ->lockForUpdate()
            ->first();

        if ($existingInvoice !== null) {
            $existingInvoice->loadMissing('people.lines');

            foreach ($existingInvoice->people as $invoicePerson) {
                $invoicePerson->lines()->delete();
            }

            $existingInvoice->people()->delete();
        }

        $invoice = Invoice::updateOrCreate(
            [
                'billing_period' => $this->billingPeriod,
                'provider' => $this->provider,
            ],
            $data
        );

        $invoice->unsetRelation('people');

        return $invoice;
    }

    /**
     * @param array<string, array<string, ImportedPersonData>> $response
     */
    private function formatResponse(array $response): array
    {
        $formatted = [];

        foreach ($response as $name => $phones) {
            foreach ($phones as $phone => $personData) {
                if ($personData instanceof ImportedPersonData) {
                    $formatted[$name][$phone] = $personData->toArray();

                    continue;
                }

                $formatted[$name][$phone] = $personData;
            }
        }

        return $formatted;
    }
}
