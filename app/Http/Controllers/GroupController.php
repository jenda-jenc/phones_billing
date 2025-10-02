<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{

    public function index()
    {
        // Získání seznamu osob z databáze
        $groups = Group::with(['people', 'tariffs'])->get();
//        $tariffs = Tariff::all();
        $tariffs = Tariff::orderBy('name', 'asc')->get();
        // Předání dat do Inertia frontend stránky
        return inertia('TariffGroups', [
            'groups' => $groups,
            'tariffs' => $tariffs,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255', Rule::unique('groups', 'value')],
        ], [
            'name.required' => 'Název skupiny je povinný.',
            'value.required' => 'Hodnota je povinná.',
            'value.unique' => 'Tato hodnota je již použita jinou skupinou.',
        ]);

        Group::create($validated);

        return redirect()->route('groups.index')->with('success', 'Skupina byla úspěšně vytvořena.');
    }

    public function attachTariff(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'action' => 'required|in:ignorovat,do_limitu,plati_sam',
        ],[
            'tariff_id.required'       => 'Prosím vyberte tarif k přiřazení.',
        ]);

        $group = Group::findOrFail($request->input('group_id'));
        $group->tariffs()->attach($request->input('tariff_id'), [
            'action' => $request->input('action'),
        ]);

        return redirect()->route('groups.index')->with('success', 'Tarif byl úspěšně přiřazen.');
    }

    public function detachTariff(Request $request) {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'tariff_id' => 'required|exists:tariffs,id',
        ]);
        $group = Group::findOrFail($request->input('group_id'));
        $group->tariffs()->detach($request->input('tariff_id'));
        return redirect()->route('groups.index');
    }

    public function listTariffs() {
        return response()->json(['tariffs' => Tariff::all()]);
    }

    public function bulkStoreTariffs(Request $request)
    {
        $names = $request->input('names', []);
        foreach ($names as $name) {
            \App\Models\Tariff::firstOrCreate(['name' => $name]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        // Odpojí skupinu od všech osob
        $group->people()->detach();

        // Odpojí skupinu od všech tarifů (pokud je pivot group_tariff)
        $group->tariffs()->detach();

        // Smaže samotnou skupinu
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Skupina byla úspěšně smazána.');
    }

    /**
     * Aktualizuje existující skupinu na základě požadavku PUT / PATCH.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Najít skupinu nebo vyhodit chybu 404
        $group = Group::findOrFail($id);

        // Validace dat z požadavku
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'value')->ignore($group->id),
            ],
        ], [
            'name.required' => 'Název skupiny je povinný.',
            'value.required' => 'Hodnota je povinná.',
            'value.unique' => 'Tato hodnota je již použita jinou skupinou.',
        ]);

        // Aktualizace hodnoty skupiny
        $group->update($validated);

        // Přesměrování zpět s úspěšnou hláškou
        return redirect()->route('groups.index')->with('success', 'Skupina byla úspěšně aktualizována.');
    }
}


