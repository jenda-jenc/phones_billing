<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonPhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
