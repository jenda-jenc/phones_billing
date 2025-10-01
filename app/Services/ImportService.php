<?php

namespace App\Services;

use App\Data\ImportedPersonData;
use App\Data\ServiceEntry;
use Illuminate\Support\Collection;

class ImportService
{
    private array $mapping;
    private array $servicesData;
    private Collection $persons;

    public function __construct(array $servicesData, array $mapping, Collection $persons)
    {
        $this->servicesData = $servicesData;
        $this->mapping = $mapping;
        $this->persons = $persons;
    }

    public function process(): array
    {
        $mobiles = $this->mapServices();
        return $this->calculatePersons($mobiles);
    }

    /**
     * Načti CSV do pole [phone] => ImportedPersonData
     */
    private function mapServices(): array
    {
        $mobiles = [];
        foreach ($this->servicesData as $k => $row) {
            if ($k === 0) continue;

            $phone = $row[$this->mapping['phone_number']['index']];

            $group = '';
            if (
                isset($this->mapping['group']['index'])
                && is_numeric($this->mapping['group']['index'])
                && isset($row[$this->mapping['group']['index']])
            ) {
                $group = $row[$this->mapping['group']['index']];
            }

            $tarif   = $row[$this->mapping['tarif']['index']];
            $service = $row[$this->mapping['service']['index']];
            $price   = (float) str_replace(',', '.', $row[$this->mapping['price']['index']]);
            $vat     = (float) str_replace(',', '.', $row[$this->mapping['vat']['index']]);
            $cena_s_dph = $price + ($price * ($vat / 100));

            if (!isset($mobiles[$phone])) {
                $mobiles[$phone] = [
                    'data' => new \App\Data\ImportedPersonData('', $phone),
                    'vat'  => $vat,
                ];
            }

            if ($price > 0) {
                $mobiles[$phone]['data']->addService(
                    new \App\Data\ServiceEntry(
                        $group, $tarif, $service, $price, $cena_s_dph
                    )
                );
            }
            $mobiles[$phone]['vat'] = $vat;
        }
        return $mobiles;
    }

    /**
     * Sjednoť data podle osob v DB, naplň jméno/limit a vypočítej "zaplati" a pravidla.
     * @param array $mobiles [phone] => ['data'=>ImportedPersonData, 'vat'=>float]
     * @return array
     */
    private function calculatePersons(array $mobiles): array
    {
        $mapped = [];

        foreach ($this->persons as $person) {
            $phone = $person->phone;
            $entry = $mobiles[$phone] ?? null;
            if (!$entry) continue;

            /** @var ImportedPersonData $personData */
            $personData = $entry['data'];
            $vat = $entry['vat'];
            $personData->name = $person->name;
            $personData->limit = (float)$person->limit;

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
            $mapped[$person->name][$phone] = $personData->toArray();
        }
        return $mapped;
    }

    private function unEscape(string $value): string
    {
        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}
