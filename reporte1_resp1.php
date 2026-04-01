<?php

/*reporte.php
 * 
 * Permite seleccionar un rango de fechas, un tipo de reporte
 * Y posteriormente un operador
 * Y le envia los datos a un archivo bat.
 */

//CONFIGURACION
//ETIQUETAS DE REPORTES
$etiq_reporte1 = "Tipo Reporte 1";
$etiq_reporte2 = "Tipo Reporte 2";

$titulo = "Reporte de Fulano de Tal";
$rutadescarga = "c:\\xampp\\htdocs\\cfdipro\\sal.txt";

//Archivo Bat a ejecutar
$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\reporte1.bat";

//Separador de los campos que se le enviaran al .bat
$separador = ",";


//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){
	
	//Envio los datos al .bat
	$parametros = $_POST["fechaini"] . $separador . $_POST["fechafin"] . $separador . $_POST["tiporeporte"] ;
	$linea = $archivo_bat . " " . $parametros;
	exec($linea);

	echo "Params".$linea;
	echo "<br>";

	echo "Se proceso el " . $titulo ."<br>";

	echo "<a href='" . $rutadescarga . "'>" . $titulo . "</a>";

	
	
}
else 
{
	
?>
	<form name="parametros" method="post" action="reporte1.php">
	<input type="hidden" name="procesar" value="1">
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
				<option value="1"><?php echo $etiq_reporte1; ?></option>
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