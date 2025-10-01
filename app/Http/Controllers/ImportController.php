<?php

namespace App\Http\Controllers;

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
        ]);

        $mapping = json_decode($request->input('mapping'), true);

        // Ošetři, že group nemusí být v mappingu
        if (!isset($mapping['group'])) {
            $mapping['group'] = null; // nebo ['index' => null], dle implementace v service
        }

        $servicesData = [];
        if (($handle = fopen($request->file('services')->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 100000, ';')) !== false) {
                $row = array_map(fn($f) => iconv('windows-1250', 'UTF-8', $f), $row);
                $servicesData[] = $row;
            }
            fclose($handle);
        }

        $persons = Person::with(['groups.tariffs'])->get();

        $importService = new ImportService($servicesData, $mapping, $persons);
        $importData = $importService->process();

        return inertia('ImportTable', [
            'importData' => $importData,
        ])->with('success', 'Process proběhl v pořádku.');
    }
}
