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
$etiq_reporte1 = "Todos";

$titulo = "Bitacora de Unidad";
$rutadescarga = "c:\\xampp\\htdocs\\cfdipro\\sal.txt";

//Archivo Bat a ejecutar
$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\manttobitacora1.bat";

//Separador de los campos que se le enviaran al .bat
$separador = ",";

$prefijobd = $_REQUEST["prefijo"];

$RutaServer = "ftp://108.163.180.18:21000/xml_ATPrimavera/";


//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){
	
	//Envio los datos al .bat
	$parametros = $prefijobd . $separador. "-p1" . $separador . $_POST["fechaini"] . $separador .  "-p2" . $separador .  $_POST["fechafin"] . $separador .  "-p3" . $separador .  $_POST["tiporeporte"] ;

	if ( $_POST["tiporeporte"] == '5') {
		$linea = $archivo_bat . ' ' . $parametros;
// echo	$linea; 
	}



//	exec($linea);
	system($linea);
//	passthru($linea);

//	echo "Params".$linea;
//	echo "<br>";

	echo "Se proceso el " . $titulo ."<br>";

	if ( $_POST["tiporeporte"] == '5') {
		$rutadescarga = $RutaServer."manttobitacora1.pdf";
	}


	echo "<a href='" . $rutadescarga . "'>" . $titulo . "</a>";
	
}
else 
{
	
	echo "<center>". $titulo ."<br>";
?>
	<form name="parametros" method="post" action="manttobitacora1.php">
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
				<option value="5"><?php echo $etiq_reporte1; ?></option>
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