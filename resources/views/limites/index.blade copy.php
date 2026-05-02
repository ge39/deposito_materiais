@extends('layouts.app')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📊 Controle de Limite de Clientes</h2>

        <div>
            <button class="btn btn-danger btn-sm" onclick="filtrar('ESTOURADO')">🔴 Estourados</button>
            <button class="btn btn-success btn-sm" onclick="filtrar('OK')">🟢 OK</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrar('')">Todos</button>
        </div>
    </div>

    <div class="mb-3">
        <select id="filtroStatus" class="form-select" style="width:250px;">
            <option value="">Todos</option>
            <option value="OK">OK</option>
            <option value="ESTOURADO">ESTOURADO</option>
        </select>
    </div>

    <div class="card shadow">
        <div class="card-body">

            <table id="tabela-clientes" class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Limite</th>
                        <th>Disponível</th>
                        <th>Usado</th>
                        <th>% Uso</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($clientes as $c)

                        @php
                            $percentual = $c->limite_credito > 0 
                                ? (($c->limite_usado / $c->limite_credito) * 100) 
                                : 0;
                        @endphp

                        <tr>
                            <td>{{ $c->cliente_id }}</td>

                            <td>
                                <strong>{{ $c->nome }}</strong>
                            </td>

                            <td>R$ {{ number_format($c->limite_credito, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($c->limite_disponivel, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($c->limite_usado, 2, ',', '.') }}</td>

                            <td>{{ number_format($percentual, 1) }}%</td>

                            <td>
                                @if($c->status_limite == 'ESTOURADO')
                                    <span class="badge bg-danger">ESTOURADO</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>

                            <td>
                                @if($c->bloqueado)
                                    <button class="btn btn-sm btn-success"
                                        onclick="desbloquearCliente({{ $c->cliente_id }}, '{{ $c->nome }}')">
                                        🔓 Desbloquear
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-danger"
                                        onclick="bloquearCliente({{ $c->cliente_id }}, '{{ $c->nome }}')">
                                        🚫 Bloquear
                                    </button>
                                @endif
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
       
        </div>
    </div>

</div>

<!-- Scripts -->
 <!-- DataTables -->
<!-- 1. jQuery PRIMEIRO -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- 2. Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- 4. Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<!-- 5. Excel (ESSENCIAL) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- 6. Exportações -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<!-- 7. PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    let tabela;

    $(document).ready(function () {

        tabela = $('#tabela-clientes').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📥 Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '📄 PDF',
                    className: 'btn btn-danger',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6]
                    }
                }
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                paginate: {
                    next: "Próximo",
                    previous: "Anterior"
                }
            }
        });

        $('#filtroStatus').on('change', function () {
            tabela.column(6).search(this.value).draw();
        });

    });

    function filtrar(status) {
        $('#filtroStatus').val(status).trigger('change');
    }

    function bloquearCliente(id, nome) {
        if (!confirm(`Bloquear ${nome}?`)) return;

        fetch(`/clientes/${id}/bloquear`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(() => location.reload());
    }

    function desbloquearCliente(id, nome) {
        if (!confirm(`Desbloquear ${nome}?`)) return;

        fetch(`/clientes/${id}/desbloquear`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(() => location.reload());
    }
</script>

 @endsection