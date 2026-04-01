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
$etiq_reporte1 = "Tipo Reporte 1";
$etiq_reporte2 = "Tipo Reporte 2";
$etiq_reporte3 = "Tipo Reporte 3";
$etiq_reporte4 = "Tipo Reporte 4";
$etiq_reporte5 = "Tipo Reporte 5";

$titulo = "Resumenes de Liquidacion";
$rutadescarga = "c:\\xampp\\htdocs\\cfdipro\\sal.txt";

//Archivo Bat a ejecutar
//$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\liquidacionresumen1.bat";
$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\liquidacionresumen";

//Separador de los campos que se le enviaran al .bat
$separador = ",";

$prefijobd = $_REQUEST["prefijo"];

$RutaServer = "ftp://174.142.204.88:21000/xml/";


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
		<td>Fecha Inicial: </td>
		<td><input type="date" name="fechaini" id="fechaini" /></td>
	</tr>
	<tr>
		<td>Fecha Final: </td>
		<td><input type="date" name="fechafin" id="fechafin" /></td>
	</tr>
	<tr>
		<td>Tipo de reporte: </td>
		<td><select name="tiporeporte">
				<option value="1"><?php echo $etiq_reporte1; ?></option>
				<option value="2"><?php echo $etiq_reporte2; ?></option>
				<option value="3"><?php echo $etiq_reporte3; ?></option>
				<option value="4"><?php echo $etiq_reporte4; ?></option>
				<option value="5"><?php echo $etiq_reporte5; ?></option>
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