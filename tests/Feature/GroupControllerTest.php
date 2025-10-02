<?php

use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
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
