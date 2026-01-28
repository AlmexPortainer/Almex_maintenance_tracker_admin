<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Equipo de Medición</title>

    <style>
        /* ===========================
           TICKET 80MM (AISLADO)
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
        }

        /* ===========================
           FILAS GENERALES
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
            min-width: 0;                 /* 🔥 clave para evitar saltos */
            border-bottom: 1px solid #000;
            padding-left: 1mm;
            font-size: 8px;
        }

        /* ===========================================
           FILA EQUIPO (GRID – UNA SOLA LÍNEA SIEMPRE)
           =========================================== */
        .ticket-80mm .row-equipo {
            display: grid;
            grid-template-columns: 16mm 1fr 16mm;
            align-items: center;
            column-gap: 1mm;
            margin-bottom: 1.5mm;
        }

        /* label */
        .ticket-80mm .row-equipo .label {
            font-weight: bold;
            font-size: 8px;
            white-space: nowrap;
        }

        /* value (texto largo controlado) */
        .ticket-80mm .row-equipo .value {
            font-size: 8px;
            border-bottom: 1px solid #000;
            padding-left: 1mm;

            white-space: nowrap;        /* 🔒 NO SALTOS */
            overflow: hidden;           /* 🔒 NO DESBORDE */
            text-overflow: ellipsis;    /* … si es largo */
        }

        /* status */
        .ticket-80mm .row-equipo .ticket-status {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #000;
            background-color: darkgray;
            white-space: nowrap;
        }

        /* variantes */
        .ticket-80mm .ticket-status--apto {
            background-color: #f5f5f5;
        }

        .ticket-80mm .ticket-status--no-apto {
            background-color: #e0e0e0;
        }

        /* ===========================
           TABLA CAL / VAL / MANT (REAL)
           =========================== */

        .ticket-80mm .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm;
            font-size: 7px;
            table-layout: fixed;           /* 🔒 estabilidad */
        }

        /* encabezados */
        .ticket-80mm .ticket-table th {
            border: 1px solid #000;
            padding: 1mm;
            text-align: center;
            font-weight: bold;
            font-size: 7px;
            background-color: #f5f5f5;
        }

        /* celdas */
        .ticket-80mm .ticket-table td {
            border: 1px solid #000;
            padding: 1mm;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* primera columna (títulos de fila) */
        .ticket-80mm .ticket-table .row-title {
            font-weight: bold;
            white-space: nowrap;
            width: 16mm;
        }

        /* fila especial: REQUIERE */
        .ticket-80mm .ticket-table .row-requiere {
            background-color: #eaeaea;
            font-weight: bold;
        }

        /* ===========================
           FUERA DE SERVICIO
           =========================== */

        .ticket-80mm .fuera {
            border: 1px solid #000;
            padding: 3mm;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-top: 3mm;
        }

        /* ===========================
           FOOTER
           =========================== */

        .ticket-80mm .ticket-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #000;
            margin-top: 1mm;
            padding-top: 1mm;
        }

        .ticket-80mm .ticket-footer-left {
            flex: 0 0 auto;
        }

        .ticket-80mm .ticket-logo {
            max-width: 20mm;
            height: auto;
        }

        .ticket-80mm .ticket-footer-right {
            text-align: right;
            font-size: 7px;
            font-weight: bold;
            line-height: 1.2;
            max-width: 40mm;
            text-transform: uppercase;
        }

        /* ===========================
           FIX IMPRESIÓN 80MM
           =========================== */

        @media print {

            @page {
                size: 80mm;
                margin: 0;
            }

            html, body {
                width: 80mm;
                margin: 0;
                padding: 0;
                zoom: 1;
            }

            body * {
                visibility: hidden;
            }

            #ticket-print,
            #ticket-print * {
                visibility: visible;
            }

            #ticket-print {
                position: static !important;
                width: 72mm;
            }

            .ticket-80mm {
                width: 72mm !important;
                max-width: 72mm !important;
                box-sizing: border-box;
            }

            /* footer más estable en print */
            .ticket-80mm .ticket-footer {
                display: grid;
                grid-template-columns: auto 1fr;
                column-gap: 2mm;
            }

            .ticket-80mm,
            .ticket-80mm * {
                page-break-inside: avoid;
            }
        }
    </style>

</head>
<body>

<button type="button" onclick="window.print()">
    🖨 Imprimir Ticket
</button>

{{-- ===== TICKET NORMAL ===== --}}
<div class="ticket-80mm" id="ticket-print">

    <div class="row-equipo">
        <div class="label">Equipo</div>

        <div class="value">
            {{ $equipo }}
        </div>

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
            <td>28/11/1991</td>
            <td>28/11/1991</td>
            <td>Getsemani Avila Quezada</td>
        </tr>

        <tr>
            <td class="row-title">Próxima</td>
            <td>28/11/1991</td>
            <td>28/11/1991</td>
            <td>Getsemani Avila Quezada</td>
        </tr>

        <tr>
            <td class="row-title">Quién aplicó</td>
            <td>Getsemani Avila</td>
            <td>Getsemani Avila</td>
            <td>Getsemani Avila</td>
        </tr>

        <tr class="row-requiere">
            <td class="row-title">REQUIERE</td>
            <td>SI / NO</td>
            <td>SI / NO</td>
            <td>SI / NO</td>
        </tr>
        </tbody>
    </table>

    <div class="ticket-footer">
        <div class="ticket-footer-left">
            <img
                src="{{ asset('assets/logos/Logo_ALMEX_SVG.svg') }}"
                alt="ALMEX"
                class="ticket-logo"
            />
        </div>
        <div class="ticket-footer-right">
            ESTADO DEL EQUIPO DE MEDICIÓN
        </div>
    </div>


</div>


{{-- ===== TICKET FUERA DE SERVICIO ===== --}}
<div class="ticket-80mm" id="ticket-print">

    <div class="row">
        <div class="label">Equipo</div>
        <div class="value">{{ $equipo }}</div>
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

    <div class="fuera">
        FUERA DE SERVICIO<br>
        -- NO USAR --
    </div>

    <div class="ticket-footer">
        <div class="ticket-footer-left">
            <img
                src="{{ asset('assets/logos/Logo_ALMEX_SVG.svg') }}"
                alt="ALMEX"
                class="ticket-logo"
            />
        </div>
        <div class="ticket-footer-right">
            ESTADO DEL EQUIPO DE MEDICIÓN
        </div>
    </div>
</div>

</body>
</html>

