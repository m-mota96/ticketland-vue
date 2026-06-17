<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        hr {
            margin: unset !important;
            opacity: unset !important;
        }
        .bold {
            font-weight: bold;
        }
        .normal {
            font-weight: normal !important;
        }
        .w-5 {
            width: 5% !important;
        }
        .w-10 {
            width: 10% !important;
        }
        .w-15 {
            width: 15% !important;
        }
        .w-20 {
            width: 20% !important;
        }
        .w-25 {
            width: 25% !important;
        }
        .w-30 {
            width: 30% !important;
        }
        .w-40 {
            width: 40% !important;
        }
        .w-50 {
            width: 50% !important;
        }
        .w-60 {
            width: 60% !important;
        }
        .w-70 {
            width: 70% !important;
        }
        .w-75 {
            width: 75% !important;
        }
        .w-80 {
            width: 80% !important;
        }
        .w-90 {
            width: 90% !important;
        }
        .w-99 {
            width: 99% !important;
        }
        .w-100 {
            width: 100% !important;
        }
        .pr {
            position: relative;
        }
        .pa {
            position: absolute;
        }
        .text-blue {
            color: #17253a;
        }
        .border {
            border: 1px solid #17253a;
        }
        .bbl {
            border-bottom-left-radius: 10px;
        }
        .bbr {
            border-bottom-right-radius: 10px;
        }
        .btl {
            border-top-left-radius: 10px;
        }
        .btr {
            border-top-right-radius: 10px;
        }
        .br {
            border-radius: 15px 15px 15px 15px;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;     /* Esto es clave para que las celdas no se salgan */
            border: 2px solid #17253a;
        }
        /* th, td {
            vertical-align: top; 
        } */
    </style>
</head>
<body>
    <table class="w-100 br">
        <tbody>
            <tr>
                <td colspan="2">
                    <img class="w-100" src="{{ $eventProfile }}" alt="Ticketland">
                </td>
            </tr>
            <tr>
                <td class="bold text-left w-60 pt-4 pb-4 ps-3 pe-3" style="vertical-align: top;">
                    <p class="text-blue" style="font-size: 20px;">{{ strtoupper($eventName) }}</p>
                    <hr class="w-100" style="border: 2px solid !important;">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">NOMBRE</p>
                    <p class="normal" style="font-size: 12px;">{{ $customer_name ?? '' }}</p>
                    <hr class="w-100">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">TIPO DE BOLETO</p>
                    <p class="normal" style="font-size: 12px;">{{ $name }}</p>
                    <hr class="w-100">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">FECHAS DEL EVENTO</p>
                    <p class="normal" style="font-size: 12px;">{{ $dates }}</p>
                    <hr class="w-100">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">LUGAR DEL EVENTO</p>
                    <p class="normal" style="font-size: 12px;">{{ $eventAddress }}</p>
                </td>
                <td class="bold text-center w-40 pt-4 pb-4 ps-3 pe-3" style="border-left: 2px dashed rgb(190, 190, 190); vertical-align: top;">
                    <p class="text-blue mb-1" style="font-size: 12px;">FECHA Y HORA DE COMPRA</p>
                    <p class="normal" style="font-size: 12px;">{{ $currentDate }}</p>
                    <hr class="w-100">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">PRECIO DEL BOLETO</p>
                    <p class="normal" style="font-size: 12px;">${{ number_format($price, 2) }} MXN</p>
                    <hr class="w-100">
                    <p class="text-blue mt-3 mb-1" style="font-size: 12px;">DESCUENTO</p>
                    <p class="normal" style="font-size: 12px;">{{ $promotion ? $promotion.'%' : 'N/A' }}</p>
                    <hr class="w-100">
                    <img class="mt-4 w-70" src="data:image/png;base64,{{$qr_code}}" alt="Ticketland">
                </td>
            </tr>
            {{-- <tr>
                <td class="pt-2 pb-2 text-center" colspan="2">
                    <em class="bold">No compartas tu boleto con nadie</em>
                </td>
            </tr> --}}
        </tbody>
    </table>
</body>
</html>