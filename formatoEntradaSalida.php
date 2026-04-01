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

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}
//estilo de colores

$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

//consulta de lentrada y salida

$resSQL02 = "SELECT 
                XFolio,
                EntradaComentario, 
                EntradaKmDolly,
                EntradaKmRemolque1,
                EntradaKmRemolque2,
                EntradaKmTractor,
                EntradaPatio,
                EntradaRemolque1BarrasCuadradas,
                EntradaRemolque1Bateria,
                EntradaRemolque1GataRedonda,
                EntradaRemolque1HoraEngine,
                EntradaRemolque1HorasSwith,
                EntradaRemolque1LitrosAprox,
                EntradaRemolque2BarrasCuadradas,
                EntradaRemolque2Bateria,
                EntradaRemolque2GataRedonda,
                EntradaRemolque2HoraEngine,
                EntradaRemolque2HorasSwith,
                EntradaRemolque2LitrosAprox,
                EntradaRLDireccionalesDerecha,
                EntradaRLDireccionalesIzquierdo,
                EntradaTractorLitrosAprox,
                SalidaCarga,
                SalidaComentario,
                SalidaDestino,
                SalidaKmDolly,
                SalidaKmRemolque1,
                SalidaKmRemolque2,
                SalidaKmTractor,
                SalidaPatio,
                SalidaRemolque1BarrasCuadradas,
                SalidaRemolque1Bateria,
                SalidaRemolque1GataRedonda,
                SalidaRemolque1HoraEngine,
                SalidaRemolque1HorasSwith,
                SalidaRemolque1LitrosAprox,
                SalidaRemolque2BarrasCuadradas,
                SalidaRemolque2Bateria,
                SalidaRemolque2GataRedonda,
                SalidaRemolque2HoraEngine,
                SalidaRemolque2HorasSwith,
                SalidaRemolque2LitrosAprox,
                SalidaRLDireccionalesDerecha,
                SalidaRLDireccionalesIzquierdo,
                SalidaTractorLitrosAprox,
                IncidenteDireccionalDerecha,
                IncidenteDireccionalIzquierdo
              
                FROM {$prefijobd}remisiones WHERE id = $idFolio";
$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);
while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
    $XFolio = $rowSQL02['XFolio'];
    #entrada
    $EntradaComentario = $rowSQL02['EntradaComentario'];
    $EntradaKmDolly = $rowSQL02['EntradaKmDolly'];
    $EntradaKmRemolque1 = $rowSQL02['EntradaKmRemolque1'];
    $EntradaKmRemolque2 = $rowSQL02['EntradaKmRemolque2'];
    $EntradaKmTractor = $rowSQL02['EntradaKmTractor'];
    $EntradaPatio = $rowSQL02['EntradaPatio'];
    $EntradaRemolque1BarrasCuadradas = $rowSQL02['EntradaRemolque1BarrasCuadradas'];
    $EntradaRemolque1Bateria = $rowSQL02['EntradaRemolque1Bateria'];
    $EntradaRemolque1GataRedonda = $rowSQL02['EntradaRemolque1GataRedonda'];
    $EntradaRemolque1HoraEngine = $rowSQL02['EntradaRemolque1HoraEngine'];
    $EntradaRemolque1HorasSwith = $rowSQL02['EntradaRemolque1HorasSwith']; 
    $EntradaRemolque1LitrosAprox = $rowSQL02['EntradaRemolque1LitrosAprox'];
    $EntradaRemolque2BarrasCuadradas = $rowSQL02['EntradaRemolque2BarrasCuadradas'];
    $EntradaRemolque2Bateria = $rowSQL02['EntradaRemolque2Bateria'];
    $EntradaRemolque2GataRedonda = $rowSQL02['EntradaRemolque2GataRedonda'];
    $EntradaRemolque2HoraEngine = $rowSQL02['EntradaRemolque2HoraEngine'];
    $EntradaRemolque2HorasSwith = $rowSQL02['EntradaRemolque2HorasSwith'];
    $EntradaRemolque2LitrosAprox = $rowSQL02['EntradaRemolque2LitrosAprox'];
    $EntradaRLDireccionalesDerecha = $rowSQL02['EntradaRLDireccionalesDerecha'];
    $EntradaRLDireccionalesIzquierdo = $rowSQL02['EntradaRLDireccionalesIzquierdo'];
    $EntradaTractorLitrosAprox = $rowSQL02['EntradaTractorLitrosAprox'];
    #salida
    $SalidaCarga = $rowSQL02['SalidaCarga'];    
    $SalidaComentario = $rowSQL02['SalidaComentario'];
    $SalidaDestino = $rowSQL02['SalidaDestino'];
    $SalidaKmDolly = $rowSQL02['SalidaKmDolly'];
    $SalidaKmRemolque1 = $rowSQL02['SalidaKmRemolque1'];
    $SalidaKmRemolque2 = $rowSQL02['SalidaKmRemolque2'];
    $SalidaKmTractor = $rowSQL02['SalidaKmTractor'];
    $SalidaPatio = $rowSQL02['SalidaPatio'];
    $SalidaRemolque1BarrasCuadradas = $rowSQL02['SalidaRemolque1BarrasCuadradas'];
    $SalidaRemolque1Bateria = $rowSQL02['SalidaRemolque1Bateria'];
    $SalidaRemolque1GataRedonda = $rowSQL02['SalidaRemolque1GataRedonda'];
    $SalidaRemolque1HoraEngine = $rowSQL02['SalidaRemolque1HoraEngine'];
    $SalidaRemolque1HorasSwith = $rowSQL02['SalidaRemolque1HorasSwith'];
    $SalidaRemolque1LitrosAprox = $rowSQL02['SalidaRemolque1LitrosAprox'];
    $SalidaRemolque2BarrasCuadradas = $rowSQL02['SalidaRemolque2BarrasCuadradas'];
    $SalidaRemolque2Bateria = $rowSQL02['SalidaRemolque2Bateria'];
    $SalidaRemolque2GataRedonda = $rowSQL02['SalidaRemolque2GataRedonda'];
    $SalidaRemolque2HoraEngine = $rowSQL02['SalidaRemolque2HoraEngine'];
    $SalidaRemolque2HorasSwith = $rowSQL02['SalidaRemolque2HorasSwith'];
    $SalidaRemolque2LitrosAprox = $rowSQL02['SalidaRemolque2LitrosAprox'];
    $SalidaRLDireccionalesDerecha = $rowSQL02['SalidaRLDireccionalesDerecha'];
    $SalidaRLDireccionalesIzquierdo = $rowSQL02['SalidaRLDireccionalesIzquierdo'];
    $SalidaTractorLitrosAprox = $rowSQL02['SalidaTractorLitrosAprox'];
    #incidente  
    $IncidenteDireccionalDerecha = $rowSQL02['IncidenteDireccionalDerecha'];
    $IncidenteDireccionalIzquierdo = $rowSQL02['IncidenteDireccionalIzquierdo'];
   
  
   


}
// Generar HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
                font-size: 11px;
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
		  
       
        h2 { text-align:center; margin-bottom:20px; }
        table { border-collapse:collapse; width:100%; margin-bottom:20px; }
        th, td {  padding:5px; text-align:center; font-size:11px; }
        
    </style>
