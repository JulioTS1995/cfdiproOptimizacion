<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); 

require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

require_once('cnx_cfdi2.php');

$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$prefijo = rtrim($prefijobd, "_");

$idFolio = $_GET['id'];

mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");


//systemsettings
$resSQL01= "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
while ($rowSQL01 = mysqli_fetch_array($runSQL01)) {
    	$RazonSocial = $rowSQL01['RazonSocial'];
        $xml_dir= $rowSQL01['xmldir'];
        $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

}

//parametro colores
$parametro_bgc = 921;
$resSQL921 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_bgc";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
	 
while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
	$param= $rowSQL921['id2'];
	$color= $rowSQL921 ['VCHAR'];
}
if (empty($color)) {
    $color = '#a1a1a3'
}

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}

if (empty($color)) {
    $color_letra = '#000000'
}
//estilo de colores

$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

// consulta de los 17 puntos $s = salida $e = entrada


$resSQL02 = "SELECT 
                    XFolio,
                    p_Cabina,
                    p_Cabina1,
                    p_Comentarios,
                    p_Comentarios1,
                    p_Defensa,
                    p_Defensa1,
                    p_EjeAccion,
                    p_EjeAccion1,
                    p_Escape,
                    p_Escape1,
                    p_ExtintoresTrenRodaje,
                    p_ExtintoresTrenRodaje1,
                    p_LlantaRepuesto,
                    p_LlantaRepuesto1,
                    p_Llantas,
                    p_Llantas1,
                    p_Motor,
                    p_Motor1,
                    p_ParedesLaterales,
                    p_ParedesLaterales1,
                    p_ParedFrontal,
                    p_ParedFrontal1,
                    p_Piso,		
                    p_Piso1,	
                    p_Piso_camion,		
                    p_Piso_camion1,			
                    p_Puertas,		
                    p_Puertas1,		
                    p_TanqueAire,			
                    p_TanqueAire1,	
                    p_TanqueCombustible,		
                    p_TanqueCombustible1,
                    p_Techo,		
                    p_Techo1,
                    p_UnidadRefrigeracion,
                    p_UnidadRefrigeracion1			
            FROM {$prefijobd}remisiones WHERE id = $idFolio";    
            $runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);
            while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
                $XFolio= $rowSQL02['XFolio'];
               $s_Cabina= $rowSQL02['p_Cabina'];
               $e_Cabina= $rowSQL02['p_Cabina1'];
               $s_Comentarios= $rowSQL02['p_Comentarios'];  
               $e_Comentarios= $rowSQL02['p_Comentarios1'];
               $s_Defensa= $rowSQL02['p_Defensa'];
                $e_Defensa= $rowSQL02['p_Defensa1'];
                $s_EjeAccion= $rowSQL02['p_EjeAccion'];
                $e_EjeAccion= $rowSQL02['p_EjeAccion1'];
                $s_Escape= $rowSQL02['p_Escape'];
                $e_Escape= $rowSQL02['p_Escape1'];
                $s_ExtintoresTrenRodaje= $rowSQL02['p_ExtintoresTrenRodaje'];
                $e_ExtintoresTrenRodaje= $rowSQL02['p_ExtintoresTrenRodaje1'];
                $s_LlantaRepuesto= $rowSQL02['p_LlantaRepuesto'];
                $e_LlantaRepuesto= $rowSQL02['p_LlantaRepuesto1'];
                $s_Llantas= $rowSQL02['p_Llantas']; 
                $e_Llantas= $rowSQL02['p_Llantas1'];
                $s_Motor= $rowSQL02['p_Motor'];
                $e_Motor= $rowSQL02['p_Motor1'];
                $s_ParedesLaterales= $rowSQL02['p_ParedesLaterales'];
                $e_ParedesLaterales= $rowSQL02['p_ParedesLaterales1'];
                $s_ParedFrontal= $rowSQL02['p_ParedFrontal'];
                $e_ParedFrontal= $rowSQL02['p_ParedFrontal1'];
                $s_Piso= $rowSQL02['p_Piso'];
                $e_Piso= $rowSQL02['p_Piso1'];
                $s_Piso_camion= $rowSQL02['p_Piso_camion'];
                $e_Piso_camion= $rowSQL02['p_Piso_camion1'];
                $s_Puertas= $rowSQL02['p_Puertas'];
                $e_Puertas= $rowSQL02['p_Puertas1'];
                $s_TanqueAire= $rowSQL02['p_TanqueAire'];
                $e_TanqueAire= $rowSQL02['p_TanqueAire1'];
                $s_TanqueCombustible= $rowSQL02['p_TanqueCombustible'];
                $e_TanqueCombustible= $rowSQL02['p_TanqueCombustible1'];
                $s_Techo= $rowSQL02['p_Techo'];
                $e_Techo= $rowSQL02['p_Techo1'];
                $s_UnidadRefrigeracion= $rowSQL02['p_UnidadRefrigeracion'];
                $e_UnidadRefrigeracion= $rowSQL02['p_UnidadRefrigeracion1'];

            }

          

ob_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte 17 puntos</title>
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
                                <td style="text-align:center; font-size:15px; padding:6px; <?php echo $estilo_fondo; ?>  " colspan="2">
                                    <b> 17<br> Puntos</b>
                                </td>
                            </tr> 
                            <tr>
                                <td style="text-align:center; font-size:15px; padding:6px; border:1px solid #000; <?php echo $estilo_fondo; ?>">
                                    <b> Folio</b>
                                </td>
                                <td style="text-align:center; font-size:15px; padding:6px; border:1px solid #000; color: #ff0000;">
                                    <b><?php echo $XFolio; ?></b>
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
                    </tr>
                    

                    <tr>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Conformidad del Operador</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Autorización del Gerente del Area y/o Director</b>
                        </td>
                       
                    </tr>
                    
                </table>
            </div>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" /> 



