<?php

/*reporte.php
 * 
 * Permite seleccionar un rango de fechas, un tipo de reporte
 * Y posteriormente un operador
 * Y le envia los datos a un archivo bat.
 */

//Fijo el tiempo de ejecucion a 5 minutos
set_time_limit ( 360 );
include("cnx_cfdi.php");

//CONFIGURACION
//ETIQUETAS DE REPORTES
$etiq_reporte1 = "Todos";

if (!isset($_REQUEST['prefijo']) || empty($_REQUEST['prefijo'])) {
    die("Falta el prefijo de la base de datos");
}
$prefijobd = $_REQUEST["prefijo"];

$titulo = "Reporte Remisiones Fechas";
$rutadescarga = "ftp://108.163.180.18:21000/xml_Mar/ReporteRemisionesFechas.pdf";

//Archivo Bat a ejecutar
$archivo_bat = "C:\\xampp\\htdocs\\cfdipro\\remision_pdffechas.bat";

//Separador de los campos que se le enviaran al .bat
$separador = ",";

$prefijobd = $_REQUEST["prefijo"];

$RutaServer = "ftp://108.163.180.18:21000/xml_ATPrimavera/";


//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){
	
	mysql_select_db($database_cfdi, $cnx_cfdi);
	$qryreporte = "SELECT VCHAR FROM " . $prefijobd . "parametro WHERE id2 = 152";
	$resultqryreporte = mysql_query($qryreporte, $cnx_cfdi);

	if (!$resultqryreporte) {
    		die('Nombre de reporte no encontrado: ' . mysql_error());
	}

	$rowreporte = mysql_fetch_row($resultqryreporte);
	$nombrereporte = $rowreporte[0];

	$qryreporte = "SELECT count(XFolio) as N FROM " . $prefijobd . "remisionesfechas WHERE DATE(HoraSolicitud) BETWEEN '".$_POST["fechaini"]."' AND '".$_POST["fechafin"]."'";
	$resultqryreporte = mysql_query($qryreporte, $cnx_cfdi);
	$rowreg = mysql_fetch_assoc($resultqryreporte);

	if (isset($rowreg['N'])) {
		if ($rowreg['N'] > 0)
		{
			if(!Empty($_POST["fechaini"]) && !Empty($_POST["fechafin"]))
			{
				//$valor="\"'".$_POST["fechaini"]."' AND '".$_POST["fechafin"]."'\"";
				//unlink($rutadescarga);
	
				exec($archivo_bat." ".$_POST["fechaini"]." ".$_POST["fechafin"]." ".$nombrereporte." ".$prefijobd);

				//echo " \"".$valor."\"";
				echo "Se proceso el " . $titulo ."<br>";

				echo "<a href='" . $rutadescarga . "'>" . $titulo . "</a>";
			}
			Else
				echo "Fechas Invalidas";
		}
		else
			echo "No Hay Registros";
	}
	else
		echo "No Hay Registros";
	
}
else 
{
	
	echo "<center>". $titulo ."<br>";
?>
	<form name="parametros" method="post" action="remision_pdffechas.php">
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
		<td colspan="2" align="center"><input type="submit" value="Enviar" /></td>
	</tr>
	</table>
	</center>
	</form>
<?php 
}
?>