@extends('template.main')
@section('title', 'REGISTRAR PAGO')

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
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf

                        <!-- BUSCAR ESTUDIANTE -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>DNI del estudiante</strong></label>
                                <input type="text" id="dni" class="form-control" maxlength="8" placeholder="Ingrese DNI" required>
                            </div>

                            <div class="col-md-8">
                                <label><strong>Estudiante</strong></label>
                                <input type="text" id="estudiante" class="form-control" readonly>
                            </div>
                        </div>

                        <input type="hidden" name="enrollment_id" id="enrollment_id">

                        <!-- DATOS MATRÍCULA -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label>Nivel</label>
                                <input type="text" id="nivel" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Grado</label>
                                <input type="text" id="grado" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Sección</label>
                                <input type="text" id="seccion" class="form-control" readonly>
                            </div>
                        </div>

                        <hr>

                        <!-- CONCEPTO DE PAGO -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label><strong>Concepto de pago</strong></label>
                                <select name="payment_concept_id" id="concepto" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    @foreach(\App\Models\PaymentConcept::where('activo', true)->get() as $concepto)
                                        <option value="{{ $concepto->id }}" data-mensual="{{ $concepto->es_mensual ? 1 : 0 }}">
                                            {{ $concepto->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_concept_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6" id="periodoContainer" style="display:none;">
                                <label><strong>Periodo (YYYY-MM)</strong></label>
                                <input type="month" name="periodo" id="periodo" class="form-control">
                                @error('periodo')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- MONTO Y DESCUENTO -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>Monto (S/.)</strong></label>
                                <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0" required>
                                @error('monto')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label><strong>Descuento (S/.)</strong></label>
                                <input type="number" name="descuento" id="descuento" class="form-control" step="0.01" min="0" value="0">
                                @error('descuento')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4">
                                <label><strong>Método de pago</strong></label>
                                <select name="metodo_pago" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="yape">Yape</option>
                                    <option value="plin">Plin</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                                @error('metodo_pago')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <!-- BOTÓN -->
                        <div class="text-center mt-4">
                            <button class="btn btn-success px-5">
                                <i class="fa-solid fa-floppy-disk"></i> Registrar Pago
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary px-4">
                                <i class="fa-solid fa-arrow-left"></i> Volver
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================== JAVASCRIPT ================== -->
<script>
// Mostrar campo periodo si el concepto es mensual
document.getElementById('concepto').addEventListener('change', function() {
    const mensual = this.selectedOptions[0]?.dataset.mensual == "1";
    document.getElementById('periodoContainer').style.display = mensual ? 'block' : 'none';
});

// Buscar matrícula por DNI
document.getElementById('dni').addEventListener('blur', function() {
    const dni = this.value.trim();
    if (!dni) return;

    fetch(`/buscar-matricula/${dni}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                limpiarCampos();
                return;
            }

            document.getElementById('enrollment_id').value = data.enrollment_id;
            document.getElementById('estudiante').value = data.estudiante;
            document.getElementById('nivel').value = data.nivel;
            document.getElementById('grado').value = data.grado;
            document.getElementById('seccion').value = data.seccion;
        })
        .catch(() => alert('Error al buscar matrícula'));
});

function limpiarCampos() {
    ['enrollment_id','estudiante','nivel','grado','seccion'].forEach(id => {
        document.getElementById(id).value = '';
    });
}
</script>

@endsection
