@extends('template.main')
@section('title', 'CONCEPTOS DE PAGO')

@section('content')
<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0 text-primary">Listado de Conceptos de Pago</h5>
                    <a href="{{ route('payment_concepts.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nuevo Concepto
                    </a>
                </div>

                <div class="card-body">
                    @if($payment_concepts->isEmpty())
                        <p class="text-center text-muted">
                            No hay conceptos de pago registrados.
                        </p>
                    @else
                        <table class="table table-bordered table-striped text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment_concepts as $c)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $c->nombre }}</td>
                                    <td>
                                        <span class="badge {{ $c->es_mensual ? 'badge-info' : 'badge-secondary' }}">
                                            {{ $c->es_mensual ? 'Mensualidad' : 'Único' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $c->activo ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $c->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('payment_concepts.edit', $c->id) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <form action="{{ route('payment_concepts.destroy', $c->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar concepto de pago?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
