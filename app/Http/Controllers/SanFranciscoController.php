<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subsidio;
use App\Localidad;
use App\Departamento;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SanFranciscoController extends Controller
{

    //protected $num = "";

    public function generateCodigo(){
        $secretkey=" ";
        for ($i = 0; $i<8; $i++)
        {
            $secretkey .= mt_rand(0,9);
        }
        return $secretkey;
    }

    public function generateDocx($id,$tipo)
    {

        $postulante = Subsidio::where('CerNro', $id)->first();
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('\sanfrancisco\template\templatesanfrancisco.docx'));

        //var_dump($this->generateCodigo);
        $nombre = \Auth::user()->username;

        if ($postulante->CerPin == null || $postulante->CerPin == 0) {

            $num=$this->generateCodigo();
            $check = Subsidio::where('CerPin', $num)->first();

            for ($i=0; $check == $num; $i++) {
                $num=$this->generateCodigo();
                $check = Subsidio::where('CerPin', $num)->first();
            }

            //$num=$this->generateCodigo();
            $postulante->CerPin = $num;
            //$postulante->CerFecImp = date('Y-m-d H:i:s.v');
            //$postulante->CerFecSus = date('Y-m-d H:i:s.v');
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
        }else {
            $num=$postulante->CerPin;
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
            /*$postulante->CerFecImp = date('Y-m-d');
            $postulante->CerFecSus = date('Y-m-d');
            $postulante->save();*/
        }


        $templateProcessor->setValue('CAMPO11', $postulante->CerposNom);
        $templateProcessor->setValue('CAMPO26', $postulante->CerNro);
        $cedula = number_format((int)$postulante->CerPosCod,0,'.','.');
        if ($postulante->CerPosCod <= 150000 ) {
            $templateProcessor->setValue('CAMPO12', 'C.I./CARNET Nº '.$cedula);
        } else {
            $templateProcessor->setValue('CAMPO12', 'C.I. Nº '.$cedula);
        }

        if ($postulante->CerCoCI == 0 && strlen(trim($postulante->CerCoNo)) == 0 ) {

            $templateProcessor->setValue('CAMPO33', '');
            //$templateProcessor->setValue('CAMPO33b', '');

        } else {

            if(strlen(trim($postulante->CerCoNo)) != 0  && ($postulante->CerCoCI == 0 || $postulante->CerCoCI == null)){
            $templateProcessor->setValue('CAMPO33', "y su cónyuge ".rtrim($postulante->CerCoNo));
            }
            if ($postulante->CerCoCI <= 150000 ) {
                //$templateProcessor->setValue('CAMPO33', 'y su cónyuge (pareja) '.$postulante->CerCoNo.', con C.I./CARNET Nº '.$postulante->CerCoCI);
            } else {
                if ($postulante->CerTDCge == 'C') {
                    $cedulaconyuge = number_format((int)$postulante->CerCoCI,0,'.','.');
                    $templateProcessor->setValue('CAMPO33', "y su cónyuge ".rtrim($postulante->CerCoNo).", con C.I. Nº ".$cedulaconyuge/*.', con C.I. Nº '.$postulante->CerCoCI*/);
                }else{
                    $templateProcessor->setValue('CAMPO33', "y su cónyuge ".rtrim($postulante->CerCoNo).", con C.I. Nº ".trim($postulante->CerCoCI));
                }

            //$templateProcessor->setValue('CAMPO33b', ", con C.I. Nº ".$cedulaconyuge);
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
            $templateProcessor->setValue('CAMPO54', number_format($postulante->CerSupViv,2,',','.'));
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

        $templateProcessor->setValue('CAMPO50', "".rtrim($postulante->CerIdent));
        if((rtrim($postulante->CerIdent)) == 'UNIFAMILIAR'){
            $templateProcessor->setValue('CAMPO65','MANZANA '.rtrim($postulante->CerManz).' LOTE '.$postulante->CerLote);

        }
        else{

            $templateProcessor->setValue('CAMPO65','MANZANA '.$postulante->CerManz.'BLOQUE '.$postulante->CerBloque.'NIVEL '.$postulante->CerNivel.'DEPARTAMENTO '.$postulante->CerNroDpto);
        }


        $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
        $templateProcessor->setValue('CAMPO56', date('d/m/Y'));
        //$templateProcessor->setValue('CAMPO12', $postulante->CerPosCod);
         // Construir la URL completa
         $num = env('APP_URL') . '/verificacion/' . $postulante->CerPin;

         // Generar el código QR
         QrCode::format('png')->size(200)->margin(0)->generate($num, storage_path("/sanfrancisco/impresion/".$postulante->CerNro."png"));

         // Insertar la imagen del código QR en el documento
         $templateProcessor->setImageValue('IMAGEN', array(
             'src' => storage_path("/sanfrancisco/impresion/".$postulante->CerNro."png"),
         ));

        $templateProcessor->saveAs(storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".docx"));
        $word = new \COM("Word.Application") or die ("Could not initialise Object.");
        // set it to 1 to see the MS Word window (the actual opening of the document)
        $word->Visible = 0;
        // recommend to set to 0, disables alerts like "Do you want MS Word to be the default .. etc"
        $word->DisplayAlerts = 0;
        // open the word 2007-2013 document
        $word->Documents->Open(storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".docx"));
        // save it as word 2003
        //$word->ActiveDocument->SaveAs('newdocument.doc');
        // convert word 2007-2013 to PDF
        $word->ActiveDocument->ExportAsFixedFormat(storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".pdf"), 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
        // quit the Word process
        $word->Quit(false);
        // clean up
        unset($word);

        return response()->download(storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".pdf"));

    }

}
