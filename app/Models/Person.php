<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'department',
    ];

    public function phones(): HasMany
    {
        return $this->hasMany(PersonPhone::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_person');
    }

    public function invoicePeople()
    {
        return $this->hasMany(InvoicePerson::class);
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'person_user')->withTimestamps();
    }
}
