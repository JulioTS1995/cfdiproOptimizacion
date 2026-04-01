<?php

/*ces_remision.php
 * 
 * Sirve para imprimir las distintas remisiones de CES dependiendo del pais
 * 
 */

//Fijo el tiempo de ejecucion a 5 minutos
set_time_limit ( 360 );

//CONFIGURACION
$debug=0;
//ETIQUETAS DE REPORTES
$etiq_reporte = '21';

$pais1 = "MEXICO";
$pais2 = "GUATEMALA";
$pais3 = "EL SALVADOR";
$pais4 = "HONDURAS";
$pais5 = "NICARAGUA";
$pais6 = "COSTA RICA";
$pais7 = "PANAMA";
$pais8 = "CD. HIDALGO";
$pais9 = "CHINANDEGA";

$titulo = "Remision";
$rutadescarga = "c:\\xampp\\htdocs\\cfdipro\\sal.txt";

//Archivo Bat a ejecutar
$archivo_bat = "ces_remision.bat";

//Separador de los campos que se le enviaran al .bat
$separador = ",";

$prefijobd = $_REQUEST["prefijo"];
$id = $_REQUEST["id"];

$RutaServer = "ftp://108.163.180.18:21000/xml_CES/";


//Reviso si vengo ya con los datos o no.
if (isset($_POST["procesar"]) && $_POST["procesar"]==1){

//Configuro el valor que voy a buscar de la tabla de parametros
//-------------------------MODIFICAR--------------------------------//
$idnombrereporte = $etiq_reporte . $_POST["pais"];

//Realizo la conexion a la base de datos
include("cnx_cfdi.php");

//Selecciono la base de datos
mysql_select_db($database_cfdi, $cnx_cfdi);

//Obtengo el nombre del reporte que se le enviara al bat
$qryreporte = "SELECT VCHAR FROM " . $prefijobd . "parametro WHERE id2 = " . $idnombrereporte;

if ($debug == 1) {
	echo $qryreporte;
}

$resultqryreporte = mysql_query($qryreporte, $cnx_cfdi);

if (!$resultqryreporte) {
    die('Nombre de reporte no encontrado: ' . mysql_error());
}

$rowreporte = mysql_fetch_row($resultqryreporte);

$nombrereporte = $rowreporte[0];

if ($debug == 1) {
	echo $nombrereporte;
}

//Obtengo el folio del reporte para el nombre
$qryfolio = "SELECT remisiones.folio, oficina.Serie FROM " . $prefijobd . "remisiones as remisiones, " . $prefijobd . "oficinas as oficina WHERE oficina.ID=remisiones.Oficina_RID AND remisiones.id = " . $_REQUEST["id"];

if ($debug == 1) {
	echo $qryfolio;
}

$resultqryfolio = mysql_query($qryfolio, $cnx_cfdi);

if (!$resultqryfolio) {
    die('Folio no encontrado: ' . mysql_error());
}

$rowfolio = mysql_fetch_row($resultqryfolio);

$folio = $rowfolio[0];
$oficina = $rowfolio[1];

if ($debug == 1) {
	echo $folio;
}
	
//Hago la linea del bat:
$linea = exec("C:\\xampp\\htdocs\\cfdipro\\".$archivo_bat." ".$_REQUEST["id"]." ".$nombrereporte." ".$prefijobd);

$rutadescarga = $RutaServer.$oficina .$folio. ".pdf";

echo "<a href='" . $rutadescarga . "'>" . $titulo . "</a>";
	
}
else 
{
	
	echo "<center>". $titulo ."<br>";
?>
	<form name="parametros" method="post" action="ces_remision.php">
	<input type="hidden" name="procesar" value="1">
	<input type="hidden" name="prefijo" value="<?php echo $prefijobd; ?>">
	<input type="hidden" name="id" value="<?php echo $id; ?>">

	<center>
	<table>
	<tr>
		<td>Pa&iacute;s: </td>
		<td><select name="pais">
				<option value="1"><?php echo $pais1; ?></option>
				<option value="2"><?php echo $pais2; ?></option>
				<option value="3"><?php echo $pais3; ?></option>
				<option value="4"><?php echo $pais4; ?></option>
				<option value="5"><?php echo $pais5; ?></option>
				<option value="6"><?php echo $pais6; ?></option>
				<option value="7"><?php echo $pais7; ?></option>
				<option value="8"><?php echo $pais8; ?></option>
				<option value="9"><?php echo $pais9; ?></option>
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