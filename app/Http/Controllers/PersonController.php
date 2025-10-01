<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Models\Person;

class PersonController extends Controller
{

    public function index()
    {
        // Získání seznamu osob z databáze
        $people = Person::with('groups')->orderBy('name')->get();
        $groups = Group::all();
        // Předání dat do Inertia frontend stránky
        return inertia('Osoby', [
            'people' => $people,
            'groups' => $groups,
        ]);
    }

    /**
     * Uloží předanou osobu do databáze.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) : \Illuminate\Http\RedirectResponse
    {
        // Validace dat z formuláře
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:people',
            'department' => 'required|string|max:255',
            'limit' => 'required|numeric|between:0,999999.99',
        ], [
            'name.required'       => 'Prosím, vyplňte jméno.',
            'phone.required'      => 'Telefonní číslo je povinné.',
            'phone.unique'        => 'Zadané telefonní číslo již existuje.',
            'department.required' => 'Prosím, uveďte pracovní útvar.',
            'limit.required'      => 'Limit je povinná hodnota.',
            'limit.numeric'       => 'Limit musí být číslo.',
            'limit.between'       => 'Limit musí být mezi 0 a 999999.99.',
        ]);

        Person::create($validated);

        // Přesměrování na stránku s úspěšnou hláškou
        return redirect()->route('persons.index')->with('success', 'Osoba byla úspěšně přidána!');
    }
    /**
     * Aktualizuje záznam osoby v databázi.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Person $person
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:people,phone,' . $person->id,
            'department' => 'required|string|max:255',
            'limit' => 'required|numeric|between:0,999999.99',
        ], [
            'name.required'       => 'Prosím, vyplňte jméno.',
            'phone.required'      => 'Telefonní číslo je povinné.',
            'phone.unique'        => 'Zadané telefonní číslo již existuje.',
            'department.required' => 'Prosím, uveďte pracovní útvar.',
            'limit.required'      => 'Limit je povinná hodnota.',
            'limit.numeric'       => 'Limit musí být číslo.',
            'limit.between'       => 'Limit musí být mezi 0 a 999999.99.',
        ]);

        $person->update($validated);

        return redirect()->route('persons.index')->with('success', 'Osoba byla úspěšně upravena!'. uniqid());
    }

    public function destroy($id)
    {
        try {
            $person = Person::findOrFail($id);
            $person->delete();
            return back()->with('success', 'Osoba úspěšně odstraněna.');
        } catch (\Exception $e) {
            return back()->withErrors('Chyba při mazání osoby: ' . $e->getMessage());
        }
    }

    public function assignGroup(Request $request, $personId)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id', // Validace, že skupina existuje
        ]);

        $person = Person::findOrFail($personId);
        $person->groups()->syncWithoutDetaching([$request->group_id]);
        $person->save();

        return redirect()->back()->with('success', 'Skupina byla úspěšně přiřazena');
    }
    public function detachGroup(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:people,id',
            'group_id' => 'required|exists:groups,id',
        ]);
        $person = \App\Models\Person::findOrFail($request->person_id);
        $person->groups()->detach($request->group_id);

        return redirect()->back();
    }
}
