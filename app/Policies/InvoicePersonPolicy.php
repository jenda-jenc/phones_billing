<?php

namespace App\Policies;

use App\Models\InvoicePerson;
use App\Models\User;

class InvoicePersonPolicy
{
    public function view(User $user, InvoicePerson $invoicePerson): bool
    {
        return $this->isLinkedToUser($user, $invoicePerson);
    }

    public function viewAny(User $user): bool
    {
        return ($user->role ?? null) === 'admin';
    }

    public function email(User $user, InvoicePerson $invoicePerson): bool
    {
        return $this->isLinkedToUser($user, $invoicePerson);
    }

    protected function isLinkedToUser(User $user, InvoicePerson $invoicePerson): bool
    {
        if (($user->role ?? null) === 'admin') {
            return true;
        }

        $person = $invoicePerson->person;

        if ($person === null) {
            return false;
        }

        return $user->people()->where('people.id', $person->id)->exists();
    }
}
