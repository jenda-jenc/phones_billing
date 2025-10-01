<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_filename',
        'mapping',
        'row_count',
        'total_without_vat',
        'total_with_vat',
    ];

    protected $casts = [
        'mapping' => 'array',
        'total_without_vat' => 'float',
        'total_with_vat' => 'float',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(InvoicePerson::class);
    }

    public function lines(): HasManyThrough
    {
        return $this->hasManyThrough(
            InvoiceLine::class,
            InvoicePerson::class,
            'invoice_id',
            'invoice_person_id'
        );
    }
}
