@extends('template.main')
@section('title', 'REGISTRAR PAGOS')

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

                    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                        @csrf

                        {{-- BUSCAR ESTUDIANTE --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>DNI del estudiante</strong></label>
                                <input type="text" id="dni" class="form-control"
                                       maxlength="8" placeholder="Ingrese DNI" required>
                            </div>

                            <div class="col-md-8">
                                <label><strong>Estudiante</strong></label>
                                <input type="text" id="estudiante" class="form-control" readonly>
                            </div>
                        </div>

                        <input type="hidden" name="enrollment_id" id="enrollment_id">

                        {{-- DATOS ACADÉMICOS --}}
                        <div class="row mb-3">
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

                        {{-- CONCEPTOS DE PAGO --}}
                        <label><strong>Conceptos de Pago</strong></label>

                        {{-- ✅ 1. MENSUALIDAD (fila separada y grande) --}}
                        <div class="row mb-3">
                            @foreach ($conceptos as $concepto)
                                @if (strtoupper($concepto->nombre) === 'MENSUALIDAD')
                                    <div class="col-12">
                                        <div class="border rounded p-3 concepto-card shadow-sm">
                                            <label class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="conceptos[{{ $concepto->id }}][activo]"
                                                       value="1" data-id="{{ $concepto->id }}"
                                                       data-mensual="{{ $concepto->es_mensual ? 1 : 0 }}"
                                                       class="chkConcepto">
                                                <strong>{{ strtoupper($concepto->nombre) }}</strong>
                                            </label>

                                            <div class="campos-extra mt-3" style="display:none;">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label>Monto (S/.)</label>
                                                        <input type="number" step="0.01" min="0"
                                                               name="conceptos[{{ $concepto->id }}][monto]"
                                                               class="form-control monto-input" placeholder="Monto">
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label>Descuento (S/.)</label>
                                                        <input type="number" step="0.01" min="0"
                                                               name="conceptos[{{ $concepto->id }}][descuento]"
                                                               class="form-control descuento-input" value="0">
                                                    </div>
                                                </div>

                                                <div class="mt-3 meses-container" style="display:none;">
                                                    <label>Meses a pagar</label>
                                                    <div class="d-flex flex-wrap gap-1 mt-1 mesesBotones"></div>
                                                    <div class="inputsPeriodos"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <hr>

                        {{-- ✅ 2. OTROS CONCEPTOS (4 por fila) --}}
                        <div class="row">
                            @foreach ($conceptos as $concepto)
                                @if (strtoupper($concepto->nombre) !== 'MATRICULA' && strtoupper($concepto->nombre) !== 'MENSUALIDAD')
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="border rounded p-3 concepto-card h-100 shadow-sm">
                                            <label class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="conceptos[{{ $concepto->id }}][activo]"
                                                       value="1" data-id="{{ $concepto->id }}"
                                                       data-mensual="{{ $concepto->es_mensual ? 1 : 0 }}"
                                                       class="chkConcepto">
                                                <strong>{{ strtoupper($concepto->nombre) }}</strong>
                                            </label>

                                            <div class="campos-extra mt-3" style="display:none;">
                                                <div class="mb-2">
                                                    <label>Monto (S/.)</label>
                                                    <input type="number" step="0.01" min="0"
                                                           name="conceptos[{{ $concepto->id }}][monto]"
                                                           class="form-control monto-input" placeholder="Monto">
                                                </div>

                                                <div class="mb-2">
                                                    <label>Descuento (S/.)</label>
                                                    <input type="number" step="0.01" min="0"
                                                           name="conceptos[{{ $concepto->id }}][descuento]"
                                                           class="form-control descuento-input" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <hr>

                        {{-- MÉTODO DE PAGO --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label><strong>Método de pago</strong></label>
                                <select name="metodo_pago" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="yape">Yape</option>
                                    <option value="plin">Plin</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>

                        {{-- BOTONES --}}
                        <div class="text-center mt-4">
                            <button class="btn btn-success px-5">
                                <i class="fa-solid fa-floppy-disk"></i> Registrar Pagos
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
document.addEventListener('DOMContentLoaded', () => {
    const chkConceptos = document.querySelectorAll('.chkConcepto');
    const year = new Date().getFullYear();

    chkConceptos.forEach(chk => {
        chk.addEventListener('change', function () {
            const card = this.closest('.concepto-card');
            const camposExtra = card.querySelector('.campos-extra');
            const mesesContainer = card.querySelector('.meses-container');

            // Mostrar/ocultar los campos
            camposExtra.style.display = this.checked ? 'block' : 'none';

            // Si es mensual, mostrar meses
            if (this.dataset.mensual == "1" && mesesContainer) {
                mesesContainer.style.display = this.checked ? 'block' : 'none';
                if (this.checked) generarMeses(mesesContainer);
            }
        });
    });

    // Generar botones de meses
    function generarMeses(container) {
        const mesesBotones = container.querySelector('.mesesBotones');
        const inputsPeriodos = container.querySelector('.inputsPeriodos');
        mesesBotones.innerHTML = '';
        inputsPeriodos.innerHTML = '';

        for (let m = 3; m <= 12; m++) {
            const periodo = `${year}-${String(m).padStart(2, '0')}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = periodo;
            btn.className = 'btn btn-outline-primary m-1';

            btn.onclick = () => toggleMes(btn, periodo, inputsPeriodos);
            mesesBotones.appendChild(btn);
        }
    }

    function toggleMes(btn, periodo, inputsContainer) {
        const existing = inputsContainer.querySelector(`#periodo_${periodo}`);
        if (existing) {
            existing.remove();
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        } else {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'periodos[]';
            input.value = periodo;
            input.id = `periodo_${periodo}`;
            inputsContainer.appendChild(input);
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
        }
    }

    // Buscar estudiante
    document.getElementById('dni').addEventListener('blur', function () {
        const dni = this.value.trim();
        if (!dni) return;

        fetch(`/buscar-matricula/${dni}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                document.getElementById('enrollment_id').value = data.enrollment_id;
                document.getElementById('estudiante').value = data.estudiante;
                document.getElementById('nivel').value = data.nivel;
                document.getElementById('grado').value = data.grado;
                document.getElementById('seccion').value = data.seccion;
            });
    });
});
</script>
@endsection
