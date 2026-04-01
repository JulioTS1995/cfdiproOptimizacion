<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(500);
ini_set('memory_limit', '512M');
ob_start();

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query("SET NAMES 'utf8'");

$fecha_inicio = $_POST['fechai'];
$fecha_fin = $_POST['fechaf'];
$id_operador = $_POST['operador'];
$id_unidad = $_POST['unidad'];
$prefijodb = $_POST['prefijodb'];


$prefdb = trim($prefijodb, '_');


$zipper = new ZipArchive;
$nomZip="liquidaciones_download_01.zip";
$zipper->open("liquidaciones_download_01.zip",ZipArchive::CREATE);

if (!empty($id_operador)) {
    $cntQueryOp = "AND OperadorLiqui_RID = " . $id_cliente;
} else {
    $cntQueryOp = "";
}

if (!empty($id_unidad)) {
	$cntQueryUn = "AND UnidadLiqui_RID = " . $id_unidad;
} else {
	$cntQueryUn = "";
}

$cntQuery = $cntQueryOp . " " . $cntQueryUn;



$sql_01="SELECT 
			* 
		FROM {$prefijodb}liquidaciones 
		WHERE Date(Fecha) Between '{$fecha_inicio} 00:00:00' 
		And '{$fecha_fin} 23:59:59' 
		 {$cntQuery} ORDER BY XFolio DESC";
//die ($sql_01);
$res_01=mysqli_query($cnx_cfdi2, $sql_01);
$num_row = mysqli_num_rows($res_01);


if ($num_row > 0) {

	

		$fecha_inicio = $_POST["fechai"];
		$fecha_fin = $_POST["fechaf"];
		$carpetaInterna = "Liquidaciones_" . $prefdb ;
		$sql_03="SELECT xmldir FROM ".$prefijodb."SYSTEMSETTINGS";
		//echo "<br>".$sql_03;
		$res_03=mysqli_query($cnx_cfdi2, $sql_03);
		while ($fila_03 = mysqli_fetch_array($res_03)){
			$xmlDir = $fila_03['xmldir'];
		}

	while ($fila_01 = mysqli_fetch_array($res_01)){
		$folio = $fila_01['XFolio'];
		
		
		$file_pdf = "C:/xampp/htdocs".$xmlDir."/" .$prefijodb . $folio . ".pdf";
		/* echo "<br>".$file_pdf;
		die($file_pdf); */
		if (file_exists($file_pdf)) {
			$zipper->addFile($file_pdf, $carpetaInterna . "/" . basename($file_pdf));
		}

	}

	$zipper->close();

	$nom_file = "Liquidaciones_".$fecha_inicio."_".$fecha_fin."_".date('h:i:s')."_".date('d-m-Y').".zip";
	header("Content-disposition: attachment; filename=$nom_file");
	header("Content-type: MIME");
	header("Cache-Control: post-check=0, pre-check=0",false);
	readfile($nomZip);

	unlink($nomZip);



} else {
	echo "<h2>No existen archivos para la consulta</h2>";
}

ob_flush(); 

?>
