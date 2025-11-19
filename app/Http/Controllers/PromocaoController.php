<?php

namespace App\Http\Controllers;

use App\Models\Promocao;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PromocaoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:gerenciar-promocoes']);
    }

    // LISTAGEM DE PROMOÇÕES
    public function index(Request $request)
    {
        $query = Promocao::with(['produto', 'categoria'])
            ->where('status', 1)
            ->orderByDesc('created_at');

        // se o usuário filtrar por status, aplica; caso contrário, lista todas
        foreach (['tipo_abrangencia', 'produto_id', 'categoria_id', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        $promocoes = $query->paginate(15)->appends($request->query());

        return view('promocoes.index', compact('promocoes'));
    }

    // FORMULÁRIO DE CRIAÇÃO
    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('promocoes.create', compact('produtos', 'categorias'));
    }

    // SALVAR NOVA PROMOÇÃO
    public function store(Request $request)
    {
        // Validação básica antes do validated() para checar conflito de promo por produto
        if ($request->tipo_abrangencia === 'produto' && $request->produto_id && $request->input('status') == 1) {
            $hoje = Carbon::today();
            $promoVigente = Promocao::where('produto_id', $request->produto_id)
                ->where('status', 1)
                ->whereDate('promocao_inicio', '<=', $hoje)
                ->whereDate('promocao_fim', '>=', $hoje)
                ->exists();

            if ($promoVigente) {
                $produto = Produto::find($request->produto_id);
                throw ValidationException::withMessages([
                    'produto_id' => "O produto '{$produto->nome}' já participa de uma promoção vigente."
                ]);
            }
        }

        $validated = $request->validate([
            'tipo_abrangencia' => 'required|in:produto,categoria,geral',
            'produto_id' => 'required_if:tipo_abrangencia,produto|nullable|exists:produtos,id',
            'categoria_id' => 'required_if:tipo_abrangencia,categoria|nullable|exists:categorias,id',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_valor' => 'nullable|numeric|min:0',
            'preco_original' => 'nullable|numeric|min:0',
            'preco_promocional' => 'nullable|numeric|min:0',
            'promocao_inicio' => 'required|date',
            'promocao_fim' => 'required|date|after_or_equal:promocao_inicio',
            'status' => 'nullable|in:0,1',
        ]);

        // cria usando valores validados; se status não for enviado, deixa 0 (inativa) por segurança
        $promocao = Promocao::create([
            'tipo_abrangencia'    => $validated['tipo_abrangencia'],
            'produto_id'          => $validated['produto_id'] ?? null,
            'categoria_id'        => $validated['categoria_id'] ?? null,
            'desconto_percentual' => $validated['desconto_percentual'] ?? 0,
            'acrescimo_percentual'=> $validated['acrescimo_percentual'] ?? 0,
            'acrescimo_valor'     => $validated['acrescimo_valor'] ?? 0,
            'preco_original'      => $validated['preco_original'] ?? 0,
            'preco_promocional'   => $validated['preco_promocional'] ?? 0,
            'promocao_inicio'     => $validated['promocao_inicio'],
            'promocao_fim'        => $validated['promocao_fim'],
            'status'              => 1,
        ]);

        // Não aplicamos nada no Controller — o Observer (PromocaoObserver) fará o trabalho
        return $this->flashMessage($promocao, 'created');
    }

    // FORMULÁRIO DE EDIÇÃO
    public function edit(Promocao $promocao)
    {
        $produtos = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('promocoes.edit', compact('promocao', 'produtos', 'categorias'));
    }

    // ATUALIZAR PROMOÇÃO
    public function update(Request $request, Promocao $promocao)
    {
        // Se a intenção for ativar a promoção agora, checa conflito com outra vigente (exclui a atual)
        if ($request->has('status') && (int)$request->input('status') === 1
            && $promocao->tipo_abrangencia === 'produto' && $promocao->produto_id) {

            $hoje = Carbon::today();
            $promoVigente = Promocao::where('produto_id', $promocao->produto_id)
                ->where('status', 1)
                ->whereDate('promocao_inicio', '<=', $hoje)
                ->whereDate('promocao_fim', '>=', $hoje)
                ->where('id', '<>', $promocao->id)
                ->exists();

            if ($promoVigente) {
                throw ValidationException::withMessages([
                    'produto_id' => "O produto '{$promocao->produto->nome}' já participa de uma promoção vigente."
                ]);
            }
        }

        // validação mais permissiva que permite ajustar desconto/data sem travar pelo valor antigo
        $validated = $request->validate([
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'preco_promocional' => 'nullable|numeric|min:0',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
            'promocao_inicio' => 'nullable|date',
            'status' => 'nullable|in:0,1',
        ]);

        // aplica somente os campos enviados (não sobrescreve tudo)
        $updateData = [];
        foreach (['desconto_percentual', 'preco_promocional', 'promocao_inicio', 'promocao_fim', 'status'] as $fld) {
            if (array_key_exists($fld, $validated)) {
                $updateData[$fld] = $validated[$fld];
            }
        }

        if (!empty($updateData)) {
            $promocao->update($updateData);
        }

        // Observer irá aplicar/restaurar quando detectar mudança de status ou expiração

        return $this->flashMessage($promocao, 'updated');
    }

    // DELETAR PROMOÇÃO
    public function destroy(Promocao $promocao)
    {
        // Observer -> deleting() cuidará de restaurar preços
        $promocao->delete();

        return $this->flashMessage($promocao, 'deleted');
    }

    // Encerrar promoção manualmente
    public function encerrar($id)
    {
        $promocao = Promocao::findOrFail($id);

        // marcar como encerrada (status 0) e atualizar fim para hoje — usar save() para disparar observer
        $promocao->status = 0;
        $promocao->promocao_fim = Carbon::today();
        $promocao->save();

        // Observer -> updated() irá restaurar os preços
        return $this->flashMessage($promocao, 'ended');
    }

    public function toggle(Promocao $promocao)
    {
        return $this->toggleStatus($promocao);
    }

    // Alternar status da promoção
    public function toggleStatus(Promocao $promocao)
    {
        $promocao->status = !$promocao->status;
        $promocao->save(); // Observer aplica/restaura conforme necessário

        return redirect()
            ->route('promocoes.index')
            ->with(
                'success',
                $promocao->status
                    ? 'Promoção ativada com sucesso!'
                    : 'Promoção desativada com sucesso!'
            );
    }

    // VISUALIZAR PROMOÇÃO
    public function show(Promocao $promocao)
    {
        return view('promocoes.show', compact('promocao'));
    }

    private function flashMessage(Promocao $promocao, string $action)
    {
        switch($action) {
            case 'created':
                return redirect()->route('promocoes.index')
                    ->with('success', 'Promoção criada com sucesso!');
            case 'updated':
                return redirect()->route('promocoes.index')
                    ->with('success', 'Promoção atualizada com sucesso!');
            case 'ended':
                return redirect()->route('promocoes.index')
                    ->with('success', 'Promoção encerrada com sucesso!');
            case 'deleted':
                return redirect()->route('promocoes.index')
                    ->with('success', 'Promoção excluída com sucesso!');
            default:
                return redirect()->route('promocoes.index');
        }
    }
}