</head>
<body>


<htmlpageheader name="myHeader">
        <div style="margin-top:-20px;">
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
                                    <b>Reporte</b><br><b> Salidas - Entradas</b>
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
    <main>       
            <table width="100%" border="1" style="border-collapse:collapse;">
                <tr style="<?php echo $estilo_fondo; ?>"> 
                <th colspan="4">KILOMETRAJES</th>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="2">Datos de Salida</th>
                    <th colspan="2">Datos de Entrada</th>
                </tr>
                <tr>
                <td>Kilómetros Tractor</td>
                    <td><?php echo $EntradaKmTractor; ?></td>
                    <td>Kilómetros Tractor</td>
                    <td><?php echo $SalidaKmTractor; ?></td>
                </tr>
                <tr>
                    <td>Kilómetros Dolly</td>
                    <td><?php echo $EntradaKmDolly; ?></td>
                    <td>Kilómetros Dolly</td>
                    <td><?php echo $SalidaKmDolly; ?></td>
                </tr>
                <tr>
                    <td>Kilómetros Remolque 1</td>
                    <td><?php echo $EntradaKmRemolque1; ?></td>
                    <td>Kilómetros Remolque 1</td>
                    <td><?php echo $SalidaKmRemolque1; ?></td>
                </tr>
                <tr>
                    <td>Kilómetros Remolque 2</td>
                    <td><?php echo $EntradaKmRemolque2; ?></td>
                    <td>Kilómetros Remolque 2</td>
                    <td><?php echo $SalidaKmRemolque2; ?></td>

                </tr>
                
            </table>

            <table width="100%" border="1" style="border-collapse:collapse;">
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Niveles de combustible</th>
                </tr>

                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan= "2">Salida</th>
                    <th colspan= "2">Entrada</th>
                    
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Tractocamion</th>
                </tr>
                <tr>
                    <td>Tractocamion Litros Aprox:</td>
                    <td><?php echo $SalidaTractorLitrosAprox;?></td>
                    <td>Tractocamion Litros Aprox:</td>
                    <td><?php echo $EntradaTractorLitrosAprox;?></td>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Remolque 1</th>
                </tr>
                <tr>
                    <td>Litros Aprox:</td>
                    <td><?php echo $SalidaRemolque1LitrosAprox;?></td>
                    <td>Litros Aprox:</td>
                    <td><?php echo $EntradaRemolque1LitrosAprox;?></td>
                </tr>
                <tr>
                    <td>Horas Swith:</td>
                    <td><?php echo $SalidaRemolque1HorasSwith;?></td>
                    <td>Horas Swith:</td>
                    <td><?php echo $EntradaRemolque1HorasSwith;?></td>
                </tr>
                <tr>
                    <td>Hora Engine:</td>
                    <td><?php echo $SalidaRemolque1HoraEngine;?></td>
                    <td>Hora Engine:</td>
                    <td><?php echo $EntradaRemolque1HoraEngine;?></td>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Remolque 2</th>
                </tr>
                <tr>
                    <td>Litros Aprox:</td>
                    <td><?php echo $SalidaRemolque2LitrosAprox;?></td>
                    <td>Litros Aprox:</td>
                    <td><?php echo $EntradaRemolque2LitrosAprox;?></td>
                </tr>
                <tr>
                    <td>Horas Swith:</td>
                    <td><?php echo $SalidaRemolque2HorasSwith;?></td>
                    <td>Horas Swith:</td>
                    <td><?php echo $EntradaRemolque2HorasSwith;?></td>
                </tr>
                <tr>
                    <td>Hora Engine:</td>
                    <td><?php echo $SalidaRemolque2HoraEngine;?></td>
                    <td>Hora Engine:</td>
                    <td><?php echo $EntradaRemolque2HoraEngine;?></td>
                </tr>
            </table>
            <table width="100%" border="1" style="border-collapse:collapse;">
                <tr style="<?php echo $estilo_fondo; ?>">
                        <th colspan="4">EQUIPO</th>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan= "2">Salida</th>
                    <th colspan= "2">Entrada</th>
                    
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Remolque 1</th>
                </tr>
                <tr>
                    <td>Barras Cuadradas:</td>
                    <td><?php echo $SalidaRemolque1BarrasCuadradas;?></td>
                    <td>Barras Cuadradas:</td>
                    <td><?php echo $EntradaRemolque1BarrasCuadradas;?></td>
                </tr>
                <tr>
                    <td>Gata Redonda:</td>
                    <td><?php echo $SalidaRemolque1GataRedonda;?></td>
                    <td>Gata Redonda:</td>
                    <td><?php echo $EntradaRemolque1GataRedonda;?></td>
                </tr>
                <tr>
                    <td>Bateria:</td>
                    <td><?php echo $SalidaRemolque1Bateria;?></td>
                    <td>Bateria:</td>
                    <td><?php echo $EntradaRemolque1Bateria;?></td>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">Remolque 2</th>
                </tr>
                <tr>
                    <td>Barras cuadradas:</td>
                    <td><?php echo $SalidaRemolque2BarrasCuadradas;?></td>
                    <td>Barras cuadradas:</td>
                    <td><?php echo $EntradaRemolque2BarrasCuadradas;?></td>
                </tr>
                <tr>
                    <td>Gata Redonda:</td>
                    <td><?php echo $SalidaRemolque2GataRedonda;?></td>
                    <td>Gata Redonda:</td>
                    <td><?php echo $EntradaRemolque2GataRedonda;?></td>
                </tr>
                <tr>
                    <td>Bateria:</td>
                    <td><?php echo $SalidaRemolque2Bateria;?></td>
                    <td>Bateria:</td>
                    <td><?php echo $EntradaRemolque2Bateria;?></td>
                </tr>   
            </table>
    <pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />	
            <table width="100%" border="1" style="border-collapse:collapse;">
                <tr style="<?php echo $estilo_fondo; ?>">
                        <th colspan="4">FISICO DEL EQUIPO</th>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan= "2">Salida</th>
                    <th colspan= "2">Entrada</th>
                    
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan= "2">Revision de Luces</th>
                    <th colspan= "2">Revision de Luces</th>
                </tr>
                <tr>
                    <td>Direccionales Izquierdo:</td>
                    <td><?php echo $SalidaRLDireccionalesIzquierdo;?></td>
                    <td>Direccionales Izquierdo:</td>
                    <td><?php echo $EntradaRLDireccionalesIzquierdo;?></td>
                </tr>
                <tr>
                    <td>Direccionales Derecha:</td>
                    <td><?php echo $SalidaRLDireccionalesDerecha;?></td>
                    <td>Direccionales Derecha:</td>
                    <td><?php echo $EntradaRLDireccionalesDerecha;?></td>
                </tr>
                
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan="4">INCIDENTE</th>
                </tr>
                <tr>
                    <td>Direccionales Izquierdo:</td>
                    <td colspan= "3"><?php echo $IncidenteDireccionalIzquierdo;?></td>
                
                
                </tr>
                <tr>
                    <td>Direccionales Derecha:</td>
                    <td colspan= "3"><?php echo $IncidenteDireccionalDerecha;?></td>
                
                
                </tr>
                
            </table>
            <table width="100%" border="1" style="border-collapse:collapse;">
            <tr style="<?php echo $estilo_fondo; ?>">
                        <th colspan="4">COMENTARIOS</th>
                </tr>
                <tr style="<?php echo $estilo_fondo; ?>">
                    <th colspan= "2">Salida</th>
                    <th colspan= "2">Entrada</th>
                    
                </tr>
                <tr>
                    <td colspan= "2"><?php echo $SalidaComentario; ?></td>
                    <td colspan= "2"><?php echo $EntradaComentario; ?></td>
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


// Nombre PDF
$nombre_pdf = "EntradasSalidas_$XFolio.pdf";

// 👉 Descargar directamente
$mpdf->Output($nombre_pdf, 'I');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'I');
exit;

?>