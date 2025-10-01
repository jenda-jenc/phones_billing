<?php

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO pro jednu službu (tarif/služba/skupina)
 */
class ServiceEntry implements Arrayable
{
    public string $skupina;
    public string $tarif;
    public string $sluzba;
    public float $cena;
    public float $cena_s_dph;

    public function __construct(string $skupina, string $tarif, string $sluzba, float $cena, float $cena_s_dph)
    {
        $this->skupina = $skupina;
        $this->tarif = $tarif;
        $this->sluzba = $sluzba;
        $this->cena = $cena;
        $this->cena_s_dph = $cena_s_dph;
    }

    public function toArray(): array
    {
        return [
            'skupina' => $this->skupina,
            'tarif' => $this->tarif,
            'sluzba' => $this->sluzba,
            'cena' => $this->cena,
            'cena_s_dph' => $this->cena_s_dph,
        ];
    }
}

/**
 * DTO pro jednoho člověka – univerzální pro jakékoliv tarify/služby/skupiny
 */
class ImportedPersonData implements Arrayable
{
    public string $name;
    public string $phone;
    public float $limit = 0;
    public float $celkem = 0;
    public float $celkem_s_dph = 0;
    public float $zaplati = 0;
    public ?float $vat = null;

    /** @var ServiceEntry[] */
    public array $sluzby = [];

    /** @var array[] */
    public array $aplikovanaPravidla = [];

    public function __construct(string $name, string $phone, float $limit = 0)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->limit = $limit;
    }

    public function addService(ServiceEntry $service): void
    {
        $this->sluzby[] = $service;
        $this->celkem += $service->cena;
        $this->celkem_s_dph += $service->cena_s_dph;
    }

    public function addAplikovanePravidlo(array $pravidlo): void
    {
        $this->aplikovanaPravidla[] = $pravidlo;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'limit' => $this->limit,
            'celkem' => round($this->celkem,4),
            'celkem_s_dph' => round($this->celkem_s_dph,4),
            'zaplati' => round($this->zaplati,2),
            'sluzby' => array_map(fn($s) => $s->toArray(), $this->sluzby),
            'aplikovana_pravidla' => $this->aplikovanaPravidla,
        ];
    }
}
