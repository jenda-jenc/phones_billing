<?php

use App\Models\Invoice;
use App\Models\Person;
use App\Models\User;
use App\Services\ImportService;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

it('creates a person with multiple phone numbers', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = post(route('persons.store'), [
        'name' => 'John Doe',
        'phones' => [
            ['phone' => '123456789', 'limit' => 250],
            ['phone' => '987654321', 'limit' => 300],
        ],
        'department' => 'IT',
    ]);

    $response->assertRedirect(route('persons.index'));

    $person = Person::with('phones')->first();

    expect($person)->not->toBeNull()
        ->and($person->phones)->toHaveCount(2)
        ->and($person->phones->pluck('phone')->all())
        ->toEqualCanonicalizing(['123456789', '987654321'])
        ->and($person->phones->pluck('limit')->all())
        ->toEqualCanonicalizing([250.0, 300.0]);
});

it('updates phone numbers by replacing removed entries', function () {
    $user = User::factory()->create();
    actingAs($user);

    /** @var Person $person */
    $person = Person::create([
        'name' => 'Jane Smith',
        'department' => 'Finance',
    ]);

    $person->phones()->createMany([
        ['phone' => '555111222', 'limit' => 200],
        ['phone' => '555222333', 'limit' => 250],
    ]);

    $response = put(route('persons.update', $person), [
        'id' => $person->id,
        'name' => 'Jane Smith',
        'department' => 'Finance',
        'phones' => [
            ['phone' => '555222333', 'limit' => 450],
            ['phone' => '555999888', 'limit' => 500],
        ],
    ]);

    $response->assertRedirect(route('persons.index'));

    $person->refresh();

    expect($person->phones)->toHaveCount(2)
        ->and($person->phones->pluck('phone')->all())
        ->toEqualCanonicalizing(['555222333', '555999888'])
        ->and($person->phones->pluck('limit')->all())
        ->toEqualCanonicalizing([450.0, 500.0]);
});

it('lists people together with all of their phone numbers', function () {
    $user = User::factory()->create();
    actingAs($user);

    $person = Person::create([
        'name' => 'Listing Person',
        'department' => 'Support',
    ]);

    $person->phones()->createMany([
        ['phone' => '444111000', 'limit' => 100],
        ['phone' => '444222000', 'limit' => 200],
    ]);

    $response = get(route('persons.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Osoby')
        ->has('people', 1)
        ->has('people.0.phones', 2)
        ->where('people.0.phones.0.phone', '444111000')
        ->where('people.0.phones.1.phone', '444222000')
    );
});

it('imports invoice rows for every matching phone number', function () {
    $person = Person::create([
        'name' => 'Imported Person',
        'department' => 'Logistics',
    ]);

    $phones = ['777000111', '777000222'];

    $person->phones()->createMany([
        ['phone' => $phones[0], 'limit' => 0],
        ['phone' => $phones[1], 'limit' => 0],
    ]);

    $mapping = [
        'phone_number' => ['index' => 0],
        'tarif' => ['index' => 1],
        'service' => ['index' => 2],
        'price' => ['index' => 3],
        'vat' => ['index' => 4],
        'group' => ['index' => 5],
    ];

    $servicesData = [
        ['phone', 'tarif', 'service', 'price', 'vat', 'group'],
        [$phones[0], 'Tarif A', 'Service A', '10', '21', 'Skupina A'],
        [$phones[1], 'Tarif B', 'Service B', '5', '21', 'Skupina B'],
    ];

    $persons = Person::with(['groups.tariffs', 'phones'])->get();

    $importService = new ImportService(
        $servicesData,
        $mapping,
        $persons,
        'services.csv',
        '2025-01',
        Invoice::PROVIDER_T_MOBILE
    );

    $result = $importService->process();

    $invoice = Invoice::with('people')->find($result['invoice_id']);

    expect($invoice)->not->toBeNull()
        ->and($invoice->provider)->toBe(Invoice::PROVIDER_T_MOBILE)
        ->and($invoice->people)->toHaveCount(2)
        ->and($invoice->people->pluck('phone')->all())
        ->toEqualCanonicalizing($phones);

    $responseData = $result['data'][$person->name] ?? [];

    expect($responseData)->not->toBeEmpty();

    foreach ($phones as $phone) {
        expect($responseData[$phone]['invoice_person_id'] ?? null)
            ->not->toBeNull();
    }
});
