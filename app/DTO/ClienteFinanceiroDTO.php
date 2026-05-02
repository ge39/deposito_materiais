<?php
namespace App\DTO;

class ClienteFinanceiroDTO
{
    public function __construct(
        public string $cliente,
        public float $saldoCarteira,
        public bool $temMovimentacao,
        public float $limiteCredito,
        public string $statusCredito,
        public float $creditoUsado,
        public float $saldoFinal,
        public array $formasPagamento
    ) {}

    public function toArray(): array
    {
        return [
            'cliente' => $this->cliente,
            'saldo_carteira' => $this->saldoCarteira,
            'tem_movimentacao' => $this->temMovimentacao,
            'limite_credito' => $this->limiteCredito,
            'status_credito' => $this->statusCredito,
            'credito_usado' => $this->creditoUsado,
            'saldo_final' => $this->saldoFinal,
            'formas_pagamento' => $this->formasPagamento,
        ];
    }
}