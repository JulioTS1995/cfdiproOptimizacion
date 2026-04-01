<?php 
ini_set('memory_limit', '2048M');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución


require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi2.php');
require_once('cnx_cfdi2.php');

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

if (!isset($_GET['idoperador']) || empty($_GET['idoperador'])) {
    die("Falta el ID del Operador");
}

$prefijodb = $_GET['prefijodb'];
$idoperador = $_GET['idoperador'];
$prefijo = rtrim($prefijodb, '_');

//consulta systemsettings
$resSQL01= "SELECT * FROM basdb.{$prefijodb}systemsettings";
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
while ($rowSQL01 = mysqli_fetch_array($runSQL01)) {
    	$RazonSocial = $rowSQL01['RazonSocial'];
		$xml_dir= $rowSQL01['xmldir'];
        $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

}

//Consulta Operador
$resSQL02 = "SELECT op.RFC, 
                    op.Operador, 
                    opi.Cantidad, 
                    opi.Descripcion, 
                    opi.NoSerie, 
                    opi.Fecha, 
                    opi.Movimiento,
                    opi.Costo  
            FROM basdb.{$prefijodb}operadores AS op
            INNER JOIN basdb.{$prefijodb}operadoresinventario AS opi ON op.ID = opi.FolioSub_RID 
            WHERE op.ID ={$idoperador}
            ORDER BY opi.Fecha DESC";
$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);  
if (!$runSQL02) {
    die("Error en la consulta SQL: " . mysqli_error($cnx_cfdi2).$resSQL02);
} 
while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
    $nombreOperador = $rowSQL02['Operador'];
    $rfcOperador = $rowSQL02['RFC'];
   
    
} 


//estilo de colores
$parametro_bgc = 921;
$resSQL921 = "SELECT id2, VCHAR, VLOGI FROM basdb.{$prefijodb}parametro Where id2 =$parametro_bgc";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
	 
while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
	$param= $rowSQL921['id2'];
	$color= $rowSQL921 ['VCHAR'];
}

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM basdb.{$prefijodb}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}

$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

$parametro_sin_logo = 931;
$sinLogo = 0;
$resSQL931 = "SELECT id2, VLOGI FROM basdb.{$prefijodb}parametro WHERE id2= {$parametro_sin_logo}";
$runSQL931 = mysqli_query($cnx_cfdi2, $resSQL931);
while ($rowSQL931 = mysqli_fetch_array($runSQL931)) {
	$sinLogos = $rowSQL931['VLOGI'];
}

if ($sinLogos =='1') {
	$rutalogo =  '../cfdipro/imagenes/NOLOGO.jpg';
}


ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<link rel="stylesheet" href="css/style.css">-->
		
		<style>
			@page {
                margin: 150px 25px;
            }
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 150px;
				font-family: Helvetica, sans-serif;
				

            }
			
			footer {
                position: fixed; 
                margin: 120px 18px 160px 18px;
                height: 170px; 
				font-family: Helvetica, sans-serif;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: Helvetica, sans-serif;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
		
		
    <title>Inventario de Operador</title>
</head>
<body>
    <htmlpageheader name="myHeader">
        <div style="margin-top:-10px;">
            <table width="100%" border="0" style="border-collapse:collapse;">
                <tr>
                    <!-- Logo -->
                    <td style="width:25%; text-align:center;">
                        <img src="<?php echo $rutalogo; ?>" width="120" alt="">
                    </td>

                    <!-- Datos empresa -->
                    <td style="width:45%; text-align:center; font-size:11px;">
                        <strong style="font-size:15px;"><?php echo strtoupper($RazonSocial); ?></strong></strong>
                    </td>

                    <!-- Cartela derecha -->
                    <td style="width:30%;">
                        <table width="100%" cellspacing="0" cellpadding="0" style="border:2px solid #000;">
                            <tr>
                                <td style="text-align:center; font-size:15px; padding:6px; <?php echo $estilo_fondo; ?>">
                                    <b> INVENTARIO<br> OPERADOR</b>
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
                        <td style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px; border:1px solid #999;">
                            <b>Firma de Conformidad del Empleado</b>
                        </td>
                        <td style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px; border:1px solid #999;">
                            <b>Firma de Autorización del Gerente del Area y/o Director</b>
                        </td>
                        <td style="text-align:center; <?php echo $estilo_fondo; ?> font-size:12px; border:1px solid #999;">
                            <b>VO. BO. Recursos humanos</b>
                        </td>
                    </tr>
                
                </table>
            </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="myFooter" value="on" />

    <main>
        <br><br>
        <div style="margin-top:10px; font-size:12px;">
            <table width="100%" border="0" style="border-collapse:collapse; font-size:12px;">
                <tr>
                    <td style="width:20%; <?php echo $estilo_fondo; ?>"><strong>Operador:</strong></td>
                    <td style="width:40%; <?php echo $estilo_fondo; ?>"><?php echo $nombreOperador; ?></td>
                    <td style="width:20%; <?php echo $estilo_fondo; ?>"><strong>RFC:</strong></td>
                    <td style="width:20%; <?php echo $estilo_fondo; ?>"><?php echo $rfcOperador; ?></td>
                </tr>
            </table>
        </div>

        <div style="margin-top:10px; font-size:12px;">
            <table width="100%" border="1" style="border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="<?php echo $estilo_fondo; ?>">
                        <th style="padding:6px; text-align:center;">Fecha</th>
                        <th style="padding:6px; text-align:center;">Descripción</th>
                        <th style="padding:6px; text-align:center;">No. Serie</th>
                        <th style="padding:6px; text-align:center;">Cantidad</th>
                        <th style="padding:6px; text-align:center;">Movimiento</th>
                        <th style="padding:6px; text-align:center;">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Re-ejecutar la consulta para obtener todos los registros
                    $runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);  
                    while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
                        $cantidad = $rowSQL02['Cantidad'];
                        $descripcion = $rowSQL02['Descripcion'];
                        $noSerie = $rowSQL02['NoSerie'];
                        $fecha = date("d/m/Y", strtotime($rowSQL02['Fecha']));
                        $movimiento = $rowSQL02['Movimiento'];
                        $costo = $rowSQL02['Costo'];
                    ?>
                    <tr>
                        <td style="padding:6px; text-align:center;"><?php echo $fecha; ?></td>
                        <td style="padding:6px; text-align:left;"><?php echo $descripcion; ?></td>
                        <td style="padding:6px; text-align:center;"><?php echo $noSerie; ?></td>
                        <td style="padding:6px; text-align:center;"><?php echo $cantidad; ?></td>
                        <td style="padding:6px; text-align:center;"><?php echo $movimiento; ?></td>
                        <td style="padding:6px; text-align:center;"><?php echo "$ ". number_format((float)$costo,2); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>


<?php

require_once __DIR__ . '/vendor/autoload.php';
$html = ob_get_clean();
//die($html);
// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


$mpdf->SetFont('Helvetica');
$mpdf->WriteHTML($html);

$nombre_pdf = $prefijo . " - Inventario de ".$nombreOperador.".pdf";


$mpdf->Output($nombre_pdf, 'I');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'D');
exit;



?>