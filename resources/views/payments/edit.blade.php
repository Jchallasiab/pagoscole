@extends('template.main')
@section('title', 'EDITAR PAGO')

@section('content')
<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0 font-weight-bold">@yield('title')</h1>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="{{ route('payments.update', $payment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- DATOS DEL ESTUDIANTE -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label><strong>DNI</strong></label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->enrollment->student->dni }}" readonly>
                            </div>

                            <div class="col-md-5">
                                <label><strong>Estudiante</strong></label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->enrollment->student->nombres }} 
                                              {{ $payment->enrollment->student->apellido_paterno }} 
                                              {{ $payment->enrollment->student->apellido_materno }}" 
                                       readonly>
                            </div>

                            <div class="col-md-4">
                                <label><strong>Grado y Sección</strong></label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->enrollment->grade->nombre ?? '-' }} - 
                                              {{ $payment->enrollment->section->nombre ?? '-' }}" 
                                       readonly>
                            </div>
                        </div>

                        <hr>

                        <!-- CONCEPTO Y PERIODO -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label><strong>Concepto de pago</strong></label>
                                <select name="payment_concept_id" id="concepto" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    @foreach($conceptos as $c)
                                        <option value="{{ $c->id }}" 
                                                data-mensual="{{ $c->es_mensual ? 1 : 0 }}"
                                                {{ $payment->payment_concept_id == $c->id ? 'selected' : '' }}>
                                            {{ $c->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_concept_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6" id="periodoContainer" style="{{ $payment->periodo ? '' : 'display:none;' }}">
                                <label><strong>Periodo (YYYY-MM)</strong></label>
                                <input type="month" name="periodo" id="periodo"
                                       class="form-control"
                                       value="{{ $payment->periodo }}">
                                @error('periodo')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- MONTOS -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label><strong>Monto (S/.)</strong></label>
                                <input type="number" name="monto" id="monto" class="form-control"
                                       value="{{ $payment->monto }}" step="0.01" min="0" required>
                                @error('monto')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label><strong>Descuento (S/.)</strong></label>
                                <input type="number" name="descuento" id="descuento" class="form-control"
                                       value="{{ $payment->descuento }}" step="0.01" min="0">
                                @error('descuento')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label><strong>Método de pago</strong></label>
                                <select name="metodo_pago" class="form-control" required>
                                    <option value="efectivo" {{ $payment->metodo_pago == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                    <option value="yape" {{ $payment->metodo_pago == 'yape' ? 'selected' : '' }}>Yape</option>
                                    <option value="plin" {{ $payment->metodo_pago == 'plin' ? 'selected' : '' }}>Plin</option>
                                    <option value="transferencia" {{ $payment->metodo_pago == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                </select>
                                @error('metodo_pago')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- ESTADO -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>Estado</strong></label>
                                <select name="estado" class="form-control">
                                    <option value="pagado" {{ $payment->estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                    <option value="pendiente" {{ $payment->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="validado" {{ $payment->estado == 'validado' ? 'selected' : '' }}>Validado</option>
                                </select>
                                @error('estado')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="text-center mt-4">
                            <button class="btn btn-success px-5">
                                <i class="fa-solid fa-floppy-disk"></i> Actualizar Pago
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary px-4 ml-2">
                                <i class="fa-solid fa-arrow-left"></i> Volver
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
// Mostrar campo "periodo" si el concepto es mensual
document.getElementById('concepto').addEventListener('change', function() {
    const mensual = this.selectedOptions[0]?.dataset.mensual == "1";
    document.getElementById('periodoContainer').style.display = mensual ? 'block' : 'none';
});
</script>

@endsection
