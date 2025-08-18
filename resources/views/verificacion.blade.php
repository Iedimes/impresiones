<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <title>MUVH - VALIDACION DE CERTIFICADO</title>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 40px; /* Espacio superior */
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Ajusta el tamaño máximo del contenedor */
            margin: 0 auto; /* Centra el contenedor */
            background-color: #f5faff; /* Color de fondo suave para la tarjeta */
            border: 1px solid #d1d1d1; /* Borde sutil */
        }
        .card-header {
            background-color: #ea2428; /* Color rojo para el encabezado */
            color: white;
            font-weight: bold;
        }
        .card-body {
            background-color: #f1f1f1; /* Fondo claro para la sección del cuerpo */
            border-radius: 0 0 10px 10px; /* Mantiene el borde redondeado en la parte inferior */
        }
        .card-title {
            margin-bottom: 0.5rem;
            color: #ea2428; /* Color rojo para los títulos */
            font-weight: bold; /* Negrita para los títulos */
        }
        .info {
            color: #000000; /* Color negro para la información */
            font-weight: bold; /* Negrita para la información */
        }
        .text-center {
            text-align: center;
        }
        .logo {
            width: 100%; /* Adaptar a ancho completo del contenedor */
            max-width: 500px; /* Ajusta el tamaño máximo de la imagen */
            height: auto; /* Mantiene la proporción */
            display: block; /* Asegura que el logo se comporte como un bloque */
            margin: 20px auto; /* Centra y agrega espaciado */
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{url('img/logofull.png')}}" class="img-fluid logo" alt="Logo"/>

        <div class="card">
            <h5 class="card-header text-center">CERTIFICADO DE SUBSIDIO DE LA VIVIENDA SOCIAL</h5>
            <div class="card-body">
                @if (isset($verificar))
                <h5 class="card-title">RESOLUCION NRO: <span class="info">{{ $verificar->CerResNro }}</span></h5>
                <h5 class="card-title">FECHA RESOLUCION: <span class="info">{{ date('d/m/Y', strtotime($verificar->CerFecCer)) }}</span></h5>
                <h5 class="card-title">BENEFICIARIO: <span class="info">{{ $verificar->CerposNom }}</span></h5>
                <h5 class="card-title">DOCUMENTO: <span class="info">{{ number_format((int)$verificar->CerPosCod, 0, ".", ".") }}</span></h5>
                <h5 class="card-title">LLAMADO NRO: <span class="info">{{ $verificar->CerLla }}/{{ $verificar->CerAno }}</span></h5>
                <h5 class="card-title">RESOLUCION LLAMADO NRO: <span class="info">{{ $verificar->CerReLla }}</span></h5>
                <h5 class="card-title">FECHA RESOLUCION LLAMADO: <span class="info">{{ date('d/m/Y', strtotime($verificar->CerReLFe)) }}</span></h5>
                <h5 class="card-title">ESTADO: <span class="info">
                    @switch($verificar->CerEst)
                        @case(1) Emitido Vigente @break
                        @case(2) Vencido @break
                        @case(3) Efectivizado @break
                        @case(4) Entregado @break
                        @case(5) Pendiente de Entrega @break
                        @case(6) Rectificado @break
                        @case(7) Anulado @break
                        @case(8) Renuncia @break
                        @case(9) Prorrogado @break
                        @case(10) Sustituido @break
                        @default Estado desconocido
                    @endswitch
                </span></h5>
                <h5 class="card-title">VALIDO HASTA: <span class="info">{{ date('d/m/Y', strtotime($verificar->CerVto)) }}</span></h5>
                @else
                <h5 class="card-header text-center" style="color:white;">No existe el registro</h5>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
</body>
</html>
