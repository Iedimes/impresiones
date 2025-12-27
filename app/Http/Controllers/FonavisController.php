<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subsidio;
use App\Localidad;
use App\Departamento;
use App\Grupo;
use App\Persona;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FonavisController extends Controller
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
        $sat = Persona::where('PerCod', $postulante->CerNucCod)->first();
        $titular = Persona::where('PerCod', $postulante->CerPosCod)->first();
        $CerNro = $postulante->CerPosCod;
        $CerNro = substr($CerNro, 0, strpos($CerNro, ' '));

        $nombre = \Auth::user()->username;

        if ($tipo == 1) {
            $ext="CS";
        }else{
            $ext="RC";
        }
//verifico modalidad
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
            case "CV":
                if ($tipo == 1) {
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/cvtemplate.docx'));
                } else {
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/cvrecibo.docx'));
                }
                break;
            case "LP":
                if ($tipo == 1) {
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/cvtemplate.docx'));
                } else {
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('/fonavis/template/cvrecibo.docx'));
                }
                break;
            default:
                //echo "Your favorite color is neither red, blue, nor green!";
                return "No existe platilla";
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

        if (!isset($titular->PerSexo)) {
            $templateProcessor->setValue('CAMPO11', ' el Señor/a '.rtrim($postulante->CerposNom));
        } else {
            if ($titular->PerSexo == 'M') {
                $templateProcessor->setValue('CAMPO11', ' el Señor '.rtrim($postulante->CerposNom));
            } else {
                $templateProcessor->setValue('CAMPO11', ' la Señora '.rtrim($postulante->CerposNom));
            }
        }




        if ($postulante->CerMod === "CV") {
            $templateProcessor->setValue('CAMPO23', '');
        } else {
            $report = Grupo::where('NucCod', $postulante->CerNucCod)
                ->where('GnuCod', $postulante->CerGnuCod)
                ->first();

            $gnunom = $report->GnuNom ?? '';

            // Reemplazar << y >> si existen
            $gnunom = str_replace(['<<', '>>'], ['‹‹', '››'], $gnunom);

            $templateProcessor->setValue('CAMPO23', $gnunom);
        }



        if ((int)$postulante->CerEst == 6) {
            if ((int)$postulante->CerRect2Nr == 0) {
                $templateProcessor->setValue('CAMPO73', 'y rectificado por la Resolución Nº '.$postulante->CerRectNro.' de fecha '.date('d/m/Y', strtotime($postulante->CerRectFec)));
                $templateProcessor->setValue('CAMPO74', '');
            } else {
                $templateProcessor->setValue('CAMPO73', ', rectificados por Resolución Nº '.$postulante->CerRectNro.' de fecha '.
                date('d/m/Y', strtotime($postulante->CerRectFec)).
                ' y Resolución Nº '.
                $postulante->CerRect2Nr.' de fecha '.date('d/m/Y', strtotime($postulante->CerRect2Fe)));
                $templateProcessor->setValue('CAMPO74', '');
            }
        } else {
            $templateProcessor->setValue('CAMPO73', '');
            $templateProcessor->setValue('CAMPO74', '');
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

        $templateProcessor->setValue('CAMPO25', htmlspecialchars($postulante->CerNucNom));
        $templateProcessor->setValue('CAMPO26', $CerNro);
        $cedula = number_format((int)$postulante->CerPosCod,0,'.','.');
        if ($postulante->CerPosCod <= 150000 ) {
            $templateProcessor->setValue('CAMPO12', 'C.I./CARNET Nº '.$cedula);
        } else {
            $templateProcessor->setValue('CAMPO12', 'C.I. Nº '.$cedula);
        }

        $conyugeCI = (int)trim($postulante->CerCoCI);
        $conyugeNom = trim($postulante->CerCoNo);

        if ($conyugeCI == 0 || $conyugeNom == "" || $conyugeNom == "0") {
            if (!isset($titular->PerSexo)) {
                $templateProcessor->setValue('CAMPO33', ' ha sido beneficiado/a');
            } else {
                if ($titular->PerSexo == 'M') {
                    $templateProcessor->setValue('CAMPO33', ' ha sido beneficiado');
                } else {
                    $templateProcessor->setValue('CAMPO33', ' ha sido beneficiada');
                }
            }
        } else {
            if ($conyugeCI <= 150000) {
                $templateProcessor->setValue('CAMPO33', 'y su cónyuge (pareja) '.rtrim($postulante->CerCoNo).', con C.I./CARNET Nº '.number_format($conyugeCI,0,'.','.').', han sido beneficiados');
            } else {
                $templateProcessor->setValue('CAMPO33', "y su cónyuge (pareja) ".rtrim($postulante->CerCoNo).', con C.I. Nº '.number_format($conyugeCI,0,'.','.').', han sido beneficiados');
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
        if ($postulante->CerCiuId == 0) {

        } else {
            $templateProcessor->setValue('CAMPO42', $ciudad->CiuNom);
        }

        $depto = Departamento::find($postulante->CerDptoId);
        if ($postulante->CerDptoId == 0) {

        } else {
            $templateProcessor->setValue('CAMPO43', $depto->DptoNom);
        }



        if ($postulante->CerIndert == '') {
            $templateProcessor->setValue('CAMPO55', '1061/15');
        } else {
            $templateProcessor->setValue('CAMPO55', $postulante->CerIndert);
        }

        $templateProcessor->setValue('CAMPO50', $postulante->CerIdent);
        $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
        $templateProcessor->setValue('CAMPO56', date('d/m/Y'));


       // Construir la URL completa
        $num = env('APP_URL').'/verificacion/'.$postulante->CerPin;

        // Generar el código QR
        QrCode::format('png')->size(110)->margin(0)->generate($num, storage_path("/fonavis/impresion/".$CerNro.".png"));

        // Insertar la imagen del código QR en el documento
        $templateProcessor->setImageValue('IMAGEN', array(
            'src' => storage_path("/fonavis/impresion/".$CerNro.".png"),
        ));

        // Guardar el documento
        $templateProcessor->saveAs(storage_path("/fonavis/impresion/".$CerNro.".docx"));

        // Inicializar Word
        $word = new \COM("Word.Application") or die ("Could not initialise Object.");
        $word->Visible = 0; // Cambiar a 1 para ver la ventana de Word
        $word->DisplayAlerts = 0;

        // Abrir el documento
        $word->Documents->Open(storage_path("/fonavis/impresion/".$CerNro.".docx"));

        // Exportar como PDF
        $word->ActiveDocument->ExportAsFixedFormat(storage_path("/fonavis/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf"), 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);

        // Cerrar Word
        $word->Quit(false);
        unset($word);

        if ($tipo == 99) {
            return response()->download(storage_path("/fonavis/impresion/".$CerNro.".docx"));
        }else{
            return response()->download(storage_path("/fonavis/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf"));
        }



    }

    public function generateMasivo(Request $request)
{
    set_time_limit(0);
    ini_set('memory_limit', '512M'); // Aumentar memoria

    $s = $request->input('dateid');
    $dt = new \DateTime($s);
    $date = $dt->format('Y-d-m H:i:s.v');

    // CAMBIO CRÍTICO: Usar get() en lugar de paginate() para obtener TODOS los registros
    $projects = Subsidio::where('CerProg', $request->input('progid'))
        ->where('CerResNro', '=', $request->input('resid'))
        ->where('CerFeRe', '=', $date)
        ->orderBy(DB::raw('SUBSTRING(CerNro, 4, 15)'), 'asc')
        ->get(); // CAMBIO AQUÍ

    $time = time();
    $name = 'FONAVIS' . '-' . $request->input('resid') . '-' . $request->input('dateid') . '-' . $time . '.zip';

    $zip = new \ZipArchive;
    $ext = ($request->input('idtipo') == 1) ? "CS" : "RC";

    // Contadores para logging
    $procesados = 0;
    $errores = 0;
    $erroresDetalle = [];

    try {
        $zipPath = storage_path("/fonavis/impresion/" . $name);

        if (!is_writable(dirname($zipPath))) {
            throw new \Exception('El directorio no es escribible.');
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

            \Log::info("Iniciando generación de " . count($projects) . " certificados");

            foreach ($projects as $index => $value) {
                try {
                    \Log::info("Procesando certificado " . ($index + 1) . "/" . count($projects) . ": " . $value->CerNro);

                    // Generar el documento
                    $this->generateDocxMulti($value->CerNro, $request->input('idtipo'));

                    $fileName = $ext . substr(rtrim($value->CerNro), 5) . '_' . rtrim($value->CerPosCod) . ".pdf";
                    $filePath = storage_path("/fonavis/impresion/" . $fileName);

                    // Verificar que el archivo existe y tiene contenido
                    if (file_exists($filePath) && filesize($filePath) > 0) {
                        $zip->addFile($filePath, $fileName);
                        $procesados++;
                        \Log::info("✓ Certificado agregado al ZIP: " . $fileName);
                    } else {
                        $errores++;
                        $erroresDetalle[] = "Archivo no generado o vacío: " . $value->CerNro;
                        \Log::warning("✗ Archivo no existe o está vacío: " . $fileName);
                    }

                    // Liberar memoria cada 10 documentos
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

            // Si no se procesó ningún archivo, devolver error
            if ($procesados == 0) {
                return response()->json([
                    'error' => 'No se pudo generar ningún certificado',
                    'detalles' => $erroresDetalle
                ], 500);
            }

            // Limpiar archivos temporales antes de descargar
            // Esperar que Word libere todos los archivos
            sleep(2);
            $this->limpiarArchivosTemporales($directorio = storage_path("/fonavis/impresion/"));

            return response()->download($zipPath);

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

    $sat = Persona::where('PerCod', $postulante->CerNucCod)->first();
    $titular = Persona::where('PerCod', $postulante->CerPosCod)->first();
    $CerNro = $postulante->CerPosCod;
    $CerNro = substr($CerNro, 0, strpos($CerNro, ' ') ?: strlen($CerNro));

    $nombre = \Auth::user()->username;
    $ext = ($tipo == 1) ? "CS" : "RC";

    // Definir rutas de archivos
    $docxPath = storage_path("/fonavis/impresion/".$CerNro.".docx");
    $qrPath = storage_path("/fonavis/impresion/".$CerNro.".png");
    $pdfPath = storage_path("/fonavis/impresion/".$ext.substr(rtrim($postulante->CerNro), 5).'_'.$CerNro.".pdf");

    // IMPORTANTE: Eliminar archivos previos si existen para evitar conflictos
    if (file_exists($docxPath)) {
        // Intentar eliminar, si falla, usar nombre alternativo
        $intentos = 0;
        while (file_exists($docxPath) && $intentos < 3) {
            if (@unlink($docxPath)) {
                break;
            }
            // Si no se puede eliminar, usar nombre con timestamp
            $docxPath = storage_path("/fonavis/impresion/".$CerNro."_".time().".docx");
            $intentos++;
        }
    }

    if (file_exists($qrPath)) {
        @unlink($qrPath);
    }

    // Seleccionar plantilla
    $templates = [
        'CH' => ($tipo == 1) ? 'chtemplate.docx' : 'chrecibo.docx',
        'TI' => ($tipo == 1) ? 'titemplate.docx' : 'tirecibo.docx',
        'LP' => ($tipo == 1) ? 'cvtemplate.docx' : 'cvrecibo.docx',
        'CV' => ($tipo == 1) ? 'cvtemplate.docx' : 'cvrecibo.docx',
    ];

    if (!isset($templates[$postulante->CerMod])) {
        throw new \Exception("No existe plantilla para modalidad: " . $postulante->CerMod);
    }

    $templatePath = storage_path('/fonavis/template/' . $templates[$postulante->CerMod]);

    if (!file_exists($templatePath)) {
        throw new \Exception("Plantilla no encontrada: " . $templatePath);
    }

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

    // Generar PIN si no existe
    if ($postulante->CerPin == null || $postulante->CerPin == 0) {
        $num = $this->generateCodigo();
        $check = Subsidio::where('CerPin', $num)->first();

        while ($check) {
            $num = $this->generateCodigo();
            $check = Subsidio::where('CerPin', $num)->first();
        }

        $postulante->CerPin = $num;
        $postulante->CerUsuImp = substr($nombre, 0, 9);
        $postulante->save();
    } else {
        $num = $postulante->CerPin;
    }

    // Procesar todos los campos del template (tu lógica existente)
    // [... todo tu código de setValue se mantiene igual ...]

    // CAMPO11 - Género
    if (!isset($titular->PerSexo)) {
        $templateProcessor->setValue('CAMPO11', ' el Señor/a '.rtrim($postulante->CerposNom));
    } else {
        $templateProcessor->setValue('CAMPO11',
            ($titular->PerSexo == 'M') ? ' el Señor '.rtrim($postulante->CerposNom) : ' la Señora '.rtrim($postulante->CerposNom)
        );
    }

    // CAMPO23 - Grupo
    if ($postulante->CerMod === "CV") {
        $templateProcessor->setValue('CAMPO23', '');
    } else {
        $report = Grupo::where('NucCod', $postulante->CerNucCod)
            ->where('GnuCod', $postulante->CerGnuCod)
            ->first();
        $gnunom = $report->GnuNom ?? '';
        $gnunom = str_replace(['<<', '>>'], ['‹‹', '››'], $gnunom);
        $templateProcessor->setValue('CAMPO23', $gnunom);
    }

    // CAMPO73/74 - Rectificaciones
    if ((int)$postulante->CerEst == 6) {
        if ((int)$postulante->CerRect2Nr == 0) {
            $templateProcessor->setValue('CAMPO73', 'y rectificado por la Resolución Nº '.$postulante->CerRectNro.' de fecha '.date('d/m/Y', strtotime($postulante->CerRectFec)));
            $templateProcessor->setValue('CAMPO74', '');
        } else {
            $templateProcessor->setValue('CAMPO73', ', rectificados por Resolución Nº '.$postulante->CerRectNro.' de fecha '.
            date('d/m/Y', strtotime($postulante->CerRectFec)).' y Resolución Nº '.
            $postulante->CerRect2Nr.' de fecha '.date('d/m/Y', strtotime($postulante->CerRect2Fe)));
            $templateProcessor->setValue('CAMPO74', '');
        }
    } else {
        $templateProcessor->setValue('CAMPO73', '');
        $templateProcessor->setValue('CAMPO74', '');
    }

    // CAMPO31 - Plazo
    $plazos = [6 => '6 meses (seis)', 9 => '9 meses (nueve)', 18 => '18 meses (diez y ocho)'];
    $templateProcessor->setValue('CAMPO31', $plazos[$postulante->CerPlzOrig] ?? '');

    // Campos varios
    $templateProcessor->setValue('CAMPO32', date('d/m/Y', strtotime($postulante->CerVig)));
    $templateProcessor->setValue('CAMPO20', $postulante->CerNivCod);
    $templateProcessor->setValue('CAMPO21', number_format($postulante->CerMonUSM, 2, ',', '.'));

    $meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    $templateProcessor->setValue('CAMPO27', 'Asunción, '.date('d', strtotime($postulante->CerFeRe)).' de '.
        $meses[date('m', strtotime($postulante->CerFeRe))-1].' de '.date('Y', strtotime($postulante->CerFeRe)));

    $templateProcessor->setValue('CAMPO57', empty($postulante->CerObsSub) ? '' : 'Observación: '.$postulante->CerObsSub);
    $templateProcessor->setValue('CAMPO35', ($postulante->CerEst == 6) ? 'Certificado Rectificado, impreso en fecha '.date('d/m/Y') : '');
    $templateProcessor->setValue('CAMPO17', rtrim($postulante->CerLla).'/'.rtrim($postulante->CerAno));
    $templateProcessor->setValue('CAMPO18', $postulante->CerReLla);
    $templateProcessor->setValue('CAMPO30', date('d/m/Y', strtotime($postulante->CerReLFe)));
    $templateProcessor->setValue('CAMPO25', htmlspecialchars($postulante->CerNucNom));
    $templateProcessor->setValue('CAMPO26', $CerNro);

    $cedula = number_format((int)$postulante->CerPosCod, 0, '.', '.');
    $templateProcessor->setValue('CAMPO12',
        ($postulante->CerPosCod <= 150000) ? 'C.I./CARNET Nº '.$cedula : 'C.I. Nº '.$cedula
    );

    // CAMPO33 - Cónyuge
    $conyugeCI = (int)trim($postulante->CerCoCI);
    $conyugeNom = trim($postulante->CerCoNo);

    if ($conyugeCI == 0 || $conyugeNom == "" || $conyugeNom == "0") {
        if (!isset($titular->PerSexo)) {
            $templateProcessor->setValue('CAMPO33', ' ha sido beneficiado/a');
        } else {
            $templateProcessor->setValue('CAMPO33',
                ($titular->PerSexo == 'M') ? ' ha sido beneficiado' : ' ha sido beneficiada'
            );
        }
    } else {
        if ($conyugeCI <= 150000) {
            $templateProcessor->setValue('CAMPO33', 'y su cónyuge (pareja) '.rtrim($postulante->CerCoNo).', con C.I./CARNET Nº '.number_format($conyugeCI,0,'.','.').', han sido beneficiados');
        } else {
            $templateProcessor->setValue('CAMPO33', "y su cónyuge (pareja) ".rtrim($postulante->CerCoNo).
                ', con C.I. Nº '.number_format($conyugeCI, 0, '.', '.').', han sido beneficiados');
        }
    }

    $templateProcessor->setValue('CAMPO14', $postulante->CerResNro);
    $templateProcessor->setValue('CAMPO22', number_format($postulante->CerUsm, 2, ',', '.'));
    $templateProcessor->setValue('CAMPO53', empty($postulante->CerTipViv) ? 'VR-2D' : $postulante->CerTipViv);
    $templateProcessor->setValue('CAMPO54', ($postulante->CerSupViv <= 0) ? '43.50' : $postulante->CerSupViv);

    $ciudad = Localidad::find($postulante->CerCiuId);
    $templateProcessor->setValue('CAMPO42', $ciudad->CiuNom ?? '');

    $depto = Departamento::find($postulante->CerDptoId);
    $templateProcessor->setValue('CAMPO43', $depto->DptoNom ?? '');

    $templateProcessor->setValue('CAMPO55', empty($postulante->CerIndert) ? '1061/15' : $postulante->CerIndert);
    $templateProcessor->setValue('CAMPO50', $postulante->CerIdent);
    $templateProcessor->setValue('CAMPO10', date('d/m/Y', strtotime($postulante->CerFeRe)));
    $templateProcessor->setValue('CAMPO56', date('d/m/Y'));

    // Generar QR
    $num = env('APP_URL').'/verificacion/'.$postulante->CerPin;
    \QrCode::format('png')->size(110)->margin(0)->generate($num, $qrPath);

    if (!file_exists($qrPath)) {
        throw new \Exception("No se pudo generar el código QR");
    }

    $templateProcessor->setImageValue('IMAGEN', ['src' => $qrPath]);

    // Guardar DOCX
    try {
        $templateProcessor->saveAs($docxPath);
    } catch (\Exception $e) {
        \Log::error("Error guardando DOCX para ".$CerNro.": " . $e->getMessage());
        // Si falla por archivo bloqueado, intentar con nombre alternativo
        $docxPath = storage_path("/fonavis/impresion/".$CerNro."_".microtime(true).".docx");
        $templateProcessor->saveAs($docxPath);
    }

    if (!file_exists($docxPath)) {
        throw new \Exception("No se pudo guardar el archivo DOCX");
    }

    // Convertir a PDF con Word COM
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

        // Importante: liberar recursos COM
        unset($doc);
        unset($word);

        // Verificar que se creó el PDF
        if (!file_exists($pdfPath) || filesize($pdfPath) == 0) {
            throw new \Exception("PDF no generado correctamente");
        }

        \Log::info("✓ PDF generado exitosamente: " . basename($pdfPath));

    } catch (\Exception $e) {
        // Asegurar que Word se cierra incluso si hay error
        if ($doc) {
            try { $doc->Close(false); } catch (\Exception $ex) {}
        }
        if ($word) {
            try { $word->Quit(false); } catch (\Exception $ex) {}
        }
        unset($doc);
        unset($word);

        \Log::error("Word COM Error for ".$CerNro.": " . $e->getMessage());
        throw new \Exception("Error al convertir a PDF: " . $e->getMessage());
    }
}

/**
 * Limpiar archivos temporales del directorio de impresión
 * Llamar después de generar el ZIP masivo
 */
public function limpiarTemporales()
{
    $directorio = storage_path("/fonavis/impresion/");
    $archivosEliminados = 0;
    $errores = 0;

    try {
        // Obtener archivos .docx y .png
        $archivos = array_merge(
            glob($directorio . "*.docx"),
            glob($directorio . "*.png")
        );

        foreach ($archivos as $archivo) {
            // Esperar un momento por si está bloqueado
            sleep(1);

            if (@unlink($archivo)) {
                $archivosEliminados++;
            } else {
                $errores++;
                \Log::warning("No se pudo eliminar: " . basename($archivo));
            }
        }

        \Log::info("Limpieza completada - Eliminados: $archivosEliminados, Errores: $errores");

        return response()->json([
            'success' => true,
            'eliminados' => $archivosEliminados,
            'errores' => $errores
        ]);

    } catch (\Exception $e) {
        \Log::error("Error en limpieza: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * Limpiar archivos temporales (versión privada para uso interno)
 */
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
