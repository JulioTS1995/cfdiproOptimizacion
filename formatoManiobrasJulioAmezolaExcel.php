<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 

header("Content-type: application/vnd.ms-excel");
$nombre="Formato_Maniobras_JulioAmezola_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");

require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

$v_condicion = $_GET['vsql'];
$prefijobd = $_GET['prefijodb'];
$id_banco = $_GET["banco"];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));


////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="8" style="font-size: 18px;">Formato maniobras Julio Amezola. Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
					<tr>
					    <th align="center" style="font-size: 12px;">SOLICITO</th>
						<th align="center" style="font-size: 12px;">FECHA</th>
						<th align="center" style="font-size: 12px;">O. DE EMBARQUE</th>
						<th align="center" style="font-size: 12px;">NO. DE FACTURA</th>
						<th align="center" style="font-size: 12px;">NO. TRANSFERENCIA</th>
						<th align="center" style="font-size: 12px;">CLIENTE</th>
						<th align="center" style="font-size: 12px;">ID</th>
						<th align="center" style="font-size: 12px;">CONCEPTO</th>
						<th align="center" style="font-size: 12px;">CALCULO MANIOBRAS</th>
						<th align="center" style="font-size: 12px;"># CAJAS</th>
						<th align="center" style="font-size: 12px;">TOTAL</th>
				  </tr>
                  </thead>
                  <tbody>

<?php

$resSQLGastoSum = "SELECT 
REM.ID AS idRem,
GAS.ID AS idGas,
REM.Creado,
REM.RemisionOperador,
REM.Destinatario,
REM.Factura,
REM.Transferencia,
GAS.Concepto AS ConceptoGas,
GAS.Importe AS ImporteGas,
REM.CargoASolicito
FROM
".$prefijobd."GastosViajesSub AS SUB
	INNER JOIN
".$prefijobd."GastosViajes_REF AS REF ON REF.RID = SUB.ID
	INNER JOIN
".$prefijobd."GastosViajes AS GAS ON GAS.ID = REF.ID
	INNER JOIN
".$prefijobd."Remisiones AS REM ON REM.ID = GAS.Remision_RID
WHERE
SUB.Concepto = 'Maniobras'
	AND Date(REM.Creado)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59'
	AND REM.CargoACliente_RID = '".$_GET["cliente"]."'
GROUP BY GAS.ID;";
$runSQLGastoSum = mysqli_query($cnx_cfdi2, $resSQLGastoSum);
while($rowSQLGastoSum = mysqli_fetch_array($runSQLGastoSum)){
	$idRemision = $rowSQLGastoSum['idRem'];
	$idGasto = $rowSQLGastoSum['idGas'];
	$creado = $rowSQLGastoSum['Creado'];
	$ticket = $rowSQLGastoSum['RemisionOperador'];
	$factura = $rowSQLGastoSum['Factura'];
	$destinatario = $rowSQLGastoSum['Destinatario'];
	$transferencia = $rowSQLGastoSum['Transferencia'];
	$concepto = $rowSQLGastoSum['ConceptoGas'];
	$importe = $rowSQLGastoSum['ImporteGas'];
	$solicito = $rowSQLGastoSum['CargoASolicito'];


//Buscar datos de Embalaje
$resSQLEmbalaje = "SELECT Referencia, Cantidad FROM ".$prefijobd."RemisionesSub WHERE FolioSub_RID=".$idRemision." LIMIT 0,1;";
$runSQLEmbalaje = mysqli_query($cnx_cfdi2, $resSQLEmbalaje);
while($rowSQLEmbalaje = mysqli_fetch_array($runSQLEmbalaje)){
	$referencia = $rowSQLEmbalaje['Referencia'];
	$cantidadEmbalaje = $rowSQLEmbalaje['Cantidad'];
}




$creado = date("d-m-Y", strtotime($creado));





				?>
				
				<tr>
					<td align="left"><?php echo $solicito ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $ticket ?> </td>
					<td align="left"><?php echo $factura ?> </td>
					<td align="left"><?php echo $transferencia ?> </td>
					<td align="left"><?php echo $destinatario ?> </td>
					<td align="left"><?php echo $referencia ?> </td>
					<td align="left">Maniobras</td>
					<td align="left"><?php echo $concepto ?> </td>
					<td align="left"><?php echo $cantidadEmbalaje ?></td>
					<td align="left"><?php echo "$".$importe?> </td>
				  </tr>


<?php

                  } // FIN del WHILE
?>					

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
