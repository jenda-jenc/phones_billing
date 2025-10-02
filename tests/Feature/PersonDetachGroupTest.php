<?php

use App\Models\Group;
use App\Models\Person;
use App\Models\User;

it('rejects unauthenticated requests to detach a group from a person', function () {
    $person = Person::create([
        'name' => 'Unauthorized Person',
        'department' => 'Security',
        'limit' => 100,
    ]);

    $group = Group::create([
        'name' => 'Restricted Group',
        'value' => 'restricted',
    ]);

    $person->groups()->attach($group->id);

    $response = $this->post(route('persons.detach-group'), [
        'person_id' => $person->id,
        'group_id' => $group->id,
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('group_person', [
        'person_id' => $person->id,
        'group_id' => $group->id,
    ]);
});

it('allows authenticated users to detach a group from a person', function () {
    $user = User::factory()->create();

    $person = Person::create([
        'name' => 'Authorized Person',
        'department' => 'Operations',
        'limit' => 150,
    ]);

    $group = Group::create([
        'name' => 'Allowed Group',
        'value' => 'allowed',
    ]);

    $person->groups()->attach($group->id);

    $response = $this
        ->actingAs($user)
        ->from(route('persons.index'))
        ->post(route('persons.detach-group'), [
            'person_id' => $person->id,
            'group_id' => $group->id,
        ]);

    $response->assertRedirect(route('persons.index'));

    $this->assertDatabaseMissing('group_person', [
        'person_id' => $person->id,
        'group_id' => $group->id,
    ]);
});
