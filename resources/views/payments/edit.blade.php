@extends('template.main')
@section('title', 'EDITAR PAGO DE MENSUALIDAD')

@section('content')
<div class="content-wrapper">

    {{-- ENCABEZADO --}}
    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0 font-weight-bold">@yield('title')</h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">

                    {{-- ERRORES --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('payments.update', $payment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- 🔒 DATOS DEL ESTUDIANTE --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>DNI del estudiante</strong></label>
                                <input type="text" class="form-control" 
                                       value="{{ $payment->enrollment->student->dni }}" readonly>
                            </div>

                            <div class="col-md-8">
                                <label><strong>Estudiante</strong></label>
                                <input type="text" class="form-control"
                                       value="{{ $payment->enrollment->student->nombres }} 
                                              {{ $payment->enrollment->student->apellido_paterno }} 
                                              {{ $payment->enrollment->student->apellido_materno }}"
                                       readonly>
                            </div>
                        </div>

                        <input type="hidden" name="enrollment_id" value="{{ $payment->enrollment_id }}">

                        {{-- DATOS ACADÉMICOS --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Nivel</label>
                                <input type="text" class="form-control"
                                       value="{{ $payment->enrollment->level->nombre }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Grado</label>
                                <input type="text" class="form-control"
                                       value="{{ $payment->enrollment->grade->nombre }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Sección</label>
                                <input type="text" class="form-control"
                                       value="{{ $payment->enrollment->section->nombre }}" readonly>
                            </div>
                        </div>

                        <hr>

                        {{-- MESES --}}
                        <div class="row mb-4" id="mesesContainer">
                            <div class="col-md-12">
                                <label class="fw-bold">MESES PAGADOS / EDITAR PERIODO</label>
                                <div id="mesesBotones" class="d-flex flex-wrap gap-2 mt-2"></div>
                                {{-- input hidden --}}
                                <div id="inputsPeriodos"></div>
                            </div>
                        </div>

                        {{-- MONTO --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>Monto por mes (S/.)</strong></label>
                                <input type="number" name="monto" id="monto" class="form-control"
                                       step="0.01" min="0" required
                                       value="{{ $payment->monto }}">
                            </div>

                            <div class="col-md-4">
                                <label><strong>Descuento (S/.)</strong></label>
                                <input type="number" name="descuento" id="descuento" class="form-control"
                                       step="0.01" min="0" value="{{ $payment->descuento }}">
                            </div>

                            <div class="col-md-4">
                                <label><strong>Total Pagado (S/.)</strong></label>
                                <input type="number" id="total_pagado" class="form-control" readonly
                                       value="{{ number_format($payment->monto - $payment->descuento, 2, '.', '') }}">
                            </div>
                        </div>

                        {{-- MÉTODO DE PAGO Y ESTADO (solo lectura) --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label><strong>Método de pago</strong></label>
                                <input type="text" class="form-control"
                                       value="{{ ucfirst($payment->metodo_pago) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label><strong>Estado</strong></label>
                                <input type="text" class="form-control"
                                       value="{{ ucfirst($payment->estado) }}" readonly>
                            </div>
                        </div>
                        {{-- CAMPOS OCULTOS REQUERIDOS PARA VALIDACIÓN --}}
                        <input type="hidden" name="payment_concept_id" value="{{ $payment->payment_concept_id }}">
                        <input type="hidden" name="metodo_pago" value="{{ $payment->metodo_pago }}">
                        <input type="hidden" name="estado" value="{{ $payment->estado }}">


                        {{-- BOTONES --}}
                        <div class="text-center mt-4">
                            <button class="btn btn-success px-5">
                                <i class="fa-solid fa-floppy-disk"></i> Actualizar Pago
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

{{-- ================= JS ================= --}}
<script>
let mesesSeleccionados = [];
let mesesPagados = @json([$payment->periodo]); // periodo actual

function generarMeses() {
    const cont = document.getElementById('mesesBotones');
    const inputsContainer = document.getElementById('inputsPeriodos');
    cont.innerHTML = '';
    inputsContainer.innerHTML = '';
    mesesSeleccionados = [];

    const year = new Date().getFullYear();

    for (let m = 3; m <= 12; m++) {
        const periodo = `${year}-${String(m).padStart(2, '0')}`;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = periodo;
        btn.className = 'btn btn-outline-primary m-1';

        // marcar el mes actual como seleccionado
        if (mesesPagados.includes(periodo)) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            mesesSeleccionados.push(periodo);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'periodos[]';
            input.value = periodo;
            input.id = 'periodo_' + periodo;
            inputsContainer.appendChild(input);
        }

        btn.onclick = () => toggleMes(btn, periodo);
        cont.appendChild(btn);
    }
}

function toggleMes(btn, periodo) {
    const inputsContainer = document.getElementById('inputsPeriodos');

    if (mesesSeleccionados.includes(periodo)) {
        mesesSeleccionados = mesesSeleccionados.filter(p => p !== periodo);
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
        const input = document.getElementById('periodo_' + periodo);
        if (input) input.remove();
    } else {
        mesesSeleccionados.push(periodo);
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');

        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'periodos[]';
        input.value = periodo;
        input.id    = 'periodo_' + periodo;
        inputsContainer.appendChild(input);
    }
}

document.addEventListener('DOMContentLoaded', generarMeses);

// Calcular total pagado dinámicamente
const monto = document.getElementById('monto');
const descuento = document.getElementById('descuento');
const total = document.getElementById('total_pagado');

function actualizarTotal() {
    const m = parseFloat(monto.value) || 0;
    const d = parseFloat(descuento.value) || 0;
    total.value = (m - d).toFixed(2);
}

monto.addEventListener('input', actualizarTotal);
descuento.addEventListener('input', actualizarTotal);
</script>
@endsection
