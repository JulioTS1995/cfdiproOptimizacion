<?php 


/*Datos solicitados

-cliente					(cliente-razon social)
-folio abono				(abono-xfolio)
-folio factura				(factura-xfolio)
-fecha creacion abono		(abono-fecha)
-fecha creacion factura		(factura-fecha)
-fecha aplicado abono		(abono-)
-dias de pago
-forma de pago
-banco
-sub total
-iva
-retencion
-total
-comentarios

*/

/*if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}*/

//Internalizo los parametros previo escape de caracteres especiales
//$prefijobd = @mysql_escape_string($_GET["base"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
//$pos = strpos($prefijobd, "_");

//if ($pos === false) {
//    $prefijobd = $prefijobd . "_";
//} 

//Obtener Fechas

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["base"];

$cliente_id = $_POST["cliente"];
$atiende_id = $_POST["atiende"];

    require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	
if($cliente_id  > 0){
$sql_cliente = ' AND C.ID='.$cliente_id;
}else{
	$sql_cliente = '';
}

if($atiende_id  > 0){
	$sql_atiende = ' AND C.Atiende_RID='.$atiende_id;
}else{
	$sql_atiende = '';
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Listado Tracking por Remision</title>
<link href="sierraestilo.css" rel="stylesheet" type="text/css">
</head>

<body class="twoColElsLtHdr">

<div id="container">
  <div id="header">
    <h1>Abonos Detalle
      <!-- end #header -->
    </h1>
  </div>
  <div id="sidebar1">
    <h3>Listado</h3>
	<a href="abonos_detalle2_excel.php?fecha_inicio=<?php echo $fecha_inicio;?>&fecha_fin=<?php echo $fecha_fin;?>&prefijobd=<?php echo $prefijobd;?>&cliente_id=<?php echo $cliente_id;?>&atiende_id=<?php echo $atiende_id;?>""><input type="button" value="Descargar Excel" name="excel" class="btn btn-info"></a>
    <p>&nbsp;</p>
	<form >
		<input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
		<input type="date" class="form-control inputdefault" name="fechai" id="fecha" hidden value="<?php echo $fecha_inicio; ?>">
		<input type="date" class="form-control inputdefault" name="fechaf" id="fecha" hidden value="<?php echo $fecha_fin; ?>">
    
	</form>
    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">

    <table border="1">
  <tr>
    <th class="input">Cliente</th>
    <th class="input">Folio Abono</th>
    <th class="input">Folio Factura</th>
	<th class="input">Fecha creacion Abono</th>
	<th class="input">Fecha creacion Factura</th>
	<th class="input">Fecha Pago</th>
	<th class="input">Dif Fechas</th>
	<th class="input">Forma de pago</th>
	<th class="input">Banco</th>
	<th class="input">Subtotal</th>
	<th class="input">IVA</th>
	<th class="input">Retencion</th>
	<th class="input">Total</th>
	<th class="input">Comentarios</th>
	<th class="input">Atiende</th>

	
  </tr>
  <?php
  
	//$resSQL="SELECT * FROM opl_remisiones WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' ORDER BY Unidad_RID";
	//$resSQL="SELECT XFolio, fecha, formapago, comentarios, cliente_RID, cuentabancaria_RID, ID FROM ".$prefijobd."abonos WHERE Date(fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."'";
	//echo

	$resSQL="SELECT A.XFolio, A.fecha, A.formapago33_RID, A.comentarios, A.cliente_RID, A.cuentabancaria_RID, A.ID, S.Fechaaplicacion, S.subtotal, S.impuesto, S.retenido, S.importe, S.abonofactura_RID, C.RazonSocial as cliente, C.Atiende_RID  as atiende_id FROM ".$prefijobd."abonos A,".$prefijobd."abonossub S, ".$prefijobd."clientes C WHERE Date(fecha) Between '".$_POST["fechai"]."' AND '".$_POST["fechaf"]."' AND S.FolioSub_RID = A.ID AND C.ID=A.cliente_RID ".$sql_cliente.$sql_atiende."  ORDER BY A.Fecha";
	//echo $resSQL;
	$runSQL=mysql_query($resSQL);
	/*if (!$runSQL) {
		$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL;
		die($mensaje);
	}*/
	//
	while ($rowSQL=mysql_fetch_array($runSQL))
	{
		//Obtener_variables

		$id=$rowSQL['ID'];
		$clienteid = $rowSQL['cliente_RID'];
		$cliente = $rowSQL['cliente'];
		$atiendeid = $rowSQL['atiende_id'];
		
		$xfolio = $rowSQL['XFolio'];
		$fecreadoAbono = $rowSQL['fecha'];
		$formapago_id = $rowSQL['formapago33_RID'];

		//Busca forma de pago en Tabla General
		$resSQL35="SELECT descripcion  FROM ".$prefijobd."tablageneral WHERE ID ='".$formapago_id."'";
		$runSQL35=mysql_query($resSQL35);
		$rowSQL35=mysql_fetch_array($runSQL35);
		$formapago = $rowSQL35['descripcion'];

		//$diaspago = $fecha->$fechaapl;
		//$diaspago=date_diff($fecha,$fechaapl);
		$comentario = $rowSQL['comentarios'];
		$cuentaBanco=$rowSQL['cuentabancaria_RID'];
		$feaplic = $rowSQL['Fechaaplicacion'];
		$subtotal = $rowSQL['subtotal'];
		$subtotal = bcdiv($subtotal, '1', 2);
		$iva = $rowSQL['impuesto'];
		$iva = bcdiv($iva, '1', 2);
		$retencion = $rowSQL['retenido'];
		$total = $rowSQL['importe'];
		$idfactura = $rowSQL['abonofactura_RID'];
		
		//Calcular diferencia de de dias entre Fecha
		
		// Declaramos nuestras fechas inicial y final
		$fechaInicial = date($fecreadoAbono);
		$fechaFinal = date($feaplic);
		
		// Las convertimos a segundos
		$fechaInicialSegundos = strtotime($fechaInicial);
		$fechaFinalSegundos = strtotime($fechaFinal);
		
		// Hacemos las operaciones para calcular los dias entre las dos fechas y mostramos el resultado
		$dias = ($fechaFinalSegundos - $fechaInicialSegundos) / 86400;
		
		$diferencia_fechas = round($dias, 0, PHP_ROUND_HALF_UP);
		
		//echo "La diferencia entre la fecha : " . $fechaInicial . " y " . $fechaFinal . " es de: " . round($dias, 0, PHP_ROUND_HALF_UP)  . " dias." ;

		//Resultado de los dias de diferencia entre dos fechas
		
		/*
		*   La diferencia entre la fecha : 2022-01-01 y 2023-01-01 es de: 365 dias.
		*/
		
		//FIN Calcular diferencia de de dias entre Fecha
	
		
		

		/*$resSQL1="SELECT Fechaaplicacion, subtotal, aplicaiva, aplicaretencion, importe, abonofactura_RID  FROM ".$prefijobd."abonossub WHERE ID ='".$id."'";
		$runSQL1=mysql_query($resSQL1);
		$rowSQL1=mysql_fetch_array($runSQL1);*/

		/*
		$resSQL2="SELECT razonSocial,AtiendeShorty  FROM ".$prefijobd."clientes WHERE ID ='".$clienteid."'";
		$runSQL2=mysql_query($resSQL2);
		$rowSQL2=mysql_fetch_array($runSQL2);
		$cliente = $rowSQL2['razonSocial'];
		$atiende = $rowSQL2['AtiendeShorty'];
		*/
		

		$resSQL3="SELECT banco  FROM ".$prefijobd."bancos WHERE ID ='".$cuentaBanco."'";
		$runSQL3=mysql_query($resSQL3);
		$rowSQL3=mysql_fetch_array($runSQL3);
		$banco = $rowSQL3['banco'];

		$resSQL4="SELECT XFolio, Fecreado  FROM ".$prefijobd."factura WHERE ID ='".$idfactura."'";
		$runSQL4=mysql_query($resSQL4);
		$rowSQL4=mysql_fetch_array($runSQL4);
		$xfolio2 = $rowSQL4['XFolio'];
		$fecreadofactura = $rowSQL4['Fecreado'];

		$resSQL5="SELECT * FROM ".$prefijobd."usuarios WHERE ID ='".$atiendeid."'";
		$runSQL5=mysql_query($resSQL5);
		$rowSQL5=mysql_fetch_array($runSQL5);
		$atiende = $rowSQL5['Nombre'];
		
		
		
  ?>
  <tr>

  	<td width="450" class="table"><?php echo $cliente; ?></td>
    <td width="60" class="table"><?php echo $xfolio; ?></td>
	<td width="60" class="table"><?php echo $xfolio2; ?></td>
    <td width="150" class="table"><?php echo $fecreadoAbono; ?></td>
	<td width="150" class="table"><?php echo $fecreadofactura; ?></td>
	<td width="150" class="table"><?php echo $feaplic; ?></td>
	<td width="150" class="table"><?php echo $diferencia_fechas; ?></td>
	<td width="200" class="table"><?php echo $formapago; ?></td>
	<td width="200" class="table"><?php echo $banco; ?></td>
	<td width="80" class="table"><?php echo $subtotal; ?></td>
	<td width="80" class="table"><?php echo $iva; ?></td>
	<td width="80" class="table"><?php echo $retencion; ?></td>
	<td width="80" class="table"><?php echo $total; ?></td>
	<td width="350" class="table"><?php echo $comentario; ?></td>
	<td width="150" class="table"><?php echo $atiende; ?></td>
	
	

  </tr>
  <?php }  ?>
</table>
<?php



//	for($cont=1; $cont<=$paginas; $cont++)
//	{

//		echo "<a href='timbradoslist.php?numP=".$cont."' >$cont</a> ";	
//	}

?>
</form>
  <!-- end #mainContent --></div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
    <p>TractoSoft</p>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
<?php
mysql_free_result($runSQL);
//mysql_free_result($runSQL2);
mysql_close($cnx_cfdi);
?>