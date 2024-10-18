<div class="box box-primary">
    <div class="box-body">
    <div class="row">
            <form action="/filtros" method="post">
                {{ csrf_field() }}
        <div class="col-md-3">
            <div class="form-group">
                <label>Programa</label>
                <select id="progid" name="progid" class="form-control">
                    <option value="0">Seleccione un Programa</option>
                    <option value="1" {{ old('progid',isset($progid)?$progid:'') == '1' ? "selected":""}}>FONAVIS</option>
                    <option value="2" {{ old('progid',isset($progid)?$progid:'') == '2' ? "selected":""}}>VYA RENDA</option>
                    <option value="3" {{ old('progid',isset($progid)?$progid:'') == '3' ? "selected":""}}>CHE TAPYI</option>
                    <option value="4" {{ old('progid',isset($progid)?$progid:'') == '4' ? "selected":""}}>SEMBRANDO</option>
                    <option value="5" {{ old('progid',isset($progid)?$progid:'') == '5' ? "selected":""}}>EBY</option>
                    <option value="6" {{ old('progid',isset($progid)?$progid:'') == '6' ? "selected":""}}>AMA</option>
                    <option value="7" {{ old('progid',isset($progid)?$progid:'') == '7' ? "selected":""}}>SAN FRANCISCO</option>
                </select>
            </div>
            <h4><strong>Total: {{ $projects->total() }}</strong></h4>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Nro Resolucion</label>
                <input type="text" id="resid" value="{{old('resid',isset($resid)?$resid:'')}}" name="resid" class="form-control" placeholder="Ingrese Resolución">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="task_date">Fecha:</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" value="{{old('dateid',isset($dateid)?$dateid:'')}}"  name="dateid" id="dateid" class="form-control date"  placeholder="Ingrese Fecha">
                </div>
            </div>
        </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Cédula</label>
                    <input type="text" id="cedula" value="{{old('cedula',isset($cedula)?$cedula:'')}}" name="cedula" class="form-control" placeholder="Ingrese cedula">
                </div>
                <button type="submit" class="btn btn-primary pull-right">Buscar</button>
            </div>

        </form>
    </div>

</div>
</div>
<div class="box box-info">
    <div class="box-header with-border">
            @role('FONAVIS')
            <a href="{!! action('FonavisController@generateMasivo', ['progid' => $progid, 'resid' => $resid
                    ,'dateid' => $dateid,'cedula' => $cedula,'page' => $page,'idtipo'=>'1']) !!}" >
            <button class="btn btn-success" type="button"><i class="fa fa-print"></i> Generar Certificado </button>
            </a>
                <a href="{!! action('FonavisController@generateMasivo', ['progid' => $progid, 'resid' => $resid
                        ,'dateid' => $dateid,'cedula' => $cedula,'page' => $page,'idtipo'=>'2']) !!}" >
                <button class="btn btn-info" type="button"><i class="fa fa-print"></i> Generar Recibo </button>
                </a>
            @else

            @endrole
        <div class="pull-right">{{ $projects->appends(request()->except('_token'))->links() }}</div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="example" class="table">
            <thead>
                <tr>
                  <th>Codigo</th>
                  <th>Beneficiario</th>
                  <th style="text-align:center;">Cédula</th>
                  <th style="text-align:center;">Nro Resolución</th>
                  <th style="text-align:center;">Fecha Resolución</th>
                  <th>Programa</th>
                  <th style="text-align:center;">Impreso</th>
                  <th style="text-align:center;">Acciones</th>
                </tr>
                </thead>
            <tbody>
                @foreach($projects as $project)
              <tr>
                <td>{!! $project->CerNro !!}</td>
                <td>{!! $project->CerposNom !!}</td>
                <td style="text-align:center;">{!! $project->CerPosCod !!}</td>
                <td style="text-align:center;">{!! $project->CerResNro !!}</td>
                <td style="text-align:center;">{!! date('d-m-Y', strtotime($project->CerFeRe))  !!}</td>
                <td>{!! $name[$project->CerProg] !!}</td>
                <td style="text-align:center;">
                    @if ($project->CerPin == null || $project->CerPin == 0)
                    <i class="fa fa-warning" style="color:darkorange"></i>
                    @else
                    <i class="fa fa-check" style="color:forestgreen"></i>
                    @endif
                </td>
                <td style="text-align:center;">
                    @if ($project->CerPin == null || $project->CerPin == 0)
                    <a href="{!! action('HomeController@previaimpresion', ['id'=>$project->CerNro, 'progid' => $progid, 'resid' => $resid
                        ,'dateid' => $dateid,'cedula' => $cedula,'page' => $page]) !!}" >
                    <button class="btn btn-success" type="button"><i class="fa fa-print"></i> Imprimir </button>
                    </a>
                    @else
                    <a href="{!! action('HomeController@previaimpresion', ['id'=>$project->CerNro, 'progid' => $progid, 'resid' => $resid
                        ,'dateid' => $dateid,'cedula' => $cedula,'page' => $page]) !!}" >
                    <button class="btn btn-danger block" type="button"><i class="fa fa-print"></i> Re Imprimir </button>
                    </a>
                    @endif
                </td>
              </tr>
             @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Codigo</th>
                    <th>Beneficiario</th>
                    <th style="text-align:center;">Cédula</th>
                    <th style="text-align:center;">Nro Resolución</th>
                    <th style="text-align:center;">Fecha Resolución</th>
                    <th>Programa</th>
                    <th style="text-align:center;">Impreso</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </tfoot>
        </table>
    </div>
  </div>

@section('js')
<script src="{{asset('js/bootstrap-datepicker.js')}}"></script>
<script type="text/javascript">

    $('.date').datepicker({
       format: 'yyyy-mm-dd',
       autoclose: 'true',
       languaje: 'es'
     });


</script>

@stop
