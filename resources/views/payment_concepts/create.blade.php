@extends('template.main')
@section('title', 'NUEVO CONCEPTO DE PAGO')

@section('content')
<div class="content-wrapper">

    <div class="content-header text-center">
        <h1 class="m-0"><strong>@yield('title')</strong></h1>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment_concepts.store') }}">
                        @csrf

                        <div class="form-group">
                            <label><strong>Nombre del Concepto</strong></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="es_mensual" id="es_mensual" value="1" class="form-check-input">
                                <label for="es_mensual" class="form-check-label">
                                    <strong>¿Es Mensualidad?</strong>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="activo" id="activo" value="1"
                                       class="form-check-input" checked>
                                <label for="activo" class="form-check-label">
                                    <strong>Activo</strong>
                                </label>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                            <a href="{{ route('payment_concepts.index') }}" class="btn btn-secondary px-4 ml-2">
                                Volver
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
