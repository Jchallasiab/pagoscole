@extends('template.main')
@section('title', 'EDITAR MATRÍCULA')

@section('content')
<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- FILA 1 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">ESTUDIANTE</label>
                                <select name="student_id" id="student_id" class="form-control" required></select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">AÑO ESCOLAR</label>
                                <select id="school_year_id" name="school_year_id" class="form-control" required>
                                    <option value="">Seleccione año</option>
                                    @foreach($schoolYears as $sy)
                                        <option value="{{ $sy->id }}" {{ $enrollment->school_year_id == $sy->id ? 'selected' : '' }}>
                                            {{ $sy->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- FILA 2 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">NIVEL</label>
                                <select id="level_id" name="level_id" class="form-control" required>
                                    <option value="">Seleccione nivel</option>
                                    @foreach($levels as $l)
                                        <option value="{{ $l->id }}" {{ $enrollment->level_id == $l->id ? 'selected' : '' }}>
                                            {{ $l->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">GRADO</label>
                                <select id="grade_id" name="grade_id" class="form-control" required>
                                    <option value="">Seleccione grado</option>
                                    @foreach($grades as $g)
                                        <option value="{{ $g->id }}"
                                                data-level="{{ $g->level_id }}"
                                                {{ $enrollment->grade_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- FILA 3 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">SECCIÓN</label>
                                <select id="section_id" name="section_id" class="form-control" required>
                                    <option value="">Seleccione año, nivel y grado</option>
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}" {{ $enrollment->section_id == $s->id ? 'selected' : '' }}>
                                            {{ $s->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">FECHA MATRÍCULA</label>
                                <input type="date"
                                       name="fecha_matricula"
                                       class="form-control"
                                       value="{{ $enrollment->fecha_matricula }}"
                                       required>
                            </div>
                        </div>

                        <hr>

                        <!-- CONCEPTOS DE PAGO -->
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fa-solid fa-coins"></i> CONCEPTOS DE PAGO
                        </h5>

                        <div class="row">
                            @foreach($paymentConcepts->where('es_mensual', false) as $concept)
                            @php
                                $payment = $enrollment->payments->firstWhere('payment_concept_id', $concept->id);
                            @endphp
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-3 h-100">

                                    <div class="form-check">
                                        <input type="checkbox"
                                            class="form-check-input concept-checkbox"
                                            name="concepts[{{ $concept->id }}][selected]"
                                            value="1"
                                            {{ $payment ? 'checked' : '' }}>
                                        <label class="fw-bold">{{ $concept->nombre }}</label>
                                    </div>

                                    <div class="concepto-detalle mt-2 {{ $payment ? '' : 'd-none' }}">
                                        <div class="input-group">
                                            <span class="input-group-text">S/</span>
                                            <input type="number" step="0.01" class="form-control"
                                                name="concepts[{{ $concept->id }}][monto]"
                                                value="{{ $payment->monto ?? '' }}">
                                            <input type="number" step="0.01" class="form-control"
                                                name="concepts[{{ $concept->id }}][descuento]"
                                                value="{{ $payment->descuento ?? '' }}">
                                            <select class="form-select"
                                                name="concepts[{{ $concept->id }}][metodo_pago]">
                                                <option value="">Método</option>
                                                <option value="efectivo" {{ ($payment->metodo_pago ?? '') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                                <option value="yape" {{ ($payment->metodo_pago ?? '') == 'yape' ? 'selected' : '' }}>Yape</option>
                                                <option value="plin" {{ ($payment->metodo_pago ?? '') == 'plin' ? 'selected' : '' }}>Plin</option>
                                                <option value="transferencia" {{ ($payment->metodo_pago ?? '') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            @endforeach
                        </div>

                        @php
                            $mensualidad = $paymentConcepts->firstWhere('es_mensual', true);
                            $mensualPayments = $mensualidad ? $enrollment->payments->where('payment_concept_id', $mensualidad->id)->pluck('periodo')->toArray() : [];
                            $mensualMonto = $mensualidad ? $enrollment->payments->where('payment_concept_id', $mensualidad->id)->first()->monto ?? '' : '';
                            $mensualDesc = $mensualidad ? $enrollment->payments->where('payment_concept_id', $mensualidad->id)->first()->descuento ?? '' : '';
                            $mensualMetodo = $mensualidad ? $enrollment->payments->where('payment_concept_id', $mensualidad->id)->first()->metodo_pago ?? '' : '';
                        @endphp

                        @if($mensualidad)
                        <hr>
                        <h5 class="fw-bold text-success">{{ strtoupper($mensualidad->nombre) }}</h5>

                        <div class="border rounded p-3 mt-2">

                            <div class="form-check mb-2">
                                <input type="checkbox"
                                    class="form-check-input concept-checkbox"
                                    name="concepts[{{ $mensualidad->id }}][selected]"
                                    value="1"
                                    {{ count($mensualPayments) ? 'checked' : '' }}>
                                <label class="fw-bold">Incluir mensualidad</label>
                            </div>

                            <div class="concepto-detalle mt-2 {{ count($mensualPayments) ? '' : 'd-none' }}">
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control"
                                        name="concepts[{{ $mensualidad->id }}][monto]"
                                        value="{{ $mensualMonto }}">
                                    <input type="number" step="0.01" class="form-control"
                                        name="concepts[{{ $mensualidad->id }}][descuento]"
                                        value="{{ $mensualDesc }}">
                                    <select class="form-select"
                                        name="concepts[{{ $mensualidad->id }}][metodo_pago]">
                                        <option value="">Método</option>
                                        <option value="efectivo" {{ $mensualMetodo == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="yape" {{ $mensualMetodo == 'yape' ? 'selected' : '' }}>Yape</option>
                                        <option value="plin" {{ $mensualMetodo == 'plin' ? 'selected' : '' }}>Plin</option>
                                        <option value="transferencia" {{ $mensualMetodo == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="meses-container mt-3 {{ count($mensualPayments) ? '' : 'd-none' }}">
                                @foreach([
                                  '03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                                  '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre',
                                  '10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
                                ] as $num => $mes)
                                <label class="btn btn-outline-success btn-sm mes-btn {{ in_array(date('Y').'-'.$num, $mensualPayments) ? 'active' : '' }}">
                                    <input type="checkbox" class="d-none"
                                        name="concepts[{{ $mensualidad->id }}][periodos][]"
                                        value="{{ date('Y') }}-{{ $num }}"
                                        {{ in_array(date('Y').'-'.$num, $mensualPayments) ? 'checked' : '' }}>
                                    {{ $mes }}
                                </label>
                                @endforeach
                            </div>

                        </div>
                        @endif

                        <div class="text-center mt-4">
                            <button class="btn btn-success px-4">
                                <i class="fa-solid fa-save"></i> ACTUALIZAR
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const CURRENT_SECTION_ID = {{ $enrollment->section_id }};
</script>
<script>
    const CURRENT_STUDENT = {
        id: {{ $enrollment->student->id }},
        text: "{{ $enrollment->student->nombres }}  {{ $enrollment->student->apellido_paterno }} {{ $enrollment->student->apellido_materno}}"
    };
</script>


<script>
    const studentSelect = $('#student_id').select2({
        placeholder: 'Buscar estudiante...',
        ajax: {
            url: '{{ route("students.search") }}',
            dataType: 'json',
            delay: 100,
            data: params => ({ term: params.term }),
            processResults: data => ({ results: data })
        }
    });

    // 🔹 Precargar estudiante actual (EDIT)
    if (CURRENT_STUDENT?.id) {
        const option = new Option(CURRENT_STUDENT.text, CURRENT_STUDENT.id, true, true);
        studentSelect.append(option).trigger('change');
    }


const year = document.getElementById('school_year_id');
const level = document.getElementById('level_id');
const grade = document.getElementById('grade_id');
const section = document.getElementById('section_id');

// 🔹 Filtrar grados según nivel
function filtrarGrados() {
    const levelId = level.value;
    $('#grade_id option').each(function() {
        const optionLevel = $(this).data('level');
        if (!optionLevel || optionLevel == levelId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// 🔹 Cargar secciones según año, nivel y grado
function cargarSecciones() {
    if (!year.value || !level.value || !grade.value) return;

    fetch(`{{ route('enrollments.sections') }}?school_year_id=${year.value}&level_id=${level.value}&grade_id=${grade.value}`)
        .then(r => r.json())
        .then(data => {
            section.innerHTML = '<option value="">Seleccione sección</option>';

            data.forEach(s => {
                const selected = s.id == CURRENT_SECTION_ID ? 'selected' : '';
                section.innerHTML += `<option value="${s.id}" ${selected}>${s.nombre}</option>`;
            });
        });
}
level.addEventListener('change', function() {
    filtrarGrados();
    cargarSecciones();
});
grade.addEventListener('change', cargarSecciones);
year.addEventListener('change', cargarSecciones);

// 🔹 Conceptos y mensualidades
$(document).on('change', '.concept-checkbox', function () {
    const box = $(this).closest('.border');
    box.find('.concepto-detalle').toggleClass('d-none', !this.checked);
    box.find('.meses-container').toggleClass('d-none', !this.checked);
});

$(document).on('click', '.mes-btn', function (e) {
    e.preventDefault();
    $(this).toggleClass('active');
    const chk = $(this).find('input');
    chk.prop('checked', !chk.prop('checked'));
});

// 🔹 Inicializar filtrado de grados al cargar
filtrarGrados();

// esperar a que el DOM esté listo visualmente
setTimeout(() => {
    cargarSecciones();
}, 5);
</script>
@endsection
