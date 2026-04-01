<?php 

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
$fecha_inicio_f = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin = $_POST["fechaf"];
$fecha_fin_f = date("d-m-Y", strtotime($fecha_fin));
$prefijobd = $_POST["base"];

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
    


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Reporte Ventas COLOSOS</title>

 <!-- Bootstrap links -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable -->

</head>

<body>
 
    <div id = "container1" style = "width: 80%; margin: 0 auto; text-align:center;" >
        <div id="contenedor2" style="overflow:hidden;">
                <!--<div id="1" style="float: left; width: 33%; text-align:left;">
                    <img src="img/logo_ts.png" height="120">
                </div>-->
                
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Reporte de Ventas <small style="color:#4da6ff; "></small></strong></h1>
                </div>

        </div>

        <hr>
        
        <div class="row">
			<div class="col-lg-12">
			  <!--<div id="2" style="float: left; width: 33%; text-align:center;">
					<h1 class="font-weight-bold" style="text-align: center;color:#0059b3; line-height: 100px;">Resumen <?php echo $anio_01; ?></h1>
			  </div>-->
			  <label>Periodo Consultado: <?php echo $fecha_inicio_f." - ".$fecha_fin_f; ?> </label>
			  <table class="table table-hover table-responsive table-condensed" id="table">
				<thead>
				  <tr>
					<th scope="col" style="text-align: center;">Factura</th>
					<th scope="col" style="text-align: center;">Descripcion</th>
					<th scope="col" style="text-align: center;">Cliente</th>
					<th scope="col" style="text-align: center;">Fecha Creado</th>
					<th scope="col" style="text-align: center;">Flete</th>
					<th scope="col" style="text-align: center;">Subtotal</th>
					<th scope="col" style="text-align: center;">Impuesto</th>
					<th scope="col" style="text-align: center;">Retenido</th>
					<th scope="col" style="text-align: center;">Total</th>
					<th scope="col" style="text-align: center;">Cobranza Abonado</th>
					<th scope="col" style="text-align: center;">Cobranza Saldo</th>
					<th scope="col" style="text-align: center;">Fecha Vence</th>
					<th scope="col" style="text-align: center;">Estatus</th>
					<th scope="col" style="text-align: center;">CFDI Sustituida por</th>
					<th scope="col" style="text-align: center;">Abonos</th>
				  </tr>
				</thead>
				<tbody>
				<?php
					$resSQL="SELECT *  FROM ".$prefijobd."factura WHERE Date(Creado) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."' ORDER BY Creado";
					$runSQL=mysql_query($resSQL);
					
					while ($rowSQL=mysql_fetch_array($runSQL)){
						//Obtener_variables
						$xfolio = $rowSQL['XFolio'];
						$creado = $rowSQL['Creado'];
						$flete_t = $rowSQL['yFlete'];
						$flete = "$".number_format($flete_t,2);
						$subtotal_t = $rowSQL['zSubtotal'];
						$subtotal = "$".number_format($subtotal_t,2);
						$impuesto_t = $rowSQL['zImpuesto'];
						$impuesto = "$".number_format($impuesto_t,2);
						$retenido_t = $rowSQL['zRetenido'];
						$retenido = "$".number_format($retenido_t,2);
						$cancelado = $rowSQL['cCanceladoT'];
						$total_t = $rowSQL['zTotal'];
						$total = "$".number_format($total_t,2);
						$cobranzaabonado_t = $rowSQL['CobranzaAbonado'];
						$cobranzaabonado = "$".number_format($cobranzaabonado_t,2);
						$cobranzasaldo_t = $rowSQL['CobranzaSaldo'];
						$cobranzasaldo = "$".number_format($cobranzasaldo_t,2);
						$vence = $rowSQL['Vence'];
						$sustituidapor = $rowSQL['cfdiSustituidaPor'];
						$cancelado_t = $rowSQL['cCanceladoT'];
						$cliente_id = $rowSQL['CargoAFactura_RID'];
						$factura_id = $rowSQL['ID'];
						if($cancelado_t == ''){
							$cancelado = "Vigente";
						} else {
							$cancelado = "Cancelado";
						}
						
						//Buscar Cliente
						
							$resSQL1="SELECT *  FROM ".$prefijobd."clientes WHERE ID=".$cliente_id;
							$runSQL1=mysql_query($resSQL1);
							while ($rowSQL1=mysql_fetch_array($runSQL1)){
								$cliente = $rowSQL1['RazonSocial'];
							}
						
						//Buscar AbonosSub
						
						$resSQL2="SELECT b.XFolio  FROM ".$prefijobd."abonossub a  inner join ".$prefijobd."abonos b on a.AbonoFactura_RID=".$factura_id." and b.ID=a.FolioSub_RID";
						$foliosAbonos=[];
						$runSQL2=mysql_query($resSQL2);
						while ($rowSQL2=mysql_fetch_array($runSQL2)){
							//$folioabono = $rowSQL2['XFolio'];
							$foliosAbonos[]=$rowSQL2['XFolio'];
						}
						
						$resSQL3="SELECT a.Detalle  FROM ".$prefijobd."FacturaPartidas a  inner join ".$prefijobd."Factura b on a.FolioSub_RID=".$factura_id." and b.ID=a.FolioSub_RID AND a.Tipo='Flete'";
						$Detalles=[];
						$runSQL3=mysql_query($resSQL3);
						while ($rowSQL3=mysql_fetch_array($runSQL3)){
							//$folioabono = $rowSQL2['XFolio'];
							$Detalles[]=$rowSQL3['Detalle'];
						}
				if (!$runSQL3) {//debug
                      $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                      $mensaje .= 'Consulta completa: ' . $resSQL3;
                      die($mensaje);
                }
							
							
				?>
				
				  <tr>
					
					<td style="text-align: center;"><?php echo $xfolio; ?></td>
					<td style="text-align: center;"><?php echo implode(", ",$Detalles); ?></td>
					<td style="text-align: center;"><?php echo $cliente; ?></td>
					<td style="text-align: center;"><?php echo $creado; ?></td>
					<td style="text-align: center;"><?php echo $flete; ?></td>
					<td style="text-align: center;"><?php echo $subtotal; ?></td>
					<td style="text-align: center;"><?php echo $impuesto; ?></td>
					<td style="text-align: center;"><?php echo $retenido; ?></td>
					<td style="text-align: center;"><?php echo $total; ?></td>
					<td style="text-align: center;"><?php echo $cobranzaabonado; ?></td>
					<td style="text-align: center;"><?php echo $cobranzasaldo; ?></td>
					<td style="text-align: center;"><?php echo $vence; ?></td>
					<td style="text-align: center;"><?php echo $cancelado; ?></td>
					<td style="text-align: center;"><?php echo $sustituidapor; ?></td>
					<td style="text-align: center;"><?php echo implode(", ",$foliosAbonos); ?></td>
				  </tr>
				  
				<?php
				 $folioabono ="";
					}
				?>  
				</tbody>
			  </table>
			</div>
        </div>
        <br>
		<div class="row">
			<div class="col-md-12" style="text-align:left">
				<a href="colosos_facturas_reporte_excel.php?fechai=<?php echo $fecha_inicio; ?>&fechaf=<?php echo $fecha_fin; ?>&prefijodb=<?php echo $prefijobd; ?>"><button type="button" class="btn btn-success">Exporta a Excel</button></a>
			</div>
		</div>
		<br>
		<br>

    </div>
	
	<script>
	  $(document).ready(function() {
		$('#table').DataTable();
	  } );
	</script>
	
  </body>
</html>
<?php
mysql_free_result($runSQL);
mysql_close($cnx_cfdi);
?>