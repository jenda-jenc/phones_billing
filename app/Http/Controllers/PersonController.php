<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::with(['groups', 'phones'])->orderBy('name')->get();
        $groups = Group::all();

        return inertia('Osoby', [
            'people' => $people,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->merge(['phones' => $this->preparePhones($request->input('phones', []))]);

        $validated = $request->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($validated) {
            /** @var Person $person */
            $person = Person::create([
                'name' => $validated['name'],
                'department' => $validated['department'],
                'limit' => $validated['limit'],
            ]);

            $this->syncPhones($person, $validated['phones']);
        });

        return redirect()->route('persons.index')->with('success', 'Osoba byla úspěšně přidána!');
    }

    public function update(Request $request, Person $person): \Illuminate\Http\RedirectResponse
    {
        $request->merge(['phones' => $this->preparePhones($request->input('phones', []))]);

        $validated = $request->validate($this->rules($person), $this->messages());

        DB::transaction(function () use ($validated, $person) {
            $person->update([
                'name' => $validated['name'],
                'department' => $validated['department'],
                'limit' => $validated['limit'],
            ]);

            $this->syncPhones($person, $validated['phones']);
        });

        return redirect()->route('persons.index')->with('success', 'Osoba byla úspěšně upravena!');
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
            'group_id' => 'required|exists:groups,id',
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

        $person = Person::findOrFail($request->person_id);
        $person->groups()->detach($request->group_id);

        return redirect()->back();
    }

    private function preparePhones(array $phones): array
    {
        return collect($phones)
            ->map(fn ($phone) => is_string($phone) ? trim($phone) : '')
            ->filter(fn ($phone) => $phone !== '')
            ->values()
            ->all();
    }

    private function syncPhones(Person $person, array $phones): void
    {
        $existing = $person->phones()->get()->mapWithKeys(
            fn ($phoneModel) => [$phoneModel->phone => $phoneModel]
        );

        $incoming = collect($phones);

        $toDelete = $existing->keys()->diff($incoming);

        if ($toDelete->isNotEmpty()) {
            $person->phones()->whereIn('phone', $toDelete->all())->delete();
        }

        $toCreate = $incoming->diff($existing->keys());

        foreach ($toCreate as $phone) {
            $person->phones()->create(['phone' => $phone]);
        }
    }

    private function rules(?Person $person = null): array
    {
        $uniqueRule = Rule::unique('person_phones', 'phone');

        if ($person) {
            $uniqueRule = $uniqueRule->ignore($person->id, 'person_id');
        }

        return [
            'name' => 'required|string|max:255',
            'phones' => 'required|array|min:1',
            'phones.*' => [
                'required',
                'string',
                'max:15',
                'distinct',
                $uniqueRule,
            ],
            'department' => 'required|string|max:255',
            'limit' => 'required|numeric|between:0,999999.99',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'       => 'Prosím, vyplňte jméno.',
            'phones.required'     => 'Prosím, zadejte alespoň jedno telefonní číslo.',
            'phones.min'          => 'Prosím, zadejte alespoň jedno telefonní číslo.',
            'phones.array'        => 'Telefonní čísla musí být ve správném formátu.',
            'phones.*.required'   => 'Telefonní číslo je povinné.',
            'phones.*.distinct'   => 'Telefonní čísla se musí lišit.',
            'phones.*.unique'     => 'Zadané telefonní číslo již existuje.',
            'phones.*.max'        => 'Telefonní číslo nesmí být delší než 15 znaků.',
            'department.required' => 'Prosím, uveďte pracovní útvar.',
            'limit.required'      => 'Limit je povinná hodnota.',
            'limit.numeric'       => 'Limit musí být číslo.',
            'limit.between'       => 'Limit musí být mezi 0 a 999999.99.',
        ];
    }
}
