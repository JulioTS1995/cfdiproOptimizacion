<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

require 'phpqrcode/qrlib.php';
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}



require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$idFolio = $_GET["idfolio"];

$prefijo = rtrim($prefijobd, "_");


mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

//multiemisor
$resSQL00= "SELECT * FROM {$prefijobd}systemsettings";
$runSQL00 = mysqli_query($cnx_cfdi2, $resSQL00);
while ($rowSQL00 = mysqli_fetch_array($runSQL00)) {
    if (isset($rowSQL00['MultiEmisor'])) {
        $Multi = $rowSQL00['MultiEmisor'];
    }else {
        $Multi='0';
    }
}


    $resSQLMto = "SELECT 
                    a.OficinaMant_RID, 
                    a.DocumentadorUltimo,  
                    a.Fecha, 
                    a.UnidadMantenimiento_RID, 
                    a.Comentarios, 
                    a.XFolio,
                    /* a.Remision */ 
                    a.CostoManoObra, 
                    a.TotalRegistros, 
                    b.Unidad, 
                    b.Placas 
                    FROM {$prefijobd}mantenimientos as a
                    LEFT JOIN {$prefijobd}unidades as b on a.UnidadMantenimiento_RID = b.ID; 
                WHERE a.ID = '{$idFolio}' ";
                  $runSQLMto = mysqli_query($cnx_cfdi2, $resSQLMto);
                    if ($runSQLMto === false) {
                        die("Error en la consulta: " . mysqli_error($cnx_cfdi2));
                    }
                    if (mysqli_num_rows($runSQLMto) == 0) {
                        die("La consulta no devolvió filas. Revisa idFolio='{$idFolio}' y el prefijo '{$prefijobd}'");
                    }


                    $rowSQLMto = mysqli_fetch_assoc($runSQLMto);
                    if (!$rowSQLMto) {
                        die("No se pudo leer la fila. fetch_assoc devolvió false.");
                    }



                    $mantOficinaID  = isset($rowSQLMto['OficinaMant_RID']) ? $rowSQLMto['OficinaMant_RID'] : '';
                    $documentador   = isset($rowSQLMto['DocumentadorUltimo']) ? $rowSQLMto['DocumentadorUltimo'] : '';
                    $fecha          = isset($rowSQLMto['Fecha']) ? $rowSQLMto['Fecha'] : '';
                    $unidadNombre   = isset($rowSQLMto['Unidad']) ? $rowSQLMto['Unidad'] : '';
                    $unidadPlacas   = isset($rowSQLMto['Placas']) ? $rowSQLMto['Placas'] : '';
                    $comentarios    = isset($rowSQLMto['Comentarios']) ? $rowSQLMto['Comentarios'] : null;
                    $xfolio         = isset($rowSQLMto['XFolio']) ? $rowSQLMto['XFolio'] : null;
                    $costoManoObra  = isset($rowSQLMto['CostoManoObra']) ? $rowSQLMto['CostoManoObra'] : null;
                    $totalRegistros = isset($rowSQLMto['TotalRegistros']) ? $rowSQLMto['TotalRegistros'] : '';
                    #$Remision      = isset($rowSQLMto['Remision']) ? $rowSQLMto['Remision'] : '';

