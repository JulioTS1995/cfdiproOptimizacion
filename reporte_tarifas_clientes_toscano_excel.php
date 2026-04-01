<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Tarifas_Clientes_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

//mysqli_query($conexion,"SET NAMES 'utf8'");

$prefijobd = $_GET["prefijodb"];
//$boton = $_POST["consultar"];


$id_cliente = $_GET["cliente"];
$tipo = $_GET["tipo"];
$modalidad = $_GET["modalidad"];
$id_ruta = $_GET["ruta"];

if($id_cliente!=0){//verifica que exista cliente definido
	$q_cliente=" AND ct.FolioTarifas_RID = ".$id_cliente."";
}else{$q_cliente="";}

if($id_ruta!=0){//verifica que exista ruta definido
	$q_ruta=" AND ct.Ruta_RID = ".$id_ruta."";
}else{$q_ruta="";}

if($tipo!=0){//verifica que exista tipo definido
	$q_tipo=" AND ct.Tipo = '".$tipo."'";
}else{$q_tipo="";}

if($modalidad!=0){//verifica que exista modalidad definido
	$q_modalidad=" AND ctp.Modalidad = '".$modalidad."'";
}else{$q_modalidad="";}



//Obtener Fechas

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));


require_once('cnx_cfdi2.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);   


////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="11" style="font-size: 18px;">Reporte Tarifas Clientes: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
                    <tr>
						<th align="center" style="font-size: 12px;">Cliente</th>
						<th align="center" style="font-size: 12px;">Ruta</th>
						<th align="center" style="font-size: 12px;">Clase</th>
						<th align="center" style="font-size: 12px;">Fecha Inicio</th>
						<th align="center" style="font-size: 12px;">Fecha Vigencia</th>
						<th align="center" style="font-size: 12px;">Estatus</th>
						<th align="center" style="font-size: 12px;">Modalidad</th>
						<th align="center" style="font-size: 12px;">Tipo</th>
						<th align="center" style="font-size: 12px;">Concepto</th>
						<th align="center" style="font-size: 12px;">Importe</th>
						<th align="center" style="font-size: 12px;">Comentarios</th>
                    </tr>
                  </thead>
                  <tbody>



<?php

$resSQL="SELECT c.RazonSocial as cliente, r.Ruta as ruta, uc.Clase as clase, ctp.FechaInicio as fecha_inicio, ctp.FechaVigencia as fecha_vigencia, ctp.Estatus as estatus,
ctp.Modalidad as modalidad, ct.Tipo as tipo, ctp.ConceptoPartida as concepto, ctp.Importe as importe, ctp.Comentarios as comentarios
FROM ".$prefijobd."clientestarifaspartidas as ctp
LEFT OUTER JOIN ".$prefijobd."clientestarifas AS ct ON ct.ID = ctp.FolioSub_RID
LEFT OUTER JOIN ".$prefijobd."clientes AS c ON c.ID = ct.FolioTarifas_RID
LEFT OUTER JOIN ".$prefijobd."rutas AS r ON r.ID = ct.Ruta_RID
LEFT OUTER JOIN ".$prefijobd."unidadesclase AS uc ON uc.ID = ct.Clase_RID
WHERE Date(ctp.FechaVigencia) Between '".$fecha_inicio."' AND '".$fecha_fin."' 
".$q_cliente."
".$q_ruta."
".$q_tipo."
".$q_modalidad."
ORDER BY c.RazonSocial
";

//echo $resSQL;
$runSQL=mysqli_query($cnx_cfdi2,$resSQL);

while ($rowSQL=mysqli_fetch_array($runSQL)){
$cliente = $rowSQL["cliente"];
$ruta = $rowSQL["ruta"];
$clase = $rowSQL["clase"];
$fecha_inicio_t = $rowSQL["fecha_inicio"];
$fecha_inicio = date("d-m-Y", strtotime($fecha_inicio_t));
$fecha_vigencia_t = $rowSQL["fecha_vigencia"];
$fecha_vigencia = date("d-m-Y", strtotime($fecha_vigencia_t));
$estatus = $rowSQL["estatus"];
$modalidad = $rowSQL["modalidad"];
$tipo = $rowSQL["tipo"];
$concepto = $rowSQL["concepto"];
$importe_t = $rowSQL["importe"];
$importe = "$".number_format($importe_t,2);
$comentarios = $rowSQL["comentarios"];
		



?>
			<tr>
				<td align="center"><?php echo $cliente ?> </td>
				<td align="left"><?php echo $ruta ?> </td>
				<td align="left"><?php echo $clase ?> </td>
				<td align="center"><?php echo $fecha_inicio ?> </td>
				<td align="center"><?php echo $fecha_vigencia ?> </td>
				<td align="left"><?php echo $estatus ?> </td>
				<td align="left"><?php echo $modalidad ?> </td>
				<td align="left"><?php echo $tipo ?> </td>
				<td align="left"><?php echo $concepto ?> </td>
				<td align="left"><?php echo $importe ?> </td>
				<td align="left"><?php echo $comentarios ?> </td>

			</tr>
<?php
					}

                 
?>					

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
