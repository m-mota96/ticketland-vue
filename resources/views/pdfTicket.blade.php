<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Tickets</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    </head>
        {{-- <style>
            @font-face {
                font-family: 'Avenir Next LT';
                src: url('/css/AvenirNextLTPro-DemiCond.otf') format('opentype');
            }
    
            #img-background {
                position: relative;
            }
            #img-logo {
                position: absolute;
                top: 1.3rem;
                left: 14.2rem;
            }
            #boleto {
                position: absolute;
                top: 14.3rem;
                left: 18rem;
                z-index: 100;
                font-size: 12px;
                font-family: 'Avenir Next LT';
            }
            #img-main {
                position: absolute;
                top: 18.8rem;
                left: 12.3rem;
                transform: scale(0.203);
                transform-origin: top left;
                border-radius: 15px;
            }
            #data-table {
                position: absolute;
                top: 29rem;
                left: 13.2rem;
                width: 350px;
                font-family: 'Avenir Next LT';
            }
            #data-table p {
                font-size: 9px;
            }
    
            #img-qr {
                position: absolute;
                top: 44.5rem;
                left: 18.6rem;
                width: 15%;
                height: auto;
            }
            h5 {
                font-size: 10px;
            }
    </style> --}}
    <body>
        {{-- <img class="w-100" id="img-background" src="{{ asset('media/events/plantilla.png') }}">
        
        <img class="" id="img-logo" src="{{$logo}}">

        <h5 class="" id="boleto"> <b>{{$name}}</b> </h5>

        <img class="" id="img-main" src="{{$img_event}}">

        <table id="data-table">
            <tbody>
                <tr>
                    <td> <h5>Fechas del evento: </h5> <p> {{$initial_date}} a {{$final_date}} </p> </td>
                    <td> <h5>Fecha de compra:   </h5> <p> {{date('d-m-Y')}}                   </p> </td>
                </tr>
                <tr>
                    <td> <h5>Nombre del evento: </h5> <p>{{$eventName}}       </p> </td>
                    <td> <h5>Lugar de evento:   </h5> <p>{{$address ?? 'NA'}} </p> </td>
                </tr>
                <tr>
                    <td> <h5>Precio del boleto: </h5> <p>${{$price}}.00 MXN </p> </td>
                </tr>
            </tbody>
        </table>

        <img class="" id="img-qr" src="data:image/svg;base64,{{$qr}}"> --}}
        
        
        
        <img class="w-100" src="{{ $eventProfile }}" style="width: 100%;">

        <table style="width: 100%; background: #ddd8d6; padding: 20px; font-size: 1.1rem;">
            <tbody>
                <tr>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Fechas del evento: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $dates }}</span></h5>
                    </td>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Fecha de compra: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $currentDate }}</span></h5>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Nombre del evento: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $eventName }}</span></h5>
                    </td>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Lugar de evento: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $eventAddress }}</span></h5>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Tipo de boleto: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $name }}</span></h5>
                    </td>
                    <td style="width: 50%;" width="50">
                        <h5 style="margin-bottom: 0px;">Precio del boleto: <br><span style="font-weight: normal; font-size: 1.3rem;">${{ $price }} MXN</span></h5>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;" width="50" valing="top">
                        <h5 style="margin-bottom: 0px;">Nombre: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $customer_name ?? '' }}</span></h5>
                    </td>
                    <td style="width: 50%; text-align: justify;" width="50">
                        {{-- <h5>Descripción: <br><span style="font-weight: normal; font-size: 1.3rem;">{{ $eventDesc }}</span></h5> --}}
                        @if($promotion)
                            <h5 style="margin-bottom: 0px;">Descuento: <br><span style="font-weight: normal; font-size: 1.3rem;">-{{ $promotion }}%</span></h5>
                        @endif
                    </td>
                </tr>
                <tr>
                    
                    <td style="text-align: left;" colspan="2">
                        <br>
                        <img style="width: 30%;" src="data:image/png;base64,{{$qr_code}}">
                    </td>
                </tr>
            </tbody>
        </table>
        
    </body>
</html>