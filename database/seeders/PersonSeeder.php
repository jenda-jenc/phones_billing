<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->loadRows() as $row) {
            $name = trim((string) Arr::get($row, 'name', ''));

            if ($name === '') {
                continue;
            }

            $person = Person::updateOrCreate(
                ['name' => $name],
                [
                    'department' => trim((string) Arr::get($row, 'department', '')),
                ]
            );

            foreach ($this->extractPhones($row) as $phone) {
                $person->phones()->updateOrCreate(
                    ['phone' => $phone['phone']],
                    ['limit' => $phone['limit']]
                );
            }
        }
    }

    private function loadRows(): array
    {
        $phpPath = database_path('seeders/data/persons.php');

        if (File::exists($phpPath)) {
            $data = require $phpPath;

            if (is_array($data)) {
                return $data;
            }
        }

        $jsonPath = database_path('seeders/data/persons.json');

        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);

            if (is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    private function extractPhones(array $row): array
    {
        $phones = [];
        $entries = Arr::get($row, 'phones');

        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if (is_array($entry)) {
                    $number = trim((string) Arr::get($entry, 'phone', ''));

                    if ($number === '') {
                        continue;
                    }

                    $phones[] = [
                        'phone' => $number,
                        'limit' => $this->castLimit(Arr::get($entry, 'limit', Arr::get($row, 'limit'))),
                    ];

                    continue;
                }

                if (is_string($entry)) {
                    $number = trim($entry);

                    if ($number === '') {
                        continue;
                    }

                    $phones[] = [
                        'phone' => $number,
                        'limit' => $this->castLimit(Arr::get($row, 'limit')),
                    ];
                }
            }

            return $phones;
        }

        $singlePhone = Arr::get($row, 'phone');

        if (is_string($singlePhone)) {
            $number = trim($singlePhone);

            if ($number !== '') {
                $phones[] = [
                    'phone' => $number,
                    'limit' => $this->castLimit(Arr::get($row, 'limit')),
                ];
            }
        }

        return $phones;
    }

    private function castLimit(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_string($value)) {
            $normalized = str_replace(',', '.', $value);

            if (is_numeric($normalized)) {
                return round((float) $normalized, 2);
            }

            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        return 0.0;
    }
}
