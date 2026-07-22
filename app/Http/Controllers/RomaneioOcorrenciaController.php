<?php

namespace App\Http\Controllers;

use App\Models\Romaneio;
use App\Models\RomaneioEquipe;
use App\Models\RomaneioOcorrencia;
use App\Models\RomaneioOcorrenciaAnexo;
use App\Models\User;
use App\Services\Expedicao\RomaneioOcorrenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RomaneioOcorrenciaController extends Controller
{
    public function __construct(private readonly RomaneioOcorrenciaService $ocorrenciaService)
    {
    }

    public function index(Romaneio $romaneio): View
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.venda.cliente',
            'entrega.orcamento.cliente',
            'ocorrencias' => fn ($query) => $query->latest('id'),
            'ocorrencias.entrega',
            'ocorrencias.romaneioItem.entregaItem.vendaItem.produto',
            'ocorrencias.romaneioItem.entregaItem.itemOrcamento.produto',
            'ocorrencias.responsavelAnalise',
            'ocorrencias.autorizador',
            'ocorrencias.anexos.usuarioEnvio',
            'ocorrencias.historicos.usuarioRegistro',
            'ocorrencias.orcamentoReposicao',
        ]);

        $responsaveis = User::query()
            ->orderBy('name')
            ->get();

        $equipe = RomaneioEquipe::query()
            ->with([
                'motorista',
                'ajudante',
                'veiculo',
            ])
            ->where(
                'romaneio_id',
                $romaneio->id
            )
            ->latest('id')
            ->first();

        return view(
            'romaneios.ocorrencias.index',
            compact(
                'romaneio',
                'responsaveis',
                'equipe'
            )
        );
    }

    public function atribuirResponsavel(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'responsavel_analise_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $this->ocorrenciaService->atribuirResponsavel(
            $ocorrencia,
            (int) $dados['responsavel_analise_id']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Responsável pela análise definido com sucesso.'
            );
    }

    public function anexarEvidencia(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'tipo' => [
                'required',
                Rule::in([
                    'Foto',
                    'Documento',
                    'Comprovante',
                    'Assinatura',
                    'Outro',
                ]),
            ],
            'descricao' => [
                'nullable',
                'string',
                'max:500',
            ],
            'arquivo' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $mimeType = $arquivo->getMimeType();
        $tamanhoBytes = $arquivo->getSize();
        $diretorioRelativo = 'uploads/romaneios/'
            . $romaneio->id
            . '/ocorrencias/'
            . $ocorrencia->id;

        $diretorioAbsoluto = public_path(
            $diretorioRelativo
        );

        File::ensureDirectoryExists(
            $diretorioAbsoluto
        );

        $extensao = strtolower(
            $arquivo->getClientOriginalExtension()
        );

        $nomeArquivo = (string) Str::uuid()
            . ($extensao !== '' ? '.' . $extensao : '');

        $arquivo->move(
            $diretorioAbsoluto,
            $nomeArquivo
        );

        $caminho = $diretorioRelativo
            . '/'
            . $nomeArquivo;

        $caminhoAbsoluto = public_path(
            $caminho
        );

        try {
            $this->ocorrenciaService->registrarAnexo(
                $ocorrencia,
                [
                    'tipo' => $dados['tipo'],
                    'descricao' => $dados['descricao'] ?? null,
                    'nome_original' => $nomeOriginal,
                    'caminho' => $caminho,
                    'mime_type' => $mimeType,
                    'tamanho_bytes' => $tamanhoBytes,
                    'capturado_em' => now(),
                    'hash_arquivo' => is_file($caminhoAbsoluto)
                        ? hash_file('sha256', $caminhoAbsoluto)
                        : null,
                ]
            );
        } catch (\Throwable $exception) {
            if (File::exists($caminhoAbsoluto)) {
                File::delete($caminhoAbsoluto);
            }

            throw $exception;
        }

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Evidência anexada com sucesso.'
            );
    }

    public function autorizar(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'justificativa_autorizacao' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        $this->ocorrenciaService->autorizar(
            $ocorrencia,
            $dados['justificativa_autorizacao']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência autorizada com sucesso.'
            );
    }

    public function removerEvidencia(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia, RomaneioOcorrenciaAnexo $anexo): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        abort_unless(
            (int) $anexo->romaneio_ocorrencia_id
            === (int) $ocorrencia->id,
            404
        );

        $this->ocorrenciaService->removerAnexo(
            $ocorrencia,
            $anexo
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Evidência removida com sucesso.'
            );
    }

    public function registrarDecisao(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'classificacao_final' => [
                'required',
                Rule::in([
                    'Extravio',
                    'Avaria',
                    'Recusa',
                    'Devolucao',
                    'Perda',
                    'Divergencia',
                    'Improcedente',
                    'Outro',
                ]),
            ],
            'decisao' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
            'destino_estoque' => [
                'required',
                Rule::in([
                    'Sem_movimentacao',
                    'Quarentena',
                    'Reintegracao',
                    'Perda',
                    'Reposicao',
                ]),
            ],
            'orcamento_reposicao_id' => [
                'nullable',
                'integer',
                'exists:orcamentos,id',
            ],
        ]);

        $this->ocorrenciaService->registrarDecisao(
            $ocorrencia,
            $dados
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Decisão administrativa registrada com sucesso.'
            );
    }

    public function liberarFechamento(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $this->ocorrenciaService->liberarFechamentoLogistico(
            $ocorrencia
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Fechamento logístico liberado para a ocorrência.'
            );
    }

    public function resolver(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'solucao' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ]);

        $this->ocorrenciaService->resolver(
            $ocorrencia,
            $dados['solucao']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência resolvida com sucesso.'
            );
    }

    public function cancelar(Request $request, Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): RedirectResponse
    {
        $this->validarPertencimento(
            $romaneio,
            $ocorrencia
        );

        $dados = $request->validate([
            'justificativa' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ]);

        $this->ocorrenciaService->cancelar(
            $ocorrencia,
            $dados['justificativa']
        );

        return redirect()
            ->route(
                'romaneios.ocorrencias.index',
                $romaneio
            )
            ->with(
                'success',
                'Ocorrência cancelada com sucesso.'
            );
    }

    private function validarPertencimento(Romaneio $romaneio, RomaneioOcorrencia $ocorrencia): void
    {
        abort_unless(
            (int) $ocorrencia->romaneio_id
            === (int) $romaneio->id,
            404
        );
    }
}