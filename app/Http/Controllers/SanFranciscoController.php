<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subsidio;
use App\Localidad;
use App\Departamento;
use Illuminate\Support\Facades\DB;
use ZipArchive;
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

        $conyugeCI = (int)trim($postulante->CerCoCI);
        $conyugeNom = trim($postulante->CerCoNo);

        if ($conyugeCI == 0 || $conyugeNom == "" || $conyugeNom == "0") {
            $templateProcessor->setValue('CAMPO33', '');
        } else {
            $cedulaconyuge = number_format($conyugeCI, 0, '.', '.');
            if ($conyugeCI <= 150000) {
                $templateProcessor->setValue('CAMPO33', "y su cónyuge " . rtrim($postulante->CerCoNo) . ", con C.I./CARNET Nº " . $cedulaconyuge);
            } else {
                $templateProcessor->setValue('CAMPO33', "y su cónyuge " . rtrim($postulante->CerCoNo) . ", con C.I. Nº " . $cedulaconyuge);
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

         if (!file_exists(storage_path("/sanfrancisco/impresion/"))) {
             mkdir(storage_path("/sanfrancisco/impresion/"), 0755, true);
         }

         // Generar el código QR
         QrCode::format('png')->size(200)->margin(0)->generate($num, storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".png"));

         // Insertar la imagen del código QR en el documento
         $templateProcessor->setImageValue('IMAGEN', array(
             'src' => storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".png"),
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

        return response()->download(storage_path("/sanfrancisco/impresion/".$postulante->CerNro.".pdf"))->deleteFileAfterSend(true);

    }

    public function generateMasivo(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $s = $request->input('dateid');
        $dt = new \DateTime($s);
        $date = $dt->format('Y-d-m H:i:s.v');

        $projects = Subsidio::where('CerProg', $request->input('progid'))
            ->where('CerResNro', '=', $request->input('resid'))
            ->where('CerFeRe', '=', $date)
            ->orderBy(DB::raw('SUBSTRING(CerNro, 4, 15)'), 'asc')
            ->paginate(15);

        $time = time();
        $name = 'SANFRANCISCO' . '-' . $request->input('resid') . '-' . $request->input('dateid') . '-' . $time . '.zip';

        $zip = new \ZipArchive;

        $procesados = 0;
        $errores = 0;
        $erroresDetalle = [];

        try {
            $zipPath = storage_path("/sanfrancisco/impresion/" . $name);

            $directory = dirname($zipPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            if (!is_writable($directory)) {
                throw new \Exception('El directorio no es escribible: ' . $directory);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

                \Log::info("Iniciando generación de " . count($projects) . " certificados SANFRANCISCO");

                foreach ($projects as $index => $value) {
                    try {
                        \Log::info("Procesando certificado " . ($index + 1) . "/" . count($projects) . ": " . $value->CerNro);

                        $this->generateDocxMulti($value->CerNro, $request->input('idtipo'));

                        $fileName = trim($value->CerNro) . ".pdf";
                        $filePath = storage_path("/sanfrancisco/impresion/" . $fileName);

                        if (file_exists($filePath) && filesize($filePath) > 0) {
                            $zip->addFile($filePath, $fileName);
                            $procesados++;
                            \Log::info("✓ Certificado agregado al ZIP: " . $fileName);
                        } else {
                            $errores++;
                            $erroresDetalle[] = "Archivo no generado o vacío: " . $value->CerNro;
                            \Log::warning("✗ Archivo no existe o está vacío: " . $fileName);
                        }

                        if (($index + 1) % 10 == 0) {
                            gc_collect_cycles();
                        }

                    } catch (\Exception $docError) {
                        $errores++;
                        $mensaje = "Error en certificado " . $value->CerNro . ": " . $docError->getMessage();
                        $erroresDetalle[] = $mensaje;
                        \Log::error($mensaje);
                        continue;
                    }
                }

                $zip->close();

                \Log::info("Proceso completado - Procesados: $procesados, Errores: $errores");

                if (count($erroresDetalle) > 0) {
                    \Log::warning("Errores detallados: " . implode(" | ", $erroresDetalle));
                }

                if ($procesados == 0) {
                    return response()->json([
                        'error' => 'No se pudo generar ningún certificado',
                        'detalles' => $erroresDetalle
                    ], 500);
                }

                sleep(2);
                $this->limpiarArchivosTemporales(storage_path("/sanfrancisco/impresion/"));

                return response()->download($zipPath)->deleteFileAfterSend(true);

            } else {
                throw new \Exception('No se pudo crear el archivo ZIP.');
            }

        } catch (\Exception $e) {
            \Log::error("Error crítico al generar el ZIP: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al crear el archivo ZIP.',
                'mensaje' => $e->getMessage(),
                'procesados' => $procesados,
                'errores' => $errores
            ], 500);
        }
    }

    public function generateDocxMulti($id, $tipo)
    {
        $postulante = Subsidio::where('CerNro', $id)->first();

        if (!$postulante) {
            throw new \Exception("No se encontró el postulante con CerNro: $id");
        }

        $nombre = \Auth::user()->username;
        $cerNroClean = trim($postulante->CerNro);

        $docxPath = storage_path("/sanfrancisco/impresion/" . $cerNroClean . ".docx");
        $qrPath = storage_path("/sanfrancisco/impresion/" . $cerNroClean . ".png");
        $pdfPath = storage_path("/sanfrancisco/impresion/" . $cerNroClean . ".pdf");

        if (file_exists($docxPath)) {
            $intentos = 0;
            while (file_exists($docxPath) && $intentos < 3) {
                if (@unlink($docxPath)) {
                    break;
                }
                $docxPath = storage_path("/sanfrancisco/impresion/" . $cerNroClean . "_" . time() . ".docx");
                $intentos++;
            }
        }

        if (file_exists($qrPath)) {
            @unlink($qrPath);
        }

        $templatePath = storage_path('/sanfrancisco/template/templatesanfrancisco.docx');

        if (!file_exists($templatePath)) {
            throw new \Exception("Plantilla no encontrada: " . $templatePath);
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        if ($postulante->CerPin == null || $postulante->CerPin == 0) {
            $num = $this->generateCodigo();
            $check = Subsidio::where('CerPin', $num)->first();

            while ($check) {
                $num = $this->generateCodigo();
                $check = Subsidio::where('CerPin', $num)->first();
            }

            $postulante->CerPin = $num;
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
        } else {
            $num = $postulante->CerPin;
            $postulante->CerUsuImp = substr($nombre, 0, 10);
            $postulante->save();
        }

        $templateProcessor->setValue('CAMPO11', $postulante->CerposNom);
        $templateProcessor->setValue('CAMPO26', $postulante->CerNro);
        $cedula = number_format((int)$postulante->CerPosCod, 0, '.', '.');
        if ($postulante->CerPosCod <= 150000) {
            $templateProcessor->setValue('CAMPO12', 'C.I./CARNET Nº ' . $cedula);
        } else {
            $templateProcessor->setValue('CAMPO12', 'C.I. Nº ' . $cedula);
        }

        $conyugeCI = (int)trim($postulante->CerCoCI);
        $conyugeNom = trim($postulante->CerCoNo);

        if ($conyugeCI == 0 || $conyugeNom == "" || $conyugeNom == "0") {
            $templateProcessor->setValue('CAMPO33', '');
        } else {
            $cedulaconyuge = number_format($conyugeCI, 0, '.', '.');
            if ($conyugeCI <= 150000) {
                $templateProcessor->setValue('CAMPO33', "y su cónyuge " . rtrim($postulante->CerCoNo) . ", con C.I./CARNET Nº " . $cedulaconyuge);
            } else {
                $templateProcessor->setValue('CAMPO33', "y su cónyuge " . rtrim($postulante->CerCoNo) . ", con C.I. Nº " . $cedulaconyuge);
            }
        }

        $templateProcessor->setValue('CAMPO14', $postulante->CerResNro);
        $templateProcessor->setValue('CAMPO22', number_format($postulante->CerUsm, 2, ',', '.'));
        $templateProcessor->setValue('CAMPO53', ($postulante->CerTipViv == '') ? 'VR-2D' : $postulante->CerTipViv);
        $templateProcessor->setValue('CAMPO54', ($postulante->CerSupViv <= 0) ? '43.50' : number_format($postulante->CerSupViv, 2, ',', '.'));

        $ciudad = Localidad::find($postulante->CerCiuId);
        $templateProcessor->setValue('CAMPO42', $ciudad->CiuNom ?? '');

        $depto = Departamento::find($postulante->CerDptoId);
        $templateProcessor->setValue('CAMPO43', $depto->DptoNom ?? '');

        $templateProcessor->setValue('CAMPO55', ($postulante->CerIndert == '') ? '1061/15' : $postulante->CerIndert);
        $templateProcessor->setValue('CAMPO50', "" . rtrim($postulante->CerIdent));

        if (rtrim($postulante->CerIdent) == 'UNIFAMILIAR') {
            $templateProcessor->setValue('CAMPO65', 'MANZANA ' . rtrim($postulante->CerManz) . ' LOTE ' . $postulante->CerLote);
        } else {
            $templateProcessor->setValue('CAMPO65', 'MANZANA ' . $postulante->CerManz . 'BLOQUE ' . $postulante->CerBloque . 'NIVEL ' . $postulante->CerNivel . 'DEPARTAMENTO ' . $postulante->CerNroDpto);
        }

        $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
        $templateProcessor->setValue('CAMPO56', date('d/m/Y'));

        $num = env('APP_URL') . '/verificacion/' . $postulante->CerPin;
        QrCode::format('png')->size(200)->margin(0)->generate($num, $qrPath);

        if (!file_exists($qrPath)) {
            throw new \Exception("No se pudo generar el código QR");
        }

        $templateProcessor->setImageValue('IMAGEN', ['src' => $qrPath]);

        try {
            $templateProcessor->saveAs($docxPath);
        } catch (\Exception $e) {
            \Log::error("Error guardando DOCX para " . $cerNroClean . ": " . $e->getMessage());
            $docxPath = storage_path("/sanfrancisco/impresion/" . $cerNroClean . "_" . microtime(true) . ".docx");
            $templateProcessor->saveAs($docxPath);
        }

        if (!file_exists($docxPath)) {
            throw new \Exception("No se pudo guardar el archivo DOCX");
        }

        $word = null;
        $doc = null;

        try {
            $word = new \COM("Word.Application");
            $word->Visible = 0;
            $word->DisplayAlerts = 0;

            $doc = $word->Documents->Open($docxPath);
            $doc->ExportAsFixedFormat($pdfPath, 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
            $doc->Close(false);
            $word->Quit(false);

            unset($doc);
            unset($word);

            if (!file_exists($pdfPath) || filesize($pdfPath) == 0) {
                throw new \Exception("PDF no generado correctamente");
            }

            \Log::info("✓ PDF generado exitosamente: " . basename($pdfPath));

        } catch (\Exception $e) {
            if ($doc) {
                try { $doc->Close(false); } catch (\Exception $ex) {}
            }
            if ($word) {
                try { $word->Quit(false); } catch (\Exception $ex) {}
            }
            unset($doc);
            unset($word);

            \Log::error("Word COM Error for " . $cerNroClean . ": " . $e->getMessage());
            throw new \Exception("Error al convertir a PDF: " . $e->getMessage());
        }
    }

    private function limpiarArchivosTemporales($directorio)
    {
        try {
            $archivos = array_merge(
                glob($directorio . "*.docx"),
                glob($directorio . "*.png")
            );

            $eliminados = 0;
            foreach ($archivos as $archivo) {
                if (@unlink($archivo)) {
                    $eliminados++;
                }
            }

            \Log::info("Archivos temporales eliminados: $eliminados");
        } catch (\Exception $e) {
            \Log::warning("Error limpiando temporales: " . $e->getMessage());
        }
    }

}
