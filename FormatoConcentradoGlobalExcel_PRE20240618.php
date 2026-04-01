<?php
header("Content-type: application/vnd.ms-excel");
$nombre="Concentrado_Global_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

//mysqli_query($conexion,"SET NAMES 'utf8'");



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
                      <th align="center" colspan="8" style="font-size: 18px;">Formato Concentrado Global. Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
					<tr>
					<th rowspan ="2" align="center" style="font-size: 12px;">No.</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">Fecha Doc.</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">No. Doc.</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">No. Porte</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">Origen</th>
					<!--th rowspan ="2" align="center" style="font-size: 12px;">Cliente</th-->
					<th rowspan ="2" align="center" style="font-size: 12px;">Destinatario</th>
					<th colspan ="2" align="center" style="font-size: 12px;">Evidencias</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">No. Factura Perez</th>
					<th rowspan ="2" align="center" style="font-size: 12px;">Operador</th>
				  </tr>
				  <tr>
					<th align="center" style="font-size: 12px;">Sello</th>
					<th align="center" style="font-size: 12px;">Firma</th>

				  </tr>
                  </thead>
                  <tbody>



<?php

$resSQL="SELECT Creado, RemisionOperador, XFolio, Remitente, Destinatario, Sello, Firma, Operador_RID FROM ".$prefijobd."Remisiones WHERE Date(Creado)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND CargoACliente_RID = '".$_GET["cliente"]."' GROUP BY Creado;";
$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
$cont=1;
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables

		$creado = $rowSQL['Creado'];
		$ticket = $rowSQL['RemisionOperador'];
		$xfolio = $rowSQL['XFolio'];
		$remitente = $rowSQL['Remitente'];
		$destinatario = $rowSQL['Destinatario'];
		$sello = $rowSQL['Sello'];
		$firma = $rowSQL['Firma'];
		$operadorID = $rowSQL['Operador_RID'];


		//Buscar datos de Operador
		$resSQLOperador = "SELECT Operador FROM ".$prefijobd."Operadores WHERE ID=".$operadorID;
		$runSQLOperador = mysqli_query($cnx_cfdi2, $resSQLOperador);
		while($rowSQLOperador = mysqli_fetch_array($runSQLOperador)){
			$operador = $rowSQLOperador['Operador'];
		}
		if($sello!=0){$sello="X";}else{$sello="";}
		if($firma!=0){$firma="X";}else{$firma="";}

		$creado = date("d-m-Y", strtotime($creado));
				

?>
				  <tr>
				  	<td align="center"><?php echo $cont ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $ticket ?> </td>
					<td align="left"><?php echo $xfolio ?> </td>
					<td align="left"><?php echo $remitente ?> </td>
					<!--td align="left"><?php //echo $destinatario ?> </td-->
					<td align="left"><?php echo $destinatario ?> </td>
					<td align="left"><?php echo $sello ?> </td>
					<td align="left"><?php echo $firma ?> </td>
					<td align="left">PEND. ALBARAN</td>
					<td align="left"><?php echo $operador ?> </td>
				  </tr>


<?php

			$cont++;
                  } // FIN del WHILE
?>					

                  </tbody>
                </table>




<?php

//////////////////////////////////////////////////////// FIN Reporte en Excel


?>
