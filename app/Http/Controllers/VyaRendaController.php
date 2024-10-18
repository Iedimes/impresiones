<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subsidio;
use App\Localidad;
use App\Departamento;
use App\Grupo;
use App\Persona;
use App\ManLote;
use App\LoteManz;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class VyarendaController extends Controller
{

    public function generateCodigo(){
        $secretkey=" ";
        for ($i = 0; $i<8; $i++)
        {
            $secretkey .= mt_rand(0,9);
        }
        return $secretkey;
    }

    public function cargaVariable(){

    }

    public function generateDocx($id,$tipo)
    {

        $postulante = Subsidio::where('CerNro', $id)->first();
       // return ($postulante);
        $sat = Persona::where('PerCod', $postulante->CerNucCod)->first();
        $titular = Persona::where('PerCod', $postulante->CerPosCod)->first();
        $CerNro = $postulante->CerPosCod;
        $CerNro = substr($CerNro, 0, strpos($CerNro, ' '));

        $nombre = \Auth::user()->username;
        if ($tipo == 1) {
            $ext="CS";
            switch ($postulante->CerMod) {
                case "VR":
                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/vyarenda/template/vyarenda1304.docx'));
                break;
            }
        }else{
            $ext="RC";
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/vyarenda/template/vyarendarecibo.docx'));
        }

        if ($tipo == 3) {
            switch ($postulante->CerMod) {
        case "VR":
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/vyarenda/template/vyarenda1378.docx'));
        break;
    default:
        //echo "Your favorite color is neither red, blue, nor green!";
        return "No existe platilla";
        //$ext="CS";
         }
          }


        if ($postulante->CerPin == null || $postulante->CerPin == 0) {

            //$check = Subsidio::where('CerPin', $id)->first();

            $num=$this->generateCodigo();
            $check = Subsidio::where('CerPin', $num)->first();

            for ($i=0; isset($check->CerPin); $i++) {
                $num=$this->generateCodigo();
                $check = Subsidio::where('CerPin', $num)->first();
            }

            $postulante->CerPin = $num;
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
        }else {
            $num=$postulante->CerPin;
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
        }

        if ($titular->PerSexo == 'M') {
            $templateProcessor->setValue('CAMPO11', ' al Señor '.rtrim($postulante->CerposNom));
        } else {
            $templateProcessor->setValue('CAMPO11', ' a la Señora '.rtrim($postulante->CerposNom));
        }

        $templateProcessor->setValue('CAMPO111', rtrim($postulante->CerposNom));

        if($postulante->CerMod == "CV"){

        }else{
            $report = Grupo::where('NucCod', '=', $postulante->CerNucCod)
            ->where('GnuCod', '=', $postulante->CerGnuCod)
            ->first();
            //$templateProcessor->setValue('CAMPO23', $report->GnuNom);
        }

        if ($postulante->CerNucNom == 0) {
            $templateProcessor->setValue('CAMPO73', '');
            $templateProcessor->setValue('CAMPO74', '');
        } else {
            if ($postulante->CerEst == 6) {
                if ($postulante->CerRect2Nr == 0) {
                    $templateProcessor->setValue('CAMPO73', 'y rectificado por la Resolución Nº '.$postulante->CerRectNro.' de fecha '.date('d/m/Y', strtotime($postulante->CerRectFec)));
                    $templateProcessor->setValue('CAMPO74', '');
                } else {
                    $templateProcessor->setValue('CAMPO73', ', rectificados por Resolución Nº '.$postulante->CerRectNro.' de fecha '.
                    date('d/m/Y', strtotime($postulante->CerRectFec)).
                    ' y Resolución Nº '.
                    $postulante->CerRect2Nr.' de fecha '.date('d/m/Y', strtotime($postulante->CerRect2Fe)));
                    $templateProcessor->setValue('CAMPO74', '');
                }

            }
        }

        switch ($postulante->CerPlzOrig) {
            case 6:
                $templateProcessor->setValue('CAMPO31', '6 meses (seis)');
                break;
            case 9:
                $templateProcessor->setValue('CAMPO31', '9 meses (nueve)');
                break;
            case 18:
                $templateProcessor->setValue('CAMPO31', '18 meses (diez y ocho)');
                break;
        }
        $templateProcessor->setValue('CAMPO32', date('d/m/Y', strtotime($postulante->CerVig)));
        $templateProcessor->setValue('CAMPO20', $postulante->CerNivCod);
        $templateProcessor->setValue('CAMPO21', number_format($postulante->CerMonUSM,2,',','.'));
        setlocale(LC_ALL,"es_ES");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $templateProcessor->setValue('CAMPO27', 'Asunción, '.date('d', strtotime($postulante->CerFeRe)).' de '.$meses[date('m', strtotime($postulante->CerFeRe))-1].
        ' de '.date('Y', strtotime($postulante->CerFeRe)));
        if (empty($postulante->CerObsSub)) {
            $templateProcessor->setValue('CAMPO57', '');
        } else {
            $templateProcessor->setValue('CAMPO57', 'Observación: '.$postulante->CerObsSub);
        }
        if ($postulante->CerEst == 6) {
            $templateProcessor->setValue('CAMPO35', 'Certificado Rectificado, impreso en fecha '.date('d/m/Y'));
        } else {
            $templateProcessor->setValue('CAMPO35', '');
        }
        $templateProcessor->setValue('CAMPO17', rtrim($postulante->CerLla).'/'.rtrim($postulante->CerAno));
        $templateProcessor->setValue('CAMPO18', $postulante->CerReLla);
        $templateProcessor->setValue('CAMPO30', date('d/m/Y', strtotime($postulante->CerReLFe)));

        $templateProcessor->setValue('CAMPO25', $postulante->CerIdent);
        $templateProcessor->setValue('CAMPO26', $postulante->CerNro);
        $cedula = number_format((int)$postulante->CerPosCod,0,'.','.');
        if ($postulante->CerPosCod <= 150000 ) {
            $templateProcessor->setValue('CAMPO12', 'C.I./CARNET Nº '.$cedula);
        } else {
            $templateProcessor->setValue('CAMPO12', 'C.I. Nº '.$cedula);
        }

        if ($postulante->CerCoCI == 0 || strlen($postulante->CerCoNo) == 0 ) {

            if ($titular->PerSexo == 'M') {
                $templateProcessor->setValue('CAMPO33', '');
            } else {
                $templateProcessor->setValue('CAMPO33', '');
            }




        } else {

            if ($postulante->CerCoCI <= 150000 ) {
                //$templateProcessor->setValue('CAMPO33', 'y su cónyuge (pareja) '.$postulante->CerCoNo.', con C.I./CARNET Nº '.$postulante->CerCoCI);
            } else {
            $templateProcessor->setValue('CAMPO33', "y su cónyuge (pareja) ".rtrim($postulante->CerCoNo).', con C.I. Nº '.number_format((int)$postulante->CerCoCI,0,'.','.').'');
            //$templateProcessor->setValue('CAMPO33b', ", con C.I. Nº ".number_format((int)$postulante->CerCoCI,0,'.','.'));
                //$campo33=print_r('y su cónyuge (pareja) '.$postulante->CerCoNo.', con C.I. Nº '.$postulante->CerCoCI,true);
            }
        }
        $templateProcessor->setValue('CAMPO133', rtrim($postulante->CerCoNo));
        $templateProcessor->setValue('CAMPO112', "C.I. Nº ".number_format((int)$postulante->CerCoCI,0,'.','.'));
        $templateProcessor->setValue('CAMPO14', $postulante->CerResNro);
        $templateProcessor->setValue('CAMPO22', number_format($postulante->CerUsm,2,',','.'));
        if ($postulante->CerTipViv == '') {
            $templateProcessor->setValue('CAMPO53', 'VR-2D');
        } else {
            $templateProcessor->setValue('CAMPO53', $postulante->CerTipViv);
        }

        if ($postulante->CerSupViv <= 0) {
            $templateProcessor->setValue('CAMPO54', '43.50');
        } else {
            $templateProcessor->setValue('CAMPO54', $postulante->CerSupViv);
        }
        $ciudad = Localidad::find($postulante->CerCiuId);
        if ($postulante->CerCiuId == 0) {

        } else {
            $templateProcessor->setValue('CAMPO42', $ciudad->CiuNom);
        }

        $depto = Departamento::find($postulante->CerDptoId);
        if ($postulante->CerDptoId == 0) {

        } else {
            $templateProcessor->setValue('CAMPO43', $depto->DptoNom);
        }

        //$ubicacion = ManLote::find($postulante->CerPosCod,$postulante->CerPryCod);
        /*$ubicacion = ManLote::where('SolPerCod', '=', $postulante->CerPosCod)
            ->where('PylCod', '=', $postulante->CerPryCod)
            ->first();

        $templateProcessor->setValue('CAMPO9', $ubicacion->ManCod);
        $templateProcessor->setValue('CAMPO08', $ubicacion->VivLote);*/
        $ubi = LoteManz::where('CerPosCod', '=', $postulante->CerPosCod)
        ->where('CerNro', '=', $postulante->CerNro)
        ->first();

    $templateProcessor->setValue('CAMPO9', $ubi->CerManz);
    $templateProcessor->setValue('CAMPO08', $ubi->CerLote);


        if ($postulante->CerIndert == '') {
            $templateProcessor->setValue('CAMPO55', '1061/15');
        } else {
            $templateProcessor->setValue('CAMPO55', $postulante->CerIndert);
        }


        $templateProcessor->setValue('CAMPO50', $postulante->CerIdent);
        $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
        $templateProcessor->setValue('CAMPO56', date('d/m/Y'));
        //$templateProcessor->setValue('CAMPO12', $postulante->CerPosCod);
        \QrCode::format('png')->size(110)->margin(0)->generate($num,storage_path("/vyarenda/impresion/".$CerNro."png"));
        $templateProcessor->setImg('IMAGEN', array(
            'src'  => storage_path("/vyarenda/impresion/".$CerNro."png")//,
            //'size' => array( 130, 120 ) //px
        ));
        $templateProcessor->saveAs(storage_path("/vyarenda/impresion/".$CerNro.".docx"));
        $word = new \COM("Word.Application") or die ("Could not initialise Object.");
        // set it to 1 to see the MS Word window (the actual opening of the document)
        $word->Visible = 0;
        // recommend to set to 0, disables alerts like "Do you want MS Word to be the default .. etc"
        $word->DisplayAlerts = 0;
        // open the word 2007-2013 document
        $word->Documents->Open(storage_path("/vyarenda/impresion/".$CerNro.".docx"));
        // save it as word 2003
        //$word->ActiveDocument->SaveAs('newdocument.doc');
        // convert word 2007-2013 to PDF
        $word->ActiveDocument->ExportAsFixedFormat(storage_path("/vyarenda/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf"), 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
        // quit the Word process
        $word->Quit(false);
        // clean up
        unset($word);

        if ($tipo == 99) {
            return response()->download(storage_path("/vyarenda/impresion/".$CerNro.".docx"));
        }else{
            return response()->download(storage_path("/vyarenda/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf"));
        }



    }

    /*public function generateMasivo(Request $request){

            $s = $request->input('dateid');
            $dt = new \DateTime($s);
            $date = $dt->format('Y-d-m H:i:s.v');
            $projects = Subsidio::where('CerProg', $request->input('progid'))
            ->where('CerResNro','=', $request->input('resid'))
            ->where('CerFeRe','=', $date)
            ->orderBy(DB::raw('SUBSTRING(CerNro, 4,  15)'),'asc')
            //->sortBy('CerPosCod')
            ->paginate(15);
            $time = time();
            $name='FONAVIS'.'-'.$request->input('resid').'-'.$request->input('dateid').'-p'.$request->input('page').'-'.$time.'.zip';
            $zipper = new \Chumper\Zipper\Zipper;

            if ($request->input('idtipo') == 1) {
                $ext="CS";
            }else{
                $ext="RC";
            }

            foreach ($projects as $key => $value) {
                //echo $value->CerNro.'</br>';
                $this->generateDocxMulti($value->CerNro,$request->input('idtipo'));
                //$zip->addFile(storage_path("/fonavis/impresion/".$value->CerPosCod.".pdf"));
                $zipper->make(storage_path("/fonavis/impresion/".$name))->folder('')->add(storage_path("/fonavis/impresion/".$ext.substr(rtrim($value->CerNro), 5).'_'.rtrim($value->CerPosCod).".pdf"));
            }
            $zipper->close();
            return response()->download(storage_path("/fonavis/impresion/".$name));
    }
*/


   /* public function generateDocxMulti($id,$tipo)
    {

        $postulante = Subsidio::where('CerNro', $id)->first();
        $sat = Persona::where('PerCod', $postulante->CerNucCod)->first();
        $titular = Persona::where('PerCod', $postulante->CerPosCod)->first();
        $CerNro = $postulante->CerPosCod;
        $CerNro = substr($CerNro, 0, strpos($CerNro, ' '));

        $nombre = \Auth::user()->username;
        /*if ($postulante->CerMod == 'CH') {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/chtemplate.docx'));
        }

        $favcolor = "red";

        if ($tipo == 1) {
            $ext="CS";
        }else{
            $ext="RC";
        }

        switch ($postulante->CerMod) {
            case "CH":
            if ($tipo == 1) {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/chtemplate.docx'));
            } else {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/chrecibo.docx'));
            }

            break;
            case "TI":
            if ($tipo == 1) {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/titemplate.docx'));
            } else {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/tirecibo.docx'));
            }
                break;
            case "green":
                echo "Your favorite color is green!";
                break;
            default:
                //echo "Your favorite color is neither red, blue, nor green!";
                return "No existe platilla";
        }

        if ($postulante->CerPin == null || $postulante->CerPin == 0) {
            $num=$this->generateCodigo();
            $postulante->CerPin = $num;
            //$postulante->CerFecImp = date('Y-m-d H:i:s.v');
            //$postulante->CerFecSus = date('Y-m-d H:i:s.v');
            $postulante->CerUsuImp = substr($nombre, 0, 9);
            $postulante->save();
        }else {
            $num=$postulante->CerPin;
        }

        if ($titular->PerSexo == 'M') {
            $templateProcessor->setValue('CAMPO11', 'al Señor: '.rtrim($postulante->CerposNom));
        } else {
            $templateProcessor->setValue('CAMPO11', 'a la Señora: '.rtrim($postulante->CerposNom));
        }

        $report = Grupo::where('NucCod', '=', $postulante->CerNucCod)
                ->where('GnuCod', '=', $postulante->CerGnuCod)
                ->first();
        //$templateProcessor->setValue('CAMPO23', $report->GnuNom);

        if ($postulante->CerNucNom == 0) {
            $templateProcessor->setValue('CAMPO73', '');
            $templateProcessor->setValue('CAMPO74', '');
        } else {
            if ($postulante->CerEst == 6) {
                if ($postulante->CerRect2Nr == 0) {
                    $templateProcessor->setValue('CAMPO73', 'y rectificado por la Resolución Nº '.$postulante->CerRectNro.' de fecha '.date('d/m/Y', strtotime($postulante->CerRectFec)));
                    $templateProcessor->setValue('CAMPO74', '');
                } else {
                    $templateProcessor->setValue('CAMPO73', ', rectificados por Resolución Nº '.$postulante->CerRectNro.' de fecha '.
                    date('d/m/Y', strtotime($postulante->CerRectFec)).
                    ' y Resolución Nº '.
                    $postulante->CerRect2Nr.' de fecha '.date('d/m/Y', strtotime($postulante->CerRect2Fe)));
                    $templateProcessor->setValue('CAMPO74', '');
                }

            }
        }

        switch ($postulante->CerPlzOrig) {
            case 6:
                $templateProcessor->setValue('CAMPO31', '6 meses (seis)');
                break;
            case 9:
                $templateProcessor->setValue('CAMPO31', '9 meses (nueve)');
                break;
            case 18:
                $templateProcessor->setValue('CAMPO31', '18 meses (diez y ocho)');
                break;
        }
        $templateProcessor->setValue('CAMPO32', date('d/m/Y', strtotime($postulante->CerVig)));
        $templateProcessor->setValue('CAMPO20', $postulante->CerNivCod);
        $templateProcessor->setValue('CAMPO21', number_format($postulante->CerMonUSM,2,',','.'));
        setlocale(LC_ALL,"es_ES");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $templateProcessor->setValue('CAMPO27', 'Asunción, '.date('d', strtotime($postulante->CerFeRe)).' de '.$meses[date('m', strtotime($postulante->CerFeRe))-1].
        ' de '.date('Y', strtotime($postulante->CerFeRe)));
        if (isset($postulante->CerObsSub)) {
            $templateProcessor->setValue('CAMPO57', '');
        } else {
            $templateProcessor->setValue('CAMPO57', 'Observación: '.$postulante->CerObsSub);
        }
        if ($postulante->CerEst == 6) {
            $templateProcessor->setValue('CAMPO35', 'Certificado Rectificado, impreso en fecha '.date('d/m/Y'));
        } else {
            $templateProcessor->setValue('CAMPO35', '');
        }
        $templateProcessor->setValue('CAMPO17', rtrim($postulante->CerLla).'/'.rtrim($postulante->CerAno));
        $templateProcessor->setValue('CAMPO18', $postulante->CerReLla);
        $templateProcessor->setValue('CAMPO30', date('d/m/Y', strtotime($postulante->CerReLFe)));

        $templateProcessor->setValue('CAMPO25', $postulante->CerNucNom);
        $templateProcessor->setValue('CAMPO26', $postulante->CerNro);
        $cedula = number_format((int)$postulante->CerPosCod,0,'.','.');
        if ($postulante->CerPosCod <= 150000 ) {
            $templateProcessor->setValue('CAMPO12', 'C.I./CARNET Nº '.$cedula);
        } else {
            $templateProcessor->setValue('CAMPO12', 'C.I. Nº '.$cedula);
        }

        if ($postulante->CerCoCI == 0 || strlen($postulante->CerCoNo) == 0 ) {

            if ($titular->PerSexo == 'M') {
                $templateProcessor->setValue('CAMPO33', '');
            } else {
                $templateProcessor->setValue('CAMPO33', '');
            }




        } else {

            if ($postulante->CerCoCI <= 150000 ) {
                //$templateProcessor->setValue('CAMPO33', 'y su cónyuge (pareja) '.$postulante->CerCoNo.', con C.I./CARNET Nº '.$postulante->CerCoCI);
            } else {
            $templateProcessor->setValue('CAMPO33', "y su cónyuge (pareja) ".rtrim($postulante->CerCoNo).', con C.I. Nº '.number_format((int)$postulante->CerCoCI,0,'.','.').'');
            //$templateProcessor->setValue('CAMPO33b', ", con C.I. Nº ".number_format((int)$postulante->CerCoCI,0,'.','.'));
                //$campo33=print_r('y su cónyuge (pareja) '.$postulante->CerCoNo.', con C.I. Nº '.$postulante->CerCoCI,true);
            }
        }
        //$templateProcessor->setValue('CAMPO33', $campo33);
        $templateProcessor->setValue('CAMPO14', $postulante->CerResNro);
        $templateProcessor->setValue('CAMPO22', number_format($postulante->CerUsm,2,',','.'));
        if ($postulante->CerTipViv == '') {
            $templateProcessor->setValue('CAMPO53', 'VR-2D');
        } else {
            $templateProcessor->setValue('CAMPO53', $postulante->CerTipViv);
        }

        if ($postulante->CerSupViv <= 0) {
            $templateProcessor->setValue('CAMPO54', '43.50');
        } else {
            $templateProcessor->setValue('CAMPO54', $postulante->CerSupViv);
        }
        $ciudad = Localidad::find($postulante->CerCiuId);
        $templateProcessor->setValue('CAMPO42', $ciudad->CiuNom);

        $depto = Departamento::find($postulante->CerDptoId);
        $templateProcessor->setValue('CAMPO43', $depto->DptoNom);



        if ($postulante->CerIndert == '') {
            $templateProcessor->setValue('CAMPO55', '1061/15');
        } else {
            $templateProcessor->setValue('CAMPO55', $postulante->CerIndert);
        }


        $templateProcessor->setValue('CAMPO50', $postulante->CerIdent);
        $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
        $templateProcessor->setValue('CAMPO56', date('d/m/Y'));
        //$templateProcessor->setValue('CAMPO12', $postulante->CerPosCod);
        \QrCode::format('png')->size(110)->margin(0)->generate($num,storage_path("/fonavis/impresion/".$CerNro."png"));
        $templateProcessor->setImg('IMAGEN', array(
            'src'  => storage_path("/fonavis/impresion/".$CerNro."png")//,
            //'size' => array( 130, 120 ) //px
        ));
        $templateProcessor->saveAs(storage_path("/fonavis/impresion/".$CerNro.".docx"));
        $word = new \COM("Word.Application") or die ("Could not initialise Object.");
        // set it to 1 to see the MS Word window (the actual opening of the document)
        $word->Visible = 0;
        // recommend to set to 0, disables alerts like "Do you want MS Word to be the default .. etc"
        $word->DisplayAlerts = 0;
        // open the word 2007-2013 document
        $word->Documents->Open(storage_path("/fonavis/impresion/".$CerNro.".docx"));
        // save it as word 2003
        //$word->ActiveDocument->SaveAs('newdocument.doc');
        // convert word 2007-2013 to PDF
        $word->ActiveDocument->ExportAsFixedFormat(storage_path("/fonavis/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf"), 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
        // quit the Word process
        $word->Quit(false);
        // clean up
        unset($word);

        //return response()->download(storage_path("/fonavis/impresion/".$CerNro.".pdf"));

    }*/
}
