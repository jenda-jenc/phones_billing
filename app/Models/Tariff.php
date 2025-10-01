<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_tariff')
            ->withPivot('action')
            ->withTimestamps();
    }
}
