@extends('adminlte::page')

@section('content')
@php
    $name = array(
            0 => 'N/D',
            1 => 'FONAVIS',
            2 => 'VYA RENDA',
            3 => 'CHE TAPYI',
            4 => 'SEMBRANDO',
            5 => 'EBY',
            6 => 'AMA',
            7 => 'SAN FRANCISCO',
    );


@endphp
@role($name[$subsidio->CerProg])
<section class="invoice">
  <!-- title row -->
  <div class="row">
    <div class="col-xs-12">
      <h2 class="page-header">
        <i class="fa fa-user"></i> {{$subsidio->CerposNom}}
        <small class="pull-right"><strong>{{$subsidio->CerNro}}</strong></small>
      </h2>
    </div>
    <!-- /.col -->
  </div>
  <!-- info row -->
  <div class="row invoice-info">
    <div class="col-sm-4 invoice-col">
      <strong>Titular</strong>
      <address>
        Nombre: {{$subsidio->CerposNom}}<br>
        Documento: {{$subsidio->CerPosCod}}<br>
        Fecha de Adjudicación: <strong>{{date('d/m/Y', strtotime($subsidio->CerFeRe))}}</strong>
      </address>

    </div>
    <!-- /.col -->
    <div class="col-sm-4 invoice-col">
        <strong>Cónyuge (pareja)</strong>
      <address>
        Nombre: {{$subsidio->CerCoNo}}<br>
        Documento: {{$subsidio->CerCoCI}}<br>

      </address>
    </div>
    <!-- /.col -->
    <div class="col-sm-4 invoice-col">
        <strong>Ubicación</strong>
        <address>
          Lugar: {{$subsidio->CerIdent}}<br>
          @if (isset($ciudad->CiuNom))
          Distrito: {{$ciudad->CiuNom}}<br>
          @else
          Distrito: N/A<br>
          @endif

          @if (isset($depto->DptoNom))
          Departamento: {{$depto->DptoNom}}<br>
          @else
          Departamento: N/A<br>
          @endif

        </address>
    </div>
    <!-- /.col -->
  </div>

  <!-- this row will not appear when printing -->
  <div class="row no-print">
    <div class="col-xs-12">
        @role('admin')
        <a href="{!! action('FileController@imprimir', ['id'=>$subsidio->CerNro,'idtipo'=>'99']) !!}" >
            <button type="button" class="btn btn-danger pull-right" id="recibo" style="margin-right: 5px;">
                <i class="fa  fa-file-word-o"></i> Word
              </button>
          </a>
          @endrole
        <a href="{!! action('FileController@imprimir', ['id'=>$subsidio->CerNro,'idtipo'=>'2']) !!}" >
          <button type="button" class="btn btn-info pull-right" id="recibo" style="margin-right: 5px;">
              <i class="fa fa-download"></i> Imprimir Recibo
            </button>
        </a>
        @if($subsidio->CerMod == "VR")
        <a href="{!! action('FileController@imprimir', ['id'=>$subsidio->CerNro,'idtipo'=>'1']) !!}" >
            <button type="button" id="show" class="btn btn-success pull-right" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Imprimir Resolucion 1304
              </button>
        </a>
        <a href="{!! action('FileController@imprimir', ['id'=>$subsidio->CerNro,'idtipo'=>'3']) !!}" >
            <button type="button" id="show" class="btn btn-success pull-right" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Imprimir Resolucion 1378
              </button>
        </a>
        @else
        <a href="{!! action('FileController@imprimir', ['id'=>$subsidio->CerNro,'idtipo'=>'1']) !!}" >
            <button type="button" id="show" class="btn btn-success pull-right" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Imprimir Certificado
              </button>
        </a>
        @endif
        <a href="{!! action('HomeController@index', ['progid' => $progid, 'resid' => $resid
            ,'dateid' => $dateid,'cedula' => $cedula,'page' => $page]) !!}" >
            <button type="button" class="btn btn-warning pull-right" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Volver
              </button>
        </a>
    </div>
  </div>
</section>
@else
    <h2>No posee permisos para ver este Programa</h2>
@endrole


@endsection

@section('js')
<script>
  $(document).ready(function(){
    $("#show").click(function(){
      setTimeout(
            function() {
              $("#recibo").show();
            }, 3000);

            });
  });

  if ({{json_encode($subsidio->CerPin)}} == null) {
    $("#recibo").hide();
  }

  </script>
@endsection
