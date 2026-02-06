<?php

namespace App\ViewModels;

use App\Models\Caixa;
use App\Services\CaixaService;

class FechamentoCaixaViewModel
{
    public Caixa $caixa;

    public float $totalEntradas;
    public float $totalSaidas;
    public float $totalEsperado;
    public float $totalSistema;
    public float $divergencia;

    public bool $semMovimento;

    public array $totaisPorForma;

    public function __construct(Caixa $caixa)
    {
        $this->caixa = $caixa;

        $this->totalEntradas = (float) CaixaService::totalEntradasManuais($caixa->id)
            + (float) CaixaService::totalEntradasDinheiro($caixa->id);

        $this->totalSaidas = (float) CaixaService::totalSaidas($caixa->id);

        $this->totalEsperado = (float) CaixaService::total_esperado($caixa->id);

        $this->totaisPorForma = CaixaService::totaisPorForma($caixa->id);

        $this->totalSistema = array_sum($this->totaisPorForma);

        $this->divergencia = bcsub(
            (string) $this->totalEsperado,
            (string) $this->totalSistema,
            2
        );

        $this->semMovimento =
            bccomp($this->totalEntradas, 0, 2) === 0 &&
            bccomp($this->totalSaidas, 0, 2) === 0 &&
            bccomp($this->totalSistema, 0, 2) === 0;
    }
}
