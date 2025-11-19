<?php

namespace App\Observers;

use App\Models\Promocao;
use App\Services\PromocaoService;

class PromocaoObserver
{
    protected $service;

    public function __construct(PromocaoService $service)
    {
        $this->service = $service;
    }

    // Quando criar → aplicar
    public function created(Promocao $promocao)
    {
        if ($promocao->status == 1) {
            $this->service->aplicar($promocao);
        }
    }

    // Quando atualizar → aplicar ou restaurar
    public function updated(Promocao $promocao)
    {
        \Log::debug('Observer updated for promocao', [
        'id' => $promocao->id,
        'status' => $promocao->status,
        'promocao_fim' => (string) $promocao->promocao_fim,
        'now' => now()->toDateTimeString(),
    ]);
        // Foi ativada
        if ($promocao->isDirty('status') && $promocao->status == 1) {
            $this->service->aplicar($promocao);
        }

        // Foi desativada
        if ($promocao->isDirty('status') && $promocao->status == 0) {
            $this->service->restaurar($promocao);
        }

        // Se expirar pela data e ainda está ativa
        if ($promocao->status == 1 && now()->gt($promocao->promocao_fim)) {

            // altera o status sem disparar evento
            $promocao->status = 0;
            $promocao->saveQuietly();

            // restaura os preços
            $this->service->restaurar($promocao);
        }
    }

    // Quando deletar → restaurar
    public function deleting(Promocao $promocao)
    {
        $this->service->restaurar($promocao);
    }

    public function retrieved(Promocao $promocao)
{
    if ($promocao->status == 1 && now()->gt($promocao->promocao_fim)) {

        \Log::debug('Promo expirada automaticamente', [
            'id' => $promocao->id,
            'fim' => $promocao->promocao_fim,
            'agora' => now()->toDateTimeString()
        ]);

        // desativa sem loop infinito
        $promocao->status = 0;
        $promocao->saveQuietly();

        // restaura apenas uma vez
        app(\App\Services\PromocaoService::class)->restaurar($promocao);
    }
}


}
