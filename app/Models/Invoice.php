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
        'billing_period',
        'provider',
        'source_filename',
        'mapping',
        'row_count',
        'total_without_vat',
        'total_with_vat',
    ];

    protected $casts = [
        'billing_period' => 'string',
        'provider' => 'string',
        'mapping' => 'array',
        'total_without_vat' => 'float',
        'total_with_vat' => 'float',
    ];

    public const PROVIDER_T_MOBILE = 't-mobile';
    public const PROVIDER_O2 = 'o2';
    public const PROVIDER_OTHER = 'other';

    public const PROVIDERS = [
        self::PROVIDER_T_MOBILE => 'T-Mobile',
        self::PROVIDER_O2 => 'O2',
        self::PROVIDER_OTHER => 'Jiné',
    ];

    protected $appends = [
        'provider_label',
    ];

    public function getProviderLabelAttribute(): ?string
    {
        if ($this->provider === null) {
            return null;
        }

        return self::PROVIDERS[$this->provider] ?? $this->provider;
    }

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
