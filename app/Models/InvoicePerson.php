<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoicePerson extends Model
{
    use HasFactory;

    protected $table = 'invoice_people';

    protected $fillable = [
        'invoice_id',
        'person_id',
        'phone',
        'vat_rate',
        'total_without_vat',
        'total_with_vat',
        'limit',
        'payable',
        'applied_rules',
    ];

    protected $casts = [
        'total_without_vat' => 'float',
        'total_with_vat' => 'float',
        'limit' => 'float',
        'payable' => 'float',
        'vat_rate' => 'float',
        'applied_rules' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_person_id');
    }
}