if ($Multi == 1) {
    $resSQL03="SELECT of.ID, em.ID as emiID FROM {$prefijobd}oficinas as of
                    LEFT JOIN {$prefijobd}emisores as em on of.Emisor_RID = em.ID
                    WHERE of.ID = {$mantOficinaID}";
    $runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
    while ($rowSQL03 = mysqli_fetch_array($runSQL03)) {
        $emisorID = $rowSQL03['emiID'];
    }

}
if ($emisorID >=1) {
    $resSQL04 = "SELECT *  FROM {$prefijobd}emisores WHERE ID={$emisorID}";
	//echo $resSQL04;
	$runSQL04 = mysqli_query($cnx_cfdi2, $resSQL04);
	while($rowSQL04 = mysqli_fetch_array($runSQL04)){
		$RazonSocial = $rowSQL04['RazonSocial'];
		$Calle = $rowSQL04['Calle'];
		$NumeroExterior = $rowSQL04['NumeroExterior'];
		$NumeroInterior = $rowSQL04['NumeroInterior'];
		$Colonia = $rowSQL04['Colonia'];
		$CodigoPostal = $rowSQL04['CodigoPostal'];
		$Ciudad = $rowSQL04['Ciudad'];
		$Estado = $rowSQL04['Estado'];
		//$codLocalidad = $rowSQL04['codLocalidad'];
		$Telefono = $rowSQL04['Telefono'];
		$RFC = $rowSQL04['RFC'];
		$Pais = $rowSQL04['Pais'];
		$Municipio = $rowSQL04['Municipio'];
		$xml_dir= $rowSQL04['xmldir'];
		$Regimen = $rowSQL04['Regimen'];
		$PermisoSCT = $rowSQL04['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL04['TipoPermisoSCT'];
		$ruta_logo_multi= $rowSQL04['RutaLogo'];
		$codLocalidad = '';
		
	}
} else {
	$resSQL0 = "SELECT * FROM {$prefijobd}systemsettings";
	$runSQL0 = mysqli_query($cnx_cfdi2 ,$resSQL0);
	while($rowSQL0 = mysqli_fetch_array($runSQL0)){
		$RazonSocial = $rowSQL0['RazonSocial'];
		$Calle = $rowSQL0['Calle'];
		$NumeroExterior = $rowSQL0['NumeroExterior'];
		$NumeroInterior = $rowSQL0['NumeroInterior'];
		$Colonia = $rowSQL0['Colonia'];
		$CodigoPostal = $rowSQL0['CodigoPostal'];
		$Ciudad = $rowSQL0['Ciudad'];
		$Estado = $rowSQL0['Estado'];
		//$codLocalidad = $rowSQL0['codLocalidad'];
		$Telefono = $rowSQL0['Telefono'];
		$RFC = $rowSQL0['RFC'];
		$Pais = $rowSQL0['Pais'];
		$Municipio = $rowSQL0['Municipio'];
		$xml_dir= $rowSQL0['xmldir'];
		$Regimen = $rowSQL0['Regimen'];
		$PermisoSCT = $rowSQL0['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
		$codLocalidad = '';
	}
}

$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

if ($Multi == 1 ) {
   $rutalogo= $ruta_logo_multi;
}

$parametro_bgc = 921;
$resSQL921 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_bgc";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
	 
while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
	$param= $rowSQL921['id2'];
	$color= $rowSQL921 ['VCHAR'];
}

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}
//estilo de colores
$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

               

    


ob_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento: <?php echo $xfolio;?></title>
</head>
		<style>
			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
    		}
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: helvetica;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 0px; 
				font-family: helvetica;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: helvetica;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
		
		
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
<body>
<htmlpageheader name="myHeader">
    <div>
        <table border="0" style="margin:0; border-collapse: collapse; width: 100%;">
            <tr>
                <!-- LOGO IMG -->
                <td style="text-align:center; width:25%;">
                    <img src="<?php echo $rutalogo;?>" width="130px" alt=" "/>
                </td>

                <!-- INFORMACIÓN DE LA EMPRESA -->
                <td style="text-align:center; width:45%; font-size: 11px;">
                    <strong><?php echo $RazonSocial ?></strong> <br/>
                    <?php echo $RFC ?><br/>
                    <?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
                    <strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
                </td>

                <!-- INFORMACIÓN DEL DOCUMENTO -->
                <td style="text-align:right; width:30%; height:50px; font-size:10px; padding:5px;">
                    <table border="0" cellspacing="0" cellpadding="5" width="100%" 
                        style="border-collapse: separate; border: 1px solid rgb(255, 255, 255); border-radius: 15px; overflow: hidden;">
                        
                       
                        <tr>
                            <td colspan="2" style="text-align: center; font-size: 14px; vertical-align: middle;
                                background:#a1a1a3; color:#000; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <b>MANTENIMIENTO</b>
                            </td>
                        </tr>
                         <tr>
                            <td  style="text-align: center; font-size: 10px; padding: 2px; vertical-align: middle;
                                 background:#a1a1a3; color:#000;border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <b>Folio</b>
                            </td>
                            <td  style="text-align: center; font-size: 10px; padding: 2px; vertical-align: middle;
                                background-color: #ffffff; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <?php echo $xfolio; ?>
                            </td>
                        </tr>

                       
                        <tr>
                            <td  style="text-align: center; font-size: 10px; padding: 2px; vertical-align: middle;
                                background:#a1a1a3; color:#000; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <b>Fecha</b>
                            </td>
                             <td  style="text-align: center; font-size: 10px; padding: 2px; vertical-align: middle;
                                background-color: #ffffff; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <?php echo $fecha; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>
