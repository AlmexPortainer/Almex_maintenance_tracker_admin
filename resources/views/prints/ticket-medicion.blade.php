<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Equipo de Medición</title>

    {{-- =====================================================
       ESTILOS DEL TICKET (AISLADOS – 80MM)
       ===================================================== --}}
    <style>
        /* ===========================
           BASE DEL TICKET
           =========================== */
        .ticket-80mm {
            width: 72mm;
            max-width: 72mm;
            border: 1px solid #000;
            padding: 3mm;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===========================
           FILAS GENERALES (2 COLUMNAS)
           =========================== */
        .ticket-80mm .row {
            display: flex;
            align-items: center;
            margin-bottom: 1.5mm;
        }

        .ticket-80mm .label {
            width: 18mm;
            font-weight: bold;
            font-size: 8px;
        }

        .ticket-80mm .value {
            flex: 1;
            min-width: 0;
            border-bottom: 1px solid #000;
            padding-left: 1mm;
            font-size: 8px;
        }

        /* ==================================================
           FILA CRÍTICA: EQUIPO + STATUS (GRID)
           - SIEMPRE EN UNA SOLA LÍNEA
           - ESTABLE EN IMPRESIÓN
           ================================================== */
        .ticket-80mm .row-equipo {
            display: grid;
            grid-template-columns: 15mm 1fr 15mm;
            align-items: center;
            margin-bottom: 1.5mm;
        }

        .ticket-80mm .row-equipo .label {
            font-weight: bold;
            font-size: 8px;
            white-space: nowrap;
        }

        .ticket-80mm .row-equipo .value {
            font-size: 8px;
            border-bottom: 1px solid #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ticket-80mm .ticket-status {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #000;
            white-space: nowrap;
        }

        .ticket-80mm .ticket-status--apto {
            background-color: darkgray;
        }

        .ticket-80mm .ticket-status--no-apto {
            background-color: darkgray;
        }

        /* ===========================
           TABLA CAL / VAL / MANT
           (TABLA HTML REAL)
           =========================== */
        .ticket-80mm .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm;
            font-size: 7px;
            table-layout: fixed;
        }

        .ticket-80mm .ticket-table th,
        .ticket-80mm .ticket-table td {
            border: 1px solid black;
            padding: 1mm;
            vertical-align: middle;
        }

        .ticket-80mm .ticket-table th {
            background-color: lightgray;
            text-align: center;
            font-weight: bold;
        }

        .ticket-80mm .ticket-table .row-title {
            font-weight: bold;
            white-space: nowrap;
            width: 16mm;
        }

        .ticket-80mm .ticket-table .row-requiere {
            background-color: #eaeaea;
            font-weight: bold;
        }

        /* ===========================
           FUERA DE SERVICIO (2 SECCIONES)
           =========================== */
        .ticket-80mm .fuera-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm;
            padding: 3mm;
            font-weight: bold;
            box-sizing: border-box;
        }

        /* Izquierda */
        .ticket-80mm .fuera-left {
            text-align: center;
            background-color: #000;
            color: #fff;
            border: 2px solid #000;

            font-size: 12px;
            line-height: 1.2;
        }

        /* Derecha */
        .ticket-80mm .fuera-right {
            font-size: 7px;
            line-height: 1.2;
            text-align: left;
        }

        /* ===========================
           FOOTER
           =========================== */
        .ticket-80mm .ticket-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1mm;
            padding-top: 1mm;
        }

        .ticket-80mm .ticket-footer-left {
            flex: 0 0 auto;
        }

        .ticket-80mm .ticket-logo {
            align-self: center;
            max-width: 20mm;
            max-height: 20mm;
            width: 13mm;
        }

        .ticket-80mm .ticket-footer-right {
            text-align: center;
            font-size: 14px;
            color: blue;
            font-weight: bold;
            text-transform: uppercase;
            max-width: 40mm;
        }

        /* ===========================
           FUERA DE SERVICIO (ALTO CONTRASTE)
           =========================== */
        .ticket-80mm .fuera {
            padding: 3mm;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-top: 3mm;
            letter-spacing: 1px;
        }


        /* ===========================
           IMPRESIÓN 80MM
           =========================== */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                width: 80mm;
                margin: 0;
                padding: 0;
                /* Forzar impresión de fondos (negro FUERA DE SERVICIO, sombreados) */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* El botón no se imprime ni deja hueco */
            button {
                display: none !important;
            }

            body * {
                visibility: hidden;
            }

            .ticket-print,
            .ticket-print * {
                visibility: visible;
            }

            .ticket-print {
                position: static !important;
                width: 72mm;
            }

            .ticket-80mm,
            .ticket-80mm * {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<button type="button" onclick="window.print()">🖨 Imprimir Ticket</button>

{{-- =====================================================
   TICKET NORMAL (solo si el equipo está APTO)
   ===================================================== --}}
@if($apto)
<div class="ticket-80mm ticket-print">

    {{-- FILA CRÍTICA --}}
    <div class="row-equipo">
        <div class="label">Equipo</div>
        <div class="value">{{ $equipo }}</div>
        <div class="ticket-status ticket-status--{{ $apto ? 'apto' : 'no-apto' }}">
            {{ $apto ? 'APTO' : 'NO APTO' }}
        </div>
    </div>

    <div class="row">
        <div class="label">Marca</div>
        <div class="value">{{ $marca }}</div>
    </div>

    <div class="row">
        <div class="label">Modelo</div>
        <div class="value">{{ $modelo }}</div>
    </div>

    <div class="row">
        <div class="label">Código</div>
        <div class="value">{{ $codigo }}</div>
    </div>

    {{-- TABLA --}}
    <table class="ticket-table">
        <thead>
        <tr>
            <th></th>
            <th>CALIBRACIÓN</th>
            <th>VERIFICACIÓN</th>
            <th>MANTENIMIENTO</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="row-title">Última</td>
            <td>{{ $cal_ultima ?? '—' }}</td>
            <td>{{ $val_ultima ?? '—' }}</td>
            <td>{{ $mnt_ultima ?? '—' }}</td>
        </tr>
        <tr>
            <td class="row-title">Próxima</td>
            <td>{{ $cal_proxima ?? '—' }}</td>
            <td>{{ $val_proxima ?? '—' }}</td>
            <td>{{ $mnt_proxima ?? '—' }}</td>
        </tr>
        <tr>
            <td class="row-title">Quién aplicó</td>
            <td>{{ $cal_usuario ?? '—' }}</td>
            <td>{{ $val_usuario ?? '—' }}</td>
            <td>{{ $mnt_usuario ?? '—' }}</td>
        </tr>
        <tr class="row-requiere">
            <td class="row-title">REQUIERE</td>
            <td>{{ $cal_requiere ? 'SI' : 'NO' }}</td>
            <td>{{ $val_requiere ? 'SI' : 'NO' }}</td>
            <td>{{ $mnt_requiere ? 'SI' : 'NO' }}</td>
        </tr>
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="ticket-footer">
        <div class="ticket-footer-left">
            <img src="{{ asset('assets/logos/Logo_ALMEX_SVG.svg') }}" alt="ALMEX" class="ticket-logo">
        </div>
        <div class="ticket-footer-right">
            ESTADO DEL EQUIPO DE MEDICIÓN
        </div>
    </div>

</div>

@else
{{-- =====================================================
   TICKET FUERA DE SERVICIO (solo si el equipo NO está APTO)
   ===================================================== --}}
<div class="ticket-80mm ticket-print">
    <div class="row-equipo">
        <div class="label">Equipo</div>
        <div class="value">{{ $equipo }}</div>
        <div class="ticket-status ticket-status--{{ $apto ? 'apto' : 'no-apto' }}">
            {{ $apto ? 'APTO' : 'NO APTO' }}
        </div>
    </div>

    <div class="row">
        <div class="label">Marca</div>
        <div class="value">{{ $marca }}</div>
    </div>

    <div class="row">
        <div class="label">Modelo</div>
        <div class="value">{{ $modelo }}</div>
    </div>

    <div class="row">
        <div class="label">Código</div>
        <div class="value">{{ $codigo }}</div>
    </div>

    <div class="fuera fuera-grid">
        <div class="fuera-left">
            FUERA DE SERVICIO<br>
            — NO USAR —
        </div>
        <div class="fuera-right">
            NOTA:<br>
            Ver al reverso de esta etiqueta<br>
            la causa o motivo →
        </div>
    </div>

    <div class="ticket-footer">
        <div class="ticket-footer-left">
            <img src="{{ asset('assets/logos/Logo_ALMEX_SVG.svg') }}" alt="ALMEX" class="ticket-logo">
        </div>
        <div class="ticket-footer-right">
            ESTADO DEL EQUIPO DE MEDICIÓN
        </div>
    </div>

</div>
@endif
</body>
</html>
