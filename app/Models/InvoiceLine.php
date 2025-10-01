<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_person_id',
        'person_id',
        'group_name',
        'tariff',
        'service_name',
        'price_without_vat',
        'price_with_vat',
    ];

    protected $casts = [
        'price_without_vat' => 'float',
        'price_with_vat' => 'float',
    ];

    public function invoicePerson(): BelongsTo
    {
        return $this->belongsTo(InvoicePerson::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
