

<!-- pdv/itens.blade.php -->
@extends('layouts.app')
@section('content')
<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- Painel esquerdo: adicionar itens -->
        <div class="col-lg-4">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-dark text-white rounded-top-4">
                    <h6 class="mb-0">Adicionar Item</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pdv.adicionarItem', $venda->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Produto</label>
                            <select name="produto_id" class="form-select" required>
                                @foreach($produtos as $p)
                                    <option value="{{ $p->id }}">{{ $p->nome }} ({{ $p->codigo ?? 'sem cód.' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantidade" class="form-control" min="1" required>
                        </div>
                        <button class="btn btn-dark w-100 py-2">Adicionar</button>
                    </form>
                </div>
            </div>
        </div>
