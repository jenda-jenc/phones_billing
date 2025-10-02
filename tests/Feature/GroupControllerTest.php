<?php

use App\Models\Group;
use App\Models\Tariff;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\put;

it('requires a value when creating a group', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = post(route('groups.store'), [
        'name' => 'Support Team',
    ]);

    $response->assertSessionHasErrors(['value']);

    expect(Group::count())->toBe(0);
});

it('prevents updating a group with a duplicate value', function () {
    $user = User::factory()->create();
    actingAs($user);

    $existingGroup = Group::create([
        'name' => 'Existing Group',
        'value' => 'existing-value',
    ]);

    $group = Group::create([
        'name' => 'Second Group',
        'value' => 'second-value',
    ]);

    $response = put(route('groups.update', $group), [
        'name' => 'Second Group Updated',
        'value' => $existingGroup->value,
    ]);

    $response->assertSessionHasErrors(['value']);

    $group->refresh();

    expect($group->value)->toBe('second-value');
});

it('rejects unauthenticated users when bulk creating tariffs', function () {
    $response = postJson(route('tariffs.bulk-store'), [
        'names' => ['Tariff A'],
    ]);

    $response->assertStatus(401);

    expect(Tariff::count())->toBe(0);
});

it('allows verified users to bulk create tariffs', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = postJson(route('tariffs.bulk-store'), [
        'names' => ['Tariff A', 'Tariff B'],
    ]);

    $response->assertOk();

    expect(Tariff::pluck('name')->sort()->values()->all())->toBe(['Tariff A', 'Tariff B']);
});
