<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
            position: relative;
        }
        .header-table {
            width: 100%;
        }
        .logo { width: 80px; }
        .title h2, .title h3 {
            margin: 0;
            text-transform: uppercase;
        }
        .title h2 { font-size: 16px; }
        .title h3 { font-size: 15px; }
        .title p { margin: 4px 0 0 0; font-size: 12px; }

        /* ✅ Recuadro igual, número centrado sin flex */
        .boleta-numero {
            position: absolute;
            top: 10px;
            right: 20px;
            border: 1.5px solid #000;
            width: 90px;
            height: 50px;
            text-align: center;           /* Centrado horizontal */
            font-weight: bold;
            font-size: 15px;
            color: #000;
            background-color: transparent;
            line-height: 40px;            /* 🔹 Centrado vertical (igual a height) */
            margin: 0;
            padding: 0;
        }

        .boleta-label {
            position: absolute;
            top: 65px;
            right: 28px;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            text-align: center;
        }

        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 12px;
        }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 4px; }
        .conceptos th, .conceptos td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .conceptos thead { background-color: #f2f2f2; }
        .totals { width: 100%; margin-top: 10px; }
        .totals td { padding: 5px; }
        .totals .label { text-align: right; width: 70%; }
        .totals .value { text-align: right; width: 30%; }
        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>

@php
    $subtotal  = $payments->sum(fn($p) => $p->monto);
    $descuento = $payments->sum('descuento');
    $total     = $subtotal - $descuento;
    $payment   = $payments->first();
    $ultimoPago = $payments->last();
    $numeroBoleta = $ultimoPago ? str_pad($ultimoPago->id, 6, '0', STR_PAD_LEFT) : '000000';
    $tieneMensualidades = $payments->contains(fn($p) => $p->paymentConcept->es_mensual ?? false);
    $hayDescuento = $payments->contains(fn($p) => $p->descuento > 0);
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
                    {{ $tieneMensualidades ? 'Mensualidades y otros conceptos cancelados' : 'Pago de conceptos escolares' }}
                </p>
            </td>
        </tr>
    </table>

    {{-- 🔹 Recuadro fijo, número centrado vertical y horizontal --}}
    @if($ultimoPago)
        <div class="boleta-numero">{{ $numeroBoleta }}</div>
        <div class="boleta-label">N° BOLETA</div>
    @endif
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
                {{ $payment->enrollment->level->nombre }} /
                {{ $payment->enrollment->grade->nombre }} /
                {{ $payment->enrollment->section->nombre }}
            </td>
        </tr>
        <tr>
            <td><strong>Método de Pago:</strong></td>
            <td>{{ ucfirst($payment->metodo_pago) }}</td>
        </tr>
        <tr>
            <td><strong>Fecha de Pago:</strong></td>
            <td>{{ $payment->fecha_pago ? \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</div>

{{-- ================= CONCEPTOS ================= --}}
<table class="conceptos">
    <thead>
        <tr>
            <th>#</th>
            <th>Concepto</th>
            <th>Periodo</th>
            <th>Precio Original (S/.)</th>
            @if ($hayDescuento)
                <th>Descuento (S/.)</th>
            @endif
            <th>Total Pagado (S/.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $i => $p)
            @php
                $periodo = $p->periodo ? ucfirst($p->periodo) : 'Único';
                $precioOriginal = $p->monto;
                $totalPagado = $p->monto - $p->descuento;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ strtoupper($p->paymentConcept->nombre) }}</td>
                <td>{{ $periodo }}</td>
                <td>{{ number_format($precioOriginal, 2) }}</td>
                @if ($hayDescuento)
                    <td>{{ number_format($p->descuento, 2) }}</td>
                @endif
                <td><strong>{{ number_format($totalPagado, 2) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TOTALES ================= --}}
<table class="totals">
    <tr>
        <td class="label"><strong>Subtotal:</strong></td>
        <td class="value">S/. {{ number_format($subtotal, 2) }}</td>
    </tr>
    @if ($hayDescuento)
        <tr>
            <td class="label"><strong>Descuento:</strong></td>
            <td class="value">S/. {{ number_format($descuento, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td class="label"><strong>Total Pagado:</strong></td>
        <td class="value"><strong>S/. {{ number_format($total, 2) }}</strong></td>
    </tr>
</table>

<div class="footer">
    <p>------------------------------------------</p>
    <p><strong>Firma y sello de Secretaría</strong></p>
    <p>Documento generado automáticamente por el sistema escolar</p>
</div>

</body>
</html>