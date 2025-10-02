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
        'limit',
    ];

    protected $casts = [
        'limit' => 'float',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
