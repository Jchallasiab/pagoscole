<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ================= HEADER ================= */
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
        }

        .logo {
            width: 80px;
        }

        .title h2 {
            margin: 0;
            font-size: 16px;
        }

        .title h3 {
            margin: 0;
            font-size: 15px;
        }

        .title p {
            margin: 4px 0 0 0;
            font-size: 12px;
        }

        /* ================= BOX ================= */
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 4px;
        }

        /* ================= CONCEPTOS ================= */
        .conceptos th,
        .conceptos td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .conceptos thead {
            background-color: #f2f2f2;
        }

        /* ================= TOTALES ================= */
        .totals {
            width: 100%;
            margin-top: 10px;
        }

        .totals td {
            padding: 5px;
        }

        .totals .label {
            text-align: right;
            width: 70%;
        }

        .totals .value {
            text-align: right;
            width: 30%;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>

<body>

@php
    $total     = $payment->monto;          // monto final ya pagado
    $descuento = $payment->descuento ?? 0;
    $subtotal  = $total + $descuento;      // solo para mostrar
@endphp

{{-- ================= HEADER ================= --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 90px;">
                <img src="{{ public_path('img/logotesla.jpg') }}" class="logo">
            </td>
            <td class="title">
                <h2>INSTITUCIÓN EDUCATIVA PRIVADA</h2>
                <h3>TESLA BLACK HORSE</h3>
                <p>
                    <strong>Comprobante de Pago</strong><br>
                    {{ $payment->paymentConcept->nombre ?? 'Concepto no definido' }}

                    @if($payment->periodo)
                        @php
                            try {
                                $periodo = \Carbon\Carbon::createFromFormat('Y-m', $payment->periodo)
                                            ->translatedFormat('F Y');
                            } catch (\Exception $e) {
                                $periodo = $payment->periodo;
                            }
                        @endphp
                        — <small>{{ $periodo }}</small>
                    @endif
                </p>
            </td>
        </tr>
    </table>
</div>

{{-- ================= DATOS DEL ESTUDIANTE ================= --}}
<div class="box">
    <table class="info">
        <tr>
            <td><strong>Estudiante:</strong></td>
            <td>
                {{ $payment->enrollment->student->nombres }}
                {{ $payment->enrollment->student->apellido_paterno }}
                {{ $payment->enrollment->student->apellido_materno }}
            </td>
        </tr>
        <tr>
            <td><strong>DNI:</strong></td>
            <td>{{ $payment->enrollment->student->dni }}</td>
        </tr>
        <tr>
            <td><strong>Nivel / Grado / Sección:</strong></td>
            <td>
                {{ $payment->enrollment->level->nombre ?? '-' }} /
                {{ $payment->enrollment->grade->nombre ?? '-' }} /
                {{ $payment->enrollment->section->nombre ?? '-' }}
            </td>
        </tr>
        <tr>
            <td><strong>Método de Pago:</strong></td>
            <td>{{ ucfirst($payment->metodo_pago) }}</td>
        </tr>
        <tr>
            <td><strong>Fecha de Pago:</strong></td>
            <td>{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Comprobante N°:</strong></td>
            <td>{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>
</div>

{{-- ================= CONCEPTO ================= --}}
<table class="conceptos">
    <thead>
        <tr>
            <th>#</th>
            <th>Concepto</th>
            <th>Periodo</th>
            <th>Monto (S/.)</th>
            <th>Descuento (S/.)</th>
            <th>Total (S/.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>{{ $payment->paymentConcept->nombre ?? '—' }}</td>
            <td>{{ $payment->periodo ?? '—' }}</td>
            <td>{{ number_format($subtotal, 2) }}</td>
            <td>{{ number_format($descuento, 2) }}</td>
            <td><strong>{{ number_format($total, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

{{-- ================= TOTALES ================= --}}
<table class="totals">
    <tr>
        <td class="label"><strong>Subtotal:</strong></td>
        <td class="value">S/. {{ number_format($subtotal, 2) }}</td>
    </tr>
    <tr>
        <td class="label"><strong>Descuento:</strong></td>
        <td class="value">S/. {{ number_format($descuento, 2) }}</td>
    </tr>
    <tr>
        <td class="label"><strong>Total Pagado:</strong></td>
        <td class="value"><strong>S/. {{ number_format($total, 2) }}</strong></td>
    </tr>
</table>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    <p>------------------------------------------</p>
    <p><strong>Firma y sello de Secretaría</strong></p>
    <br>
    <p>Gracias por su puntualidad en los pagos.</p>
    <p>Este documento es un comprobante válido emitido por el sistema escolar.</p>
</div>

</body>
</html>
