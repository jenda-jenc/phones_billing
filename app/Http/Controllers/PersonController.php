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
            ->map(function ($phone) {
                if (is_array($phone)) {
                    $number = isset($phone['phone']) ? trim((string) $phone['phone']) : '';

                    return [
                        'phone' => $number,
                        'limit' => $this->normalizeLimit($phone['limit'] ?? null),
                    ];
                }

                if (is_string($phone)) {
                    return [
                        'phone' => trim($phone),
                        'limit' => null,
                    ];
                }

                return [
                    'phone' => '',
                    'limit' => null,
                ];
            })
            ->filter(fn ($phone) => $phone['phone'] !== '')
            ->values()
            ->all();
    }

    private function syncPhones(Person $person, array $phones): void
    {
        $incoming = collect($phones)
            ->map(fn ($phone) => [
                'phone' => $phone['phone'],
                'limit' => round((float) ($phone['limit'] ?? 0), 2),
            ])
            ->keyBy('phone');

        $existingPhones = $person->phones()->pluck('phone');
        $toDelete = $existingPhones->diff($incoming->keys());

        if ($toDelete->isNotEmpty()) {
            $person->phones()->whereIn('phone', $toDelete->all())->delete();
        }

        foreach ($incoming as $phoneData) {
            $person->phones()->updateOrCreate(
                ['phone' => $phoneData['phone']],
                ['limit' => $phoneData['limit']]
            );
        }
    }

    private function normalizeLimit(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $normalized = str_replace(',', '.', $value);

            if (! is_numeric($normalized)) {
                return null;
            }

            return (float) $normalized;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function rules(?Person $person = null): array
    {
        $uniqueRule = Rule::unique('person_phones', 'phone');

        if ($person) {
            $uniqueRule = $uniqueRule->where(fn ($query) => $query->where('person_id', '!=', $person->id));
        }

        return [
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|array:phone,limit',
            'phones.*.phone' => [
                'required',
                'string',
                'max:15',
                'distinct',
                $uniqueRule,
            ],
            'phones.*.limit' => 'required|numeric|between:0,999999.99',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'              => 'Prosím, vyplňte jméno.',
            'phones.required'            => 'Prosím, zadejte alespoň jedno telefonní číslo.',
            'phones.min'                 => 'Prosím, zadejte alespoň jedno telefonní číslo.',
            'phones.array'               => 'Telefonní čísla musí být ve správném formátu.',
            'phones.*.required'          => 'Telefonní číslo je povinné.',
            'phones.*.array'             => 'Telefonní číslo musí obsahovat hodnotu čísla i limitu.',
            'phones.*.phone.required'    => 'Telefonní číslo je povinné.',
            'phones.*.phone.distinct'    => 'Telefonní čísla se musí lišit.',
            'phones.*.phone.unique'      => 'Zadané telefonní číslo již existuje.',
            'phones.*.phone.max'         => 'Telefonní číslo nesmí být delší než 15 znaků.',
            'phones.*.limit.required'    => 'Limit je povinná hodnota.',
            'phones.*.limit.numeric'     => 'Limit musí být číslo.',
            'phones.*.limit.between'     => 'Limit musí být mezi 0 a 999999.99.',
            'department.required'        => 'Prosím, uveďte pracovní útvar.',
        ];
    }
}
