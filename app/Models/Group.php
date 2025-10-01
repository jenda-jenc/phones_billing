<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value'];

    public function people()
    {
        return $this->belongsToMany(Person::class, 'group_person');
    }

    public function tariffs()
    {
        return $this->belongsToMany(Tariff::class, 'group_tariff')->orderBy('name')
            ->withPivot('action') // Přidá přístup k `action`
            ->withTimestamps();
    }
}