<main>
    <div style="margin-top:10px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; font-size:12px;">
            <tr>
                <td style="width:50%; text-align:left; font-size:12px;">
                    <strong>Revisión de 17 puntos</strong>
                </td>
                <td style="width:50%; text-align:right; font-size:12px;">
                    <strong>Folio: <?php echo $XFolio; ?></strong>
                </td>
            </tr>
        </table>
    </div>
    <br>

    <div>
        <table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse; font-size:12px; table-layout:fixed;">
            <thead>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th style="width:40%; text-align:center;">Concepto</th>
                    <th style="width:30%; text-align:center;">Salida Estatus</th>
                    <th style="width:30%; text-align:center;">Entrada Estatus</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:left;">01. Cabina</td>
                    <td style="text-align:center;"><?php echo $s_Cabina; ?></td>
                    <td style="text-align:center;"><?php echo $e_Cabina; ?></td>
                </tr>
                               
                <tr>
                    <td style="text-align:left;">02. Defensa</td>
                    <td style="text-align:center;"><?php echo $s_Defensa; ?></td>
                    <td style="text-align:center;"><?php echo $e_Defensa; ?></td>
                </tr>                
                <tr>
                    <td style="text-align:left;">03. Eje de accion</td>
                    <td style="text-align:center;"><?php echo $s_EjeAccion; ?></td>
                    <td style="text-align:center;"><?php echo $e_EjeAccion; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">04. Escape</td>
                    <td style="text-align:center;"><?php echo $s_Escape; ?></td>
                    <td style="text-align:center;"><?php echo $e_Escape; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">05. Extintores y tren de rodaje</td>
                    <td style="text-align:center;"><?php echo $s_ExtintoresTrenRodaje; ?></td>
                    <td style="text-align:center;"><?php echo $e_ExtintoresTrenRodaje; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">06. Llanta de repuesto</td>
                    <td style="text-align:center;"><?php echo $s_LlantaRepuesto; ?></td>
                    <td style="text-align:center;"><?php echo $e_LlantaRepuesto; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">07. Llantas</td>
                    <td style="text-align:center;"><?php echo $s_Llantas; ?></td>
                    <td style="text-align:center;"><?php echo $e_Llantas; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">08. Motor</td>
                    <td style="text-align:center;"><?php echo $s_Motor; ?></td>
                    <td style="text-align:center;"><?php echo $e_Motor; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">09. Paredes laterales</td>
                    <td style="text-align:center;"><?php echo $s_ParedesLaterales; ?></td>
                    <td style="text-align:center;"><?php echo $e_ParedesLaterales; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">10. Pared frontal</td>
                    <td style="text-align:center;"><?php echo $s_ParedFrontal; ?></td>
                    <td style="text-align:center;"><?php echo $e_ParedFrontal; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">11. Piso</td>
                    <td style="text-align:center;"><?php echo $s_Piso; ?></td>
                    <td style="text-align:center;"><?php echo $e_Piso; ?></td>  
                </tr>
                <tr>
                    <td style="text-align:left;">12. Piso del camion</td>
                    <td style="text-align:center;"><?php echo $s_Piso_camion; ?></td>
                    <td style="text-align:center;"><?php echo $e_Piso_camion; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">13. Puertas</td>
                    <td style="text-align:center;"><?php echo $s_Puertas; ?></td>
                    <td style="text-align:center;"><?php echo $e_Puertas; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">14. Tanque de aire</td>
                    <td style="text-align:center;"><?php echo $s_TanqueAire; ?></td>
                    <td style="text-align:center;"><?php echo $e_TanqueAire; ?></td>    
                </tr>
                <tr>
                    <td style="text-align:left;">15. Tanque de combustible</td>
                    <td style="text-align:center;"><?php echo $s_TanqueCombustible; ?></td>
                    <td style="text-align:center;"><?php echo $e_TanqueCombustible; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">16. Techo</td>
                    <td style="text-align:center;"><?php echo $s_Techo; ?></td>
                    <td style="text-align:center;"><?php echo $e_Techo; ?></td>
                </tr>
                <tr>
                    <td style="text-align:left;">17. Unidad de refrigeración</td>
                    <td style="text-align:center;"><?php echo $s_UnidadRefrigeracion; ?></td>
                    <td style="text-align:center;"><?php echo $e_UnidadRefrigeracion; ?></td>
                </tr>

                <tfoot>

                    <tr>
                        <td style="text-align:left; height:150px;">Comentarios</td>
                        <td style="text-align:center; height:150px;"><?php echo $s_Comentarios; ?></td>
                        <td style="text-align:center; height:150px;"><?php echo $e_Comentarios; ?></td>
                    </tr> 
                </tfoot>
            </tbody>
        </table>
    </div>
    <br><br>
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

$nombre_pdf = $XFolio . " - 17 Puntos.pdf";

// 👉 Descargar directamente
$mpdf->Output($nombre_pdf, 'D');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'I');
exit;

//https://tractosoft-c9.com/cfdipro/formato17Puntos.php?prefijodb=tractosoft09_&id=939574

?>