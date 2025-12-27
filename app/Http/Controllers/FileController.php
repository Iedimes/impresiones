<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Subsidio;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use App\Http\Controllers\SembrandoController;

class FileController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $encrypted = Crypt::encryptString('2');
        //var_dump($encrypted);
        return view('home');
    }

    public function imprimir($id,$tipo){
        $cod= Subsidio::find($id);

        if ($cod->CerProg == 4) {
            $controller =  new SembrandoController;
            return $controller->generateDocx($id,$tipo);
        }
        if ($cod->CerProg == 1) {
            $controller =  new FonavisController;
            return $controller->generateDocx($id,$tipo);
        }
        if ($cod->CerProg == 3) {
            $controller =  new ChetapyiController;
            return $controller->generateDocx($id,$tipo);
        }
        if ($cod->CerProg == 6) {
            $controller =  new AmaController;
            return $controller->generateDocx($id,$tipo);
        }
        if ($cod->CerProg == 2) {
            $controller =  new VyaRendaController;
            return $controller->generateDocx($id,$tipo);
        }
        if ($cod->CerProg == 7) {
            $controller =  new SanFranciscoController;
            return $controller->generateDocx($id,$tipo);
        }
    }
}
