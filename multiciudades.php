<?php

/*reporte.php
 * 
 * Permite seleccionar un rango de fechas, un tipo de reporte
 * Y posteriormente un operador
 * Y le envia los datos a un archivo bat.
 */

//Fijo el tiempo de ejecucion a 5 minutos
set_time_limit ( 360 );

//CONFIGURACION
//ETIQUETAS DE REPORTES
$etiq_reporte1 = "Orden de carga";
$etiq_reporte2 = "Remision";
$etiq_reporte3 = "Manifiesto";

$pais1 = "MEXICO";
$pais2 = "GUATEMALA";
$pais3 = "EL SALVADOR";
$pais4 = "HONDURAS";
$pais5 = "NICARAGUA";
$pais6 = "COSTA RICA";
$pais7 = "PANAMA";

$titulo = "Orden de Carga, Remision, Manifiesto";
$rutadescarga = "c:\\xampp\\htdocs\\cfdipro\\sal.txt";

//Archivo Bat a ejecutar
$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\ordencarga";

//Separador de los campos que se le enviaran al .bat
$separador = ",";

$prefijobd = $_REQUEST["prefijo"];

$RutaServer = "ftp://174.142.214.246:21000/";


//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){
	
	//Envio los datos al .bat
	$parametros = $prefijobd . $separador. "-p1" . $separador . $_POST["fechaini"] . $separador .  "-p2" . $separador .  $_POST["fechafin"] . $separador .  "-p3" . $separador .  $_POST["tiporeporte"] ;

	if ( $_POST["tiporeporte"] == '1') {
		$linea = $archivo_bat . "1.bat " . $parametros;
	}

	if ( $_POST["tiporeporte"] == '2') {
		$linea = $archivo_bat . "1_2.bat " . $parametros;
	}

	if ( $_POST["tiporeporte"] == '3') {
		$linea = $archivo_bat . "1_3.bat " . $parametros;
	}

	if ( $_POST["tiporeporte"] == '4') {
		$linea = $archivo_bat . "1_4.bat " . $parametros;
	}

	if ( $_POST["tiporeporte"] == '5') {
		$linea = $archivo_bat . "1_5.bat " . $parametros;
	}


//	exec($linea);
	system($linea);
//	passthru($linea);

//	echo "Params".$linea;
//	echo "<br>";

	echo "Se proceso el " . $titulo ."<br>";

	if ( $_POST["tiporeporte"] == '1') {
		$rutadescarga = $RutaServer."liquidacionresumen1.pdf";
	}

	if ( $_POST["tiporeporte"] == '2') {
		$rutadescarga = $RutaServer."liquidacionresumen2.pdf";
	}

	if ( $_POST["tiporeporte"] == '3') {
		$rutadescarga = $RutaServer."liquidacionresumen3.pdf";
	}

	if ( $_POST["tiporeporte"] == '4') {
		$rutadescarga = $RutaServer."liquidacionresumen4.pdf";
	}

	if ( $_POST["tiporeporte"] == '5') {
		$rutadescarga = $RutaServer."liquidacionresumen5.pdf";
	}

	echo "<a href='" . $rutadescarga . "'>" . $titulo . "</a>";
	
}
else 
{
	
	echo "<center>". $titulo ."<br>";
?>
	<form name="parametros" method="post" action="liquidacionresumen1.php">
	<input type="hidden" name="procesar" value="1">
	<input type="hidden" name="prefijo" value="<?php echo $prefijobd; ?>">

	<center>
	<table>
	<tr>
		<td>Reporte: </td>
		<td><select name="tiporeporte">
				<option value="1"><?php echo $etiq_reporte1; ?></option>
				<option value="2"><?php echo $etiq_reporte2; ?></option>
				<option value="3"><?php echo $etiq_reporte3; ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Pa&iacute;s: </td>
		<td><select name="pais">
				<option value="1"><?php echo $pais1; ?></option>
				<option value="2"><?php echo $pais2; ?></option>
				<option value="3"><?php echo $pais3; ?></option>
				<option value="4"><?php echo $pais4; ?></option>
				<option value="5"><?php echo $pais5; ?></option>
				<option value="5"><?php echo $pais6; ?></option>
				<option value="5"><?php echo $pais7; ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" value="Enviar" /></td>
	</tr>
	</table>
	</center>
	</form>
<?php 
}
?>