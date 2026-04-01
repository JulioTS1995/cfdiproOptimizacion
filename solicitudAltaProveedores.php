<?php 

ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución


require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi2.php');

mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$prefijobd = $_GET['prefijodb'];
$idProveedor = $_GET['ID'];


$prefijo = rtrim($prefijobd, "_");

$resSQL07 = "SELECT * FROM ".$prefijobd."systemsettings";
	$runSQL07 = mysqli_query($cnx_cfdi2 ,$resSQL07);
	while($rowSQL07 = mysqli_fetch_array($runSQL07)){
		$RazonSocial = $rowSQL07['RazonSocial'];
		
		$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';
	}
$DepartamentoSolicitante = '';
$NombreSolicitante = '';
$FormaPago = '';
$CondicionPago = '';
$CategoriaProveedor = '';


$resSQL01 = "SELECT RazonSocial, 
                    FechaIngreso,
                    cActaConstitutiva,
                    cActaConstitutivaUO,
                    cConstanciaSituacionFiscal,
                    cOpinionCumplimientoVigente,
                    cComprobanteDomicilio,
                    cEvidenciaFotograficaExterior,
                    cEvidenciaFotograficaInterior,
                    cMapaLocalizacion,
                    cReferenciasComerciales,
                    cListadoPreciosAnuales,
                    cDatosBancarios,
                    cContacto,
                    FormaPago,
                    CondicionPago,
                    CategoriaProveedor,
                    NombreSolicitante, 
                    DepartamentoSolicitante
                    FROM {$prefijobd}proveedores WHERE ID = {$idProveedor}";
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
while ($rowSQL01 = mysqli_fetch_array($runSQL01)) {
    $RazonSocialC = $rowSQL01['RazonSocial'];
    $fechaIngreso = $rowSQL01['FechaIngreso']; 
    $cActaConstitutiva = $rowSQL01['cActaConstitutiva'];
    $cActaConstitutivaUO = $rowSQL01['cActaConstitutivaUO'];
    $cConstanciaSituacionFiscal = $rowSQL01['cConstanciaSituacionFiscal'];
    $cOpinionCumplimientoVigente = $rowSQL01['cOpinionCumplimientoVigente'];
    $cComprobanteDomicilio = $rowSQL01['cComprobanteDomicilio'];
    $cEvidenciaFotograficaExterior = $rowSQL01['cEvidenciaFotograficaExterior'];
    $cEvidenciaFotograficaInterior = $rowSQL01['cEvidenciaFotograficaInterior'];
    $cMapaLocalizacion = $rowSQL01['cMapaLocalizacion'];
    $cReferenciasComerciales = $rowSQL01['cReferenciasComerciales'];
    $cListadoPreciosAnuales = $rowSQL01['cListadoPreciosAnuales'];
    $cDatosBancarios = $rowSQL01['cDatosBancarios'];
    $cContacto = $rowSQL01['cContacto']; 
    $FormaPago = $rowSQL01['FormaPago'];
    $CondicionPago = $rowSQL01['CondicionPago'];   
    $CategoriaProveedor = $rowSQL01['CategoriaProveedor'];
    $NombreSolicitante = $rowSQL01['NombreSolicitante'];
    $DepartamentoSolicitante = $rowSQL01['DepartamentoSolicitante'];

}

function activaCheck($check){
    // Marcado → X negro, No marcado → invisible (misma altura)
    $mark = (bool)$check ? ' X ' : ' X ';
    $color = (bool)$check ? 'black' : 'white';

    return '<span style="
        display:inline-block;
        font-size:45px;       
        line-height:1;        
        width:2em;            
        height:1em;           
        border:3px solid #000; 
        text-align:center;
        vertical-align:middle;
        color:'.$color.';
    "> '.$mark.' </span>';
}
ob_start();

