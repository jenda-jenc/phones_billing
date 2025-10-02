<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonSeeder extends Seeder
{
    private const CSV_DATA = <<<'CSV'
name;department;phone;limit
"Adéla Vonásková";Marketing;+420 777 123 456;450
"Adéla Vonásková";Marketing;+420 602 111 222;
"Jan Novák";Sales;+420 777 222 111; 900
"Jan Novák";Sales;+420 777 222 112;900
"Jan Novák";Sales;+420 777 222 113;
"Lenka Bílá";HR;+420 603 123 456;0
"Lenka Bílá";HR;+420 603 123 457;0
"   Petr Černý  ";IT;+420 604 555 111;1 200,50
"Andrea Malá";Finance;+420 605 444 333;600
"Marie  Svobodová";Finance;;500
;Operations;+420 606 222 333;700
CSV;

    public function run(): void
    {
        $rows = $this->parseCsv();

        foreach ($rows as $row) {
            $person = Person::updateOrCreate(
                [
                    'name' => $row['name'],
                    'department' => $row['department'],
                ],
                [
                    'limit' => $row['limit'],
                ]
            );

            $person->phones()->updateOrCreate([
                'phone' => $row['phone'],
            ]);
        }
    }

    /**
     * @return array<int, array{name: string, department: string, phone: string, limit: float}>
     */
    private function parseCsv(): array
    {
        $lines = preg_split("/(\r\n|\r|\n)/", trim(self::CSV_DATA));
        if (!$lines) {
            return [];
        }

        $data = [];
        $previousLimits = [];

        // Remove header
        array_shift($lines);

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $columns = str_getcsv($line, ';');
            $columns = array_pad($columns, 4, '');

            [$name, $department, $phone, $limit] = $columns;

            $name = trim($name, " \"\t\n\r\0\x0B");
            $department = trim($department);
            $phone = preg_replace('/\s+/', '', trim($phone));
            $limit = trim($limit);

            if ($name === '' || $phone === '') {
                continue;
            }

            $personKey = mb_strtolower($name.'|'.$department);

            $normalisedLimit = Str::of($limit)
                ->replace(' ', '')
                ->replace(',', '.')
                ->toString();

            if ($normalisedLimit === '') {
                $limitValue = $previousLimits[$personKey] ?? 0.0;
            } else {
                $limitValue = (float) $normalisedLimit;
                $previousLimits[$personKey] = $limitValue;
            }

            if (!isset($previousLimits[$personKey])) {
                $previousLimits[$personKey] = $limitValue;
            }

            $data[] = [
                'name' => $name,
                'department' => $department,
                'phone' => $phone,
                'limit' => $limitValue,
            ];
        }

        return $data;
    }
}
