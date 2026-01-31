@extends('template.main')
@section('title', 'DETALLE DE MATRÍCULA')

@section('content')
<div class="content-wrapper">

    <!-- ENCABEZADO -->
    <div class="content-header">
        <div class="container-fluid text-center">
            <h1 class="m-0"><strong>@yield('title')</strong></h1>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- CABECERA: FOTO + DATOS -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3 text-center">
                            @if($enrollment->student->photo_path)
                                <img src="{{ route('mostrar.foto', basename($enrollment->student->photo_path)) }}"
                                    alt="Foto del estudiante"
                                    class="img-thumbnail"
                                    width="160">
                            @else
                                <img src="{{ asset('img/default-user.png') }}"
                                    alt="Sin foto"
                                    class="img-thumbnail"
                                    width="160">
                            @endif
                        </div>

                        <div class="col-md-9">
                            <h4 class="mb-2 text-primary">
                                <strong>{{ $enrollment->student->nombres }}
                                    {{ $enrollment->student->apellido_paterno }}
                                    {{ $enrollment->student->apellido_materno }}</strong>
                            </h4>
                            <p class="mb-1"><strong>DNI:</strong> {{ $enrollment->student->dni }}</p>
                            <p class="mb-1"><strong>Correo:</strong> {{ $enrollment->student->email ?? '—' }}</p>
                            <p class="mb-1"><strong>Celular:</strong> {{ $enrollment->student->celular }}</p>
                            <p class="mb-1"><strong>Dirección:</strong> {{ $enrollment->student->direccion }}</p>
                        </div>
                    </div>

                    <hr>

                    <!-- DATOS ACADÉMICOS -->
                    <h5 class="text-success mb-3"><strong>DATOS ACADÉMICOS</strong></h5>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <p><strong>Año Escolar:</strong> {{ $enrollment->schoolYear->nombre ?? '-' }}</p>
                            <p><strong>Nivel:</strong> {{ $enrollment->level->nombre ?? '-' }}</p>
                            <p><strong>Grado:</strong> {{ $enrollment->grade->nombre ?? '-' }}</p>
                            <p><strong>Sección:</strong> {{ $enrollment->section->nombre ?? '-' }}</p>
                        </div>

                        <div class="col-md-6 mb-2">
                            <p><strong>Fecha Matrícula:</strong> {{ $enrollment->fecha_matricula }}</p>
                            @php
                                $pagoMatricula = $enrollment->payments
                                    ->first(function ($p) {
                                        return $p->paymentConcept
                                            && strtoupper($p->paymentConcept->nombre) === 'MATRICULA';
                                    });
                            @endphp

                            <p>
                                <strong>Monto Matrícula:</strong>
                                S/.
                                {{ $pagoMatricula
                                    ? number_format($pagoMatricula->monto - $pagoMatricula->descuento, 2)
                                    : '0.00'
                                }}
                            </p>
                            <p>
                                <strong>Estado:</strong>
                                <span class="badge 
                                    @if($enrollment->estado == 'pagado') badge-success 
                                    @elseif($enrollment->estado == 'pendiente') badge-warning 
                                    @else badge-info @endif">
                                    {{ ucfirst($enrollment->estado) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- BOTONES -->
                    <div class="text-center mt-4">
                        @if($enrollment->voucher_matricula && Storage::disk('public')->exists($enrollment->voucher_matricula))
                            <a href="{{ route('enrollments.voucher', $enrollment->id) }}" 
                               target="_blank"
                               class="btn btn-info px-4 mb-2">
                                <i class="fa-solid fa-file-pdf"></i> Ver Voucher PDF
                            </a>
                        @endif

                        <a href="{{ route('enrollments.edit', $enrollment->id) }}" 
                           class="btn btn-warning px-4 mb-2">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </a>

                        <a href="{{ route('enrollments.index') }}" 
                           class="btn btn-secondary px-4 mb-2">
                            <i class="fa-solid fa-arrow-left"></i> Volver
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
