@extends('template.main')
@section('title', 'PAGOS REGISTRADOS')

@section('content')
<div class="content-wrapper">

    {{-- ENCABEZADO --}}
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    {{-- CONTENIDO --}}
    <div class="content">
        <div class="container-fluid">

            {{-- ALERTA --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="card shadow-sm">

                {{-- HEADER CON BUSQUEDA --}}
                <div class="card-header d-flex justify-content-between flex-wrap">

                    {{-- BUSCAR --}}
                    <form method="GET" action="{{ route('payments.index') }}" class="form-inline mb-2">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control mr-2"
                               placeholder="Buscar por DNI o nombre">

                        <button class="btn btn-primary mr-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>

                        @if(request('search'))
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                                Limpiar
                            </a>
                        @endif
                    </form>

                    {{-- BOTONES --}}
                    <div class="mb-2">
                        <a href="{{ route('payments.create') }}" class="btn btn-success">
                            <i class="fa-solid fa-plus"></i> Registrar Pago
                        </a>

                        <button class="btn btn-secondary ml-2" data-toggle="modal" data-target="#modalBuscarDni">
                            <i class="fa-solid fa-id-card"></i> Buscar por DNI
                        </button>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('reports.payments') }}"
                            class="btn btn-info ml-2">
                            <i class="fa-solid fa-chart-column"></i> Reporte
                        </a>
                        @endif
                    </div>

                </div>

                {{-- TABLA --}}
                <div class="card-body">

                    @if($payments->isEmpty())
                        <div class="text-center text-muted">
                            <i class="fa-solid fa-money-bill-wave fa-2x mb-2"></i>
                            <p>No hay pagos registrados.</p>
                        </div>
                    @else
                        <div class="table-responsive mb-2">
                            <table class="table table-striped table-bordered table-hover text-center align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>ESTUDIANTE</th>
                                        <th>CONCEPTO</th>
                                        <th>PERIODO</th>
                                        <th>MONTO</th>
                                        <th>DESCUENTO</th>
                                        <th>FECHA PAGO</th>
                                        <th>MÉTODO</th>
                                        <th>ESTADO</th>
                                        <th>COMPROBANTE</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $p)
                                        @php
                                            // Cantidad de pagos que comparten el mismo PDF (voucher)
                                            $grupo = \App\Models\Payment::where('voucher', $p->voucher)->count();
                                        @endphp

                                        <tr>
                                            <td>{{ $loop->iteration + ($payments->currentPage()-1)*$payments->perPage() }}</td>

                                            {{-- ESTUDIANTE --}}
                                            <td class="text-left">
                                                <strong>{{ $p->enrollment->student->nombres }} {{ $p->enrollment->student->apellido_paterno }}</strong><br>
                                                <small class="text-muted">
                                                    DNI: {{ $p->enrollment->student->dni }}
                                                </small><br>
                                                <small>
                                                    {{ $p->enrollment->grade->nombre ?? '' }} - {{ $p->enrollment->section->nombre ?? '' }}
                                                </small>
                                            </td>

                                            {{-- CONCEPTO --}}
                                            <td>{{ $p->paymentConcept->nombre ?? '—' }}</td>

                                            {{-- PERIODO --}}
                                            <td>{{ $p->periodo ?? '—' }}</td>

                                            {{-- MONTO --}}
                                            <td><strong>S/. {{ number_format($p->monto, 2) }}</strong></td>

                                            {{-- DESCUENTO --}}
                                            <td>
                                                @if($p->descuento > 0)
                                                    <span class="badge badge-success">
                                                        - S/. {{ number_format($p->descuento, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            {{-- FECHA --}}
                                            <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>

                                            {{-- MÉTODO --}}
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst($p->metodo_pago) }}
                                                </span>
                                            </td>

                                            {{-- ESTADO --}}
                                            <td>
                                                <span class="badge 
                                                    @if($p->estado == 'pagado') badge-success 
                                                    @elseif($p->estado == 'pendiente') badge-warning 
                                                    @else badge-secondary @endif">
                                                    {{ ucfirst($p->estado) }}
                                                </span>
                                            </td>

                                            {{-- COMPROBANTE --}}
                                            <td>
                                                @if($grupo > 1)
                                                    <span class="badge badge-warning">Grupal</span>
                                                @else
                                                    <span class="badge badge-info">Individual</span>
                                                @endif
                                            </td>

                                            {{-- ACCIONES --}}
                                            <td>
                                                {{-- VER PDF --}}
                                                @if($p->voucher)
                                                    <a href="{{ route('voucher.view', basename($p->voucher)) }}"
                                                       target="_blank"
                                                       class="btn btn-danger btn-sm mb-1"
                                                       title="Ver Comprobante">
                                                        <i class="fa-solid fa-file-pdf"></i>
                                                    </a>
                                                @endif

                                                {{-- SI ES GRUPAL → BLOQUEADO --}}
                                                @if($grupo > 1)
                                                    <button class="btn btn-secondary btn-sm mb-1" disabled
                                                            title="Pertenece a un comprobante grupal — no editable">
                                                        <i class="fa-solid fa-lock"></i>
                                                    </button>
                                                @else
                                                    {{-- EDITAR --}}
                                                    <a href="{{ route('payments.edit', $p->id) }}"
                                                       class="btn btn-warning btn-sm mb-1"
                                                       title="Editar Pago">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    {{-- ELIMINAR --}}
                                                    @if(auth()->user()->role === 'admin')
                                                        <form action="{{ route('payments.destroy', $p->id) }}"
                                                              method="POST"
                                                              style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-sm mb-1"
                                                                    onclick="return confirm('¿Eliminar pago?')"
                                                                    title="Eliminar Pago">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- INFO + PAGINACIÓN --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <small>
                                Mostrando del {{ $payments->firstItem() }}
                                al {{ $payments->lastItem() }}
                                de un total de {{ $payments->total() }} registros
                            </small>
                            {{ $payments->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL BUSCAR POR DNI ================= --}}
<div class="modal fade" id="modalBuscarDni" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Pagos del Estudiante</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="dni" class="form-control" placeholder="Ingrese DNI">
                    <div class="input-group-append">
                        <button class="btn btn-primary" onclick="buscarPagosPorDni()">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </div>

                <table class="table table-bordered text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>Concepto</th>
                            <th>Periodo</th>
                            <th>Monto</th>
                            <th>Descuento</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="resultadoPagos">
                        <tr>
                            <td colspan="6">Ingrese un DNI para buscar</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@if(session('voucher_pdf'))
<script>
    window.open("{{ session('voucher_pdf') }}", "_blank");
</script>
@endif

{{-- ================= JS ================= --}}
<script>
function buscarPagosPorDni() {
    let dni = document.getElementById('dni').value.trim();
    let tbody = document.getElementById('resultadoPagos');

    if (!dni) {
        alert('Ingrese el DNI');
        return;
    }

    fetch(`/payments/buscar/${dni}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="6">No hay pagos registrados</td></tr>`;
                return;
            }

            data.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td>${p.payment_concept ? p.payment_concept.nombre : '—'}</td>
                        <td>${p.periodo ?? '—'}</td>
                        <td>S/. ${Number(p.monto).toFixed(2)}</td>
                        <td>${p.descuento > 0 ? '- S/. ' + Number(p.descuento).toFixed(2) : '—'}</td>
                        <td>${p.fecha_pago}</td>
                        <td>${p.estado}</td>
                    </tr>
                `;
            });
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="6">Error al buscar</td></tr>`;
        });
}
</script>

@endsection