<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
           

<htmlpagefooter name="myFooter">
    <div style="margin-top: 10px;">
                <table width="100%" cellpadding="6" cellspacing="0"
                        style="border-collapse:collapse; font-size:12px; table-layout:fixed;">

                 

                    <tr>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    </tr>
                    

                    <tr>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b><?php echo $documentador; ?> <br>Firma de Conformidad del Empleado</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Autorización del Gerente del Area y/o Director</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>VO. BO. Recursos humanos</b>
                        </td>
                    </tr>
                   
                </table>
            </div>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" />


    <main>

        <table border= "1" style= "border-collapse:collapse;" width= "100%">
            <thead>
                <tr>
                    <td colspan='2' style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Unidad</b></td>
                    <td colspan='2' style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Placa</b></td>
                    <!-- <td colspan='2' style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Remision</b></td> -->
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan='2'><?php echo $unidadNombre; ?></td>
                    <td colspan='2'><?php echo $unidadPlacas;?></td>
                    <!-- <td colspan='2'><?php #echo $Remision;?></td> -->
                </tr>
            </tbody>
        </table>
            <br>

        <!-- Mantenimientos Sub Tipo Prioridad Reparacion Seccion Comentarios-->
        <table  style= "border-collapse:collapse;" width= "100%">
            <thead>
                <tr>
                    <td style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Tipo</b></td>
                    <td style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Prioridad</b></td>
                    <td style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Reparacion</b></td>
                    <td style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Seccion</b></td>
                    <td style=" background:#a1a1a3; color:#000; font-size:16px;"><b>Comentarios</b></td>
                </tr>
            </thead>
            <tbody>

                <?php 
                    $resSQLMtoSub = "SELECT 
                                    a.Tipo, 
                                    a.Prioridad, 
                                    b.Reparacion, 
                                    c.Seccion, 
                                    a.Comentarios 
                                    FROM {$prefijobd}mantenimientossub as a
                                    LEFT JOIN {$prefijobd}reparaciones as b on a.Reparacion_RID = b.ID
                                    LEFT JOIN {$prefijobd}reparacionessecciones as c on a.Seccion_RID = c.ID
                                    WHERE a.Mantenimiento_RID = '{$idFolio}' ";
                    $runSQLMtoSub = mysqli_query($cnx_cfdi2, $resSQLMtoSub);
                    while ($rowSQLMtoSub = mysqli_fetch_array($runSQLMtoSub)) {
                        $tipo = isset($rowSQLMtoSub['Tipo']) ? $rowSQLMtoSub['Tipo'] : '';
                        $Prioridad = isset($rowSQLMtoSub['Prioridad']) ? $rowSQLMtoSub['Prioridad'] : '';
                        $Reparacion = isset($rowSQLMtoSub['Reparacion']) ? $rowSQLMtoSub['Reparacion'] : '';
                        $Seccion = isset($rowSQLMtoSub['Seccion']) ? $rowSQLMtoSub['Seccion'] : '';
                        $ComentariosSub = isset($rowSQLMtoSub['Comentarios']) ? $rowSQLMtoSub['Comentarios'] : '';

                        echo "<tr>
                                <td>{$tipo}</td>
                                <td>{$Prioridad}</td>
                                <td>{$Reparacion}</td>
                                <td>{$Seccion}</td>
                                <td>{$ComentariosSub}</td>
                            </tr>";
                    }
                ?>
               <tr>
                    <td colspan='2' style=" border:1px solid #999;background: #a1a1a3; color: #000; font-size:16px;"><b>Costo Mano de Obra</b></td>
                    <td colspan='1'><?php echo '$ '.number_format($costoManoObra, 2); ?></td>
                    <td colspan='1' style=" border:1px solid #999;background: #a1a1a3; color: #000; font-size:16px;"><b>Total Registros</b></td>
                    <td colspan='1'><?php echo $totalRegistros;?></td>
                
               </tr>
            </tbody>
        </table>
 
    

    </main>
    
</body>
</html> 

<?php

require_once __DIR__ . '/vendor/autoload.php';
$html = ob_get_clean();
//die($html);

$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


$mpdf->SetFont('Helvetica');
$mpdf->WriteHTML($html);

$nombre_pdf = "Mantenimiento".$xfolio.".pdf";


$mpdf->Output($nombre_pdf, 'I');


exit;



?>