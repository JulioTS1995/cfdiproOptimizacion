<?php
header("Content-type: application/vnd.ms-excel");
$nombre="Concentrado_315_".date("d-m-Y")."_".date("h:i:s").".xls";//
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');



require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

//mysqli_query($conexion,"SET NAMES 'utf8'");


$prefijobd = $_GET['prefijodb'];

$fecha_inicio = $_GET["fechai"];
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_GET["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));

$cliente = '1549261';
$circuito = '8';

$qryExtra = " AND Circuito2_RID = '".$circuito."' ";



////////////////////////////////////////////////////////Reporte en Excel
?>

<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">



                <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
                  <thead>
                    <tr>
                      <th align="center" colspan="6" style="font-size: 18px;">Formato Concentrado 315. Periodo: <?php echo $fecha_inicio_f."-".$fecha_fin_f; ?></th>
                    </tr>
                    <tr>
						<th align="center" style="font-size: 12px;">No.</th>
						<th align="center" style="font-size: 12px;">Fecha Doc.</th>
						<th align="center" style="font-size: 12px;">No. Doc.</th>
						<th align="center" style="font-size: 12px;">No. Porte</th>
						<th align="center" style="font-size: 12px;">Origen</th>
						<th align="center" style="font-size: 12px;">Cliente</th>
                    </tr>
                  </thead>
                  <tbody>



<?php

$resSQL="SELECT Creado, RemisionOperador, XFolio, Remitente, Destinatario FROM ".$prefijobd."Remisiones WHERE Date(Creado)Between '".$_GET["fechai"]." 00:00:00' AND '".$_GET["fechaf"]." 23:59:59' AND CargoACliente_RID = '".$cliente."' $qryExtra ORDER BY Creado;";
$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
$cont=1;
	while ($rowSQL=mysqli_fetch_array($runSQL)){
		//Obtener_variables
		$creado = $rowSQL['Creado'];
		$ticket = $rowSQL['RemisionOperador'];
		$xfolio = $rowSQL['XFolio'];
		$remitente = $rowSQL['Remitente'];
		$destinatario = $rowSQL['Destinatario'];

		$creado = date("d-m-Y", strtotime($creado));
				
				

?>
				  <tr>
				  <td align="center"><?php echo $cont ?> </td>
					<td align="left"><?php echo $creado ?> </td>
					<td align="left"><?php echo $ticket ?> </td>
					<td align="left"><?php echo $xfolio ?> </td>
					<td align="left"><?php echo $remitente ?> </td>
					<td align="left"><?php echo $destinatario ?> </td>
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
