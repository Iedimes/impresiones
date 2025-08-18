<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subsidio;
use App\Localidad;
use App\Departamento;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['verificacion']]);
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function index(Request $request)
    {

        /*var_dump($request->input('progid'));
        var_dump($request->input('resid'));
        var_dump($request->input('dateid'));
        var_dump($request->input('cedula'));*/

        if ($request->input('cedula')) {
            $projects= Subsidio::where('CerPosCod', $request->input('cedula'))->where ('CerEst','=','1')->paginate(15);

        } else {
            //var_dump('sin cedula');
            if ($request->has(['progid', 'resid','dateid'])) {

                $s = $request->input('dateid');
                $dt = new \DateTime($s);
                $date = $dt->format('Y-d-m H:i:s.v');
                $projects = Subsidio::where('CerProg', $request->input('progid'))
                ->where('CerResNro','=', $request->input('resid'))
                ->where('CerFeRe','=', $date)
                ->orderBy(DB::raw('SUBSTRING(CerNro, 4,  15)'),'asc')
                //->sortBy('CerPosCod')
                ->paginate(15);

            }else {
                $projects = Subsidio::paginate(15);
            }
        }

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




        $progid=$request->input('progid');
        $resid=$request->input('resid');
        $dateid=$request->input('dateid');
        $cedula=$request->input('cedula');
        $page=$request->input('page');

        return view('home',compact('projects','progid','dateid','resid','cedula','name','page'));
    }

    public function previaimpresion($id, Request $request){


        $subsidio = Subsidio::find($id);
        //var_dump($request->progid);
        $progid=$request->input('progid');
        $resid=$request->input('resid');
        $dateid=$request->input('dateid');
        $cedula=$request->input('cedula');
        $page=$request->input('page');

        $ciudad = Localidad::find($subsidio->CerCiuId);
        $depto = Departamento::find($subsidio->CerDptoId);

        return view('previa',compact('subsidio','progid','dateid','resid','cedula','page','ciudad','depto'));
    }



    public function verificacion($key)
    {

        //return $key;

        $verificar = Subsidio::where('CerPin', $key)->first();

        return view('verificacion', compact('verificar'));
    }
}