var_dump($cActaConstitutiva);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<link rel="stylesheet" href="css/style.css">-->
		
		<style>
			@page {
                margin: 115px 25px;
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
                font-size:15px;

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
		
		<!--
			border-width: 1px;
			border-style: solid;
			border-color: black;
		-->
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
		
		
		
		<title>ALTA PROVEEDORES</title>	
	</head>
	<body>
	
	<htmlpageheader name="myHeader">
			<div style = "padding-top: -20px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:left;  vertical-align:top;">  
							<img src="<?php echo $rutalogo; ?>" style="width: 140px; height: auto;" alt="Logo" /> 
						</td>
						<td style="text-align:center;  font-size: 12px;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<br/>
							<strong>FORMATO DE CHECKLIST DOCUMENTACION ALTA PROVEEDOR</strong><br/>
						</td>						
					</tr>
				</table>
			</div>
			</htmlpageheader>
			<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
    			<htmlpagefooter name="myfooter">
			
			<div style="margin-top: 10px;">
                <table width="100%" cellpadding="6" cellspacing="0"
                        style="border-collapse:collapse; font-size:12px; table-layout:fixed;">
                    <tr>
                    <td colspan="3" style="font-size:13px; text-align:center;">
                        <b>Nota:</b> Para personas Físicas el Acta Constitutiva no aplica (N/A).
                        En caso especial de que no aplique otro documento, poner (N/A) en la casilla de verificación.
                    </td>
                    </tr>

                    <tr>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    </tr>

                    <tr>
                    <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                        <b>Gerente de Área</b>
                    </td>
                    <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                        <b>Autorización Gerente Compras</b>
                    </td>
                    <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                        <b>VO. BO. Director General</b>
                    </td>
                    </tr>
                </table>
            </div>
			
			
			</htmlpagefooter>
		<sethtmlpagefooter name="myfooter" value="on" show-this-page="1" />
        <main>
            <br><br>
        <table border="1" style="margin:0;border-collapse: collapse; font-size:15px;" width="100%">
            <thead>
                <tr colspan = '4'>
                    <td colspan ='1' style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px;">Razon Social</td>
                    <td colspan ='3'><?php echo $RazonSocialC; ?></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Fecha Alta</td>
                    <td style="width:20%;"><?php echo $fechaIngreso; ?></td>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Departamento Solicitante</td>
                    <td style="width:40%;"><?php echo $DepartamentoSolicitante; ?></td>
                </tr>
                <tr>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Condicion de Pago</td>
                    <td style="width:20%;"><?php echo $CondicionPago; ?></td>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Nombre Solicitante</td>
                    <td style="width:40%;"><?php echo $NombreSolicitante; ?></td>
                </tr>
                <tr>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Forma de Pago</td>
                    <td style="width:20%;"><?php echo $FormaPago; ?></td>
                    <td style="text-align:center; font-size:15px; background-color: #a1a1a3; color:#000000;font-size:12px; width:20%;">Categoría de Proveedor</td>
                    <td style="width:40%;"><?php echo $CategoriaProveedor; ?></td>
                </tr>
            </tbody>
        </table>
        <h3 style="text-align:center;background-color: #a1a1a3; color:#000000;font-size:17px; width:100%;">Documentos para Alta</h3>

            <table border="1" width="100%" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-size:10px;">
                <tr>
                    <td width="15%" style="text-align:center;font-size:65px;"><?php  echo activaCheck($cActaConstitutiva); ?></td>
                    <td style="font-size:17px;">Acta Constitutiva.</td>
                    <td width="15%" style="text-align:center;font-size:65px;"><?php  echo activaCheck($cEvidenciaFotograficaInterior); ?></td>
                    <td style="font-size:17px;">Evidencia Fotográfica del establecimiento, local u oficina. Interior: Al menos  3 fotografías  de las instalaciones.</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cActaConstitutivaUO); ?></td>
                    <td style="font-size:17px;">Acta Constitutiva - última ordinaria.</td>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cMapaLocalizacion); ?></td>
                    <td style="font-size:17px;">Mapa de localización del establecimiento, local u oficina; Indicando las calles colindantes (ubicación a travéz de Google Maps en archivo pdf o jpg).</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cConstanciaSituacionFiscal); ?></td>
                    <td style="font-size:17px;">Constancia de Situación Fiscal.</td>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cReferenciasComerciales); ?></td>
                    <td style="font-size:17px;">3 Referencias comerciales no mayores a 3 meses de la fecha alta.</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cOpinionCumplimientoVigente); ?></td>
                    <td style="font-size:17px;">Opinión de Cumplimiento vigente(No mayor a 10 días antes de la fecha alta).</td>
                    <td style="text-align:center;font-size:65px;"><?php  echo activaCheck($cListadoPreciosAnuales); ?></td>
                    <td style="font-size:17px;">Listado de Precios anuales(para Socios comerciales de Productos); o Listado general de Productos.</td>
                </tr>
                <tr>
                    <td width="15%" style="text-align:center;font-size:65px; "><?php  echo activaCheck($cComprobanteDomicilio); ?></td>
                    <td style="font-size:17px;">Comprobante de Domicilio del mes actual del Alta de Proveedor</td>
                    <td width="15%" style="text-align:center;font-size:65px;"><?php  echo activaCheck($cDatosBancarios); ?></td>
                    <td style="font-size:17px;">Datos Bancarios (información)</td>
                </tr>
                <tr>
                    <td width="15%" style="text-align:center;font-size:65px; "><?php  echo activaCheck($cEvidenciaFotograficaExterior); ?></td>
                    <td style="font-size:17px;">Evidencia Fotografica del Establecimiento, local u oficina. Exterior. Fachada.</td>
                    <td width="15%" style="text-align:center;font-size:65px;"><?php  echo activaCheck($cContacto); ?></td>
                    <td style="font-size:17px;">Contacto (Nombre, Número telefónico y correo electrónico).</td>
                </tr>
</table>



        </main>
    </body>
</html>
<?php
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();
// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


$mpdf->SetFont('helvetica');
$mpdf->WriteHTML($html);

$nombre_pdf = $prefijo . " - Solicitud Alta de Proveedor.pdf";

// 👉 Descargar directamente
$mpdf->Output($nombre_pdf, 'I');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'I');
exit;

//https://tractosoft-c8.com/cfdipro/solicitudAltaProveedores.php?prefijodb=LOGISTICAMLA_

?>