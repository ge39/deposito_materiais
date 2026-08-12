<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntregaSlaController extends Controller
{
    private const LIMITE_AVISO_MINUTOS = 480;

    private const LIMITE_CRITICO_MINUTOS = 720;

    private const STATUS_ENCERRADOS = [
        'entregue',
        'entregue_parcial',
        'parcial',
        'finalizada',
        'finalizado',
        'finalizada_com_ocorrencia',
        'entregue_finalizada_com_ocorrencia',
        'concluida',
        'concluido',
        'nao_entregue',
        'recusada',
        'recusado',
        'devolvida',
        'devolvido',
        'cancelada',
        'cancelado',
    ];

    public function index(): JsonResponse
    {
        $expressaoMinutos = 'TIMESTAMPDIFF(MINUTE, '
            . 'COALESCE(status_alterado_em, updated_at, created_at), NOW())';

        /*
         * Aceita tanto "Entregue parcial" quanto "entregue_parcial".
         * Isso impede que uma variação de escrita reative alertas encerrados.
         */
        $expressaoStatusNormalizado = "REPLACE(REPLACE("
            . "LOWER(TRIM(status)), ' ', '_'), '-', '_')";

        $alertas = Entrega::query()
            ->whereNotIn(
                DB::raw($expressaoStatusNormalizado),
                self::STATUS_ENCERRADOS
            )
            ->whereRaw(
                $expressaoMinutos . ' >= ?',
                [self::LIMITE_AVISO_MINUTOS]
            )
            ->select([
                'id',
                'codigo_entrega',
                'status',
                'status_alterado_em',
                'updated_at',
                'created_at',
            ])
            ->selectRaw($expressaoMinutos . ' AS minutos_parado')
            ->orderByRaw(
                'CASE WHEN ' . $expressaoMinutos . ' >= ? THEN 0 ELSE 1 END',
                [self::LIMITE_CRITICO_MINUTOS]
            )
            ->orderByDesc('minutos_parado')
            ->get()
            ->map(function (Entrega $entrega): array {
                $minutos = max(
                    0,
                    (int) $entrega->getAttribute('minutos_parado')
                );
                $critico = $minutos >= self::LIMITE_CRITICO_MINUTOS;

                return [
                    'id' => (int) $entrega->id,
                    'codigo' => $entrega->codigo_entrega
                        ?: 'ENT-' . $entrega->id,
                    'status' => (string) $entrega->status,
                    'status_rotulo' => Str::of((string) $entrega->status)
                        ->replace('_', ' ')
                        ->lower()
                        ->ucfirst()
                        ->toString(),
                    'minutos_parado' => $minutos,
                    'tempo_parado' => $this->formatarTempo($minutos),
                    'nivel' => $critico ? 'critico' : 'aviso',
                    'mensagem' => sprintf(
                        '%s está em “%s” há %s.',
                        $entrega->codigo_entrega ?: 'ENT-' . $entrega->id,
                        Str::of((string) $entrega->status)
                            ->replace('_', ' ')
                            ->lower()
                            ->ucfirst(),
                        $this->formatarTempo($minutos)
                    ),
                    'url' => route('entregas.show', $entrega->id),
                ];
            })
            ->values();

        return response()->json([
            'consultado_em' => now()->toIso8601String(),
            'limites' => [
                'aviso_horas' => 8,
                'critico_horas' => 12,
            ],
            'resumo' => [
                'total' => $alertas->count(),
                'avisos' => $alertas
                    ->where('nivel', 'aviso')
                    ->count(),
                'criticos' => $alertas
                    ->where('nivel', 'critico')
                    ->count(),
            ],
            'alertas' => $alertas,
        ]);
    }

    private function formatarTempo(int $minutos): string
    {
        $dias = intdiv($minutos, 1440);
        $horas = intdiv($minutos % 1440, 60);
        $minutosRestantes = $minutos % 60;
        $partes = [];

        if ($dias > 0) {
            $partes[] = $dias . 'd';
        }

        $partes[] = $horas . 'h';
        $partes[] = str_pad(
            (string) $minutosRestantes,
            2,
            '0',
            STR_PAD_LEFT
        ) . 'min';

        return implode(' ', $partes);
    }
}