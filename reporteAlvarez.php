<?php  
error_reporting(0);//NO REPORTAR ERRORES, NECESARIO PARA NO LLENAR EL EXCEL DE ERRORES CUANDO NO SE ENCUENTRA UN CAMPO
//Recibir variables
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$boton = $_POST["button"];
$id_cliente = $_POST['cliente'];
$tipoFact = $_POST['fact'];


if($id_cliente!=0){
	$cntQuery="AND CargoAFactura_RID = ".$id_cliente."";
}else{$cntQuery="";}

if($tipoFact!=0){
	$cntQuery2="AND CobranzaAbonado > 0";
}else{$cntQuery2="";}
//Formato a Fechas

$fecha_inicio_t = date("d-m-Y", strtotime($fecha_inicio));
$fecha_fin_t = date("d-m-Y", strtotime($fecha_fin));

$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');
    
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;  

//Seleccionar Mes letra
  switch ("$mes_logs") {
    case '01':
        $mes2 = "Enero";
      break;
    case '02':
        $mes2 = "Febrero";
      break;
    case '03':
        $mes2 = "Marzo";
      break;
    case '04':
        $mes2 = "Abril";
      break;
    case '05':
        $mes2 = "Mayo";
      break;
    case '06':
        $mes2 = "Junio";
      break;
    case '07':
        $mes2 = "Julio";
      break;
    case '08':
        $mes2 = "Agosto";
      break;
    case '09':
        $mes2 = "Septiembre";
      break;
    case '10':
        $mes2 = "Octubre";
      break;
    case '11':
        $mes2 = "Noviembre";
      break;
    case '12':
        $mes2 = "Diciembre";
      break;
    
  } //Fin switch

$fecha = $dia_logs." de ".$mes2." de ". $anio_logs;

$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;




if($boton == 'Generar Reporte'){
		  header("Content-type: application/vnd.ms-excel");
$nombre="Reporte_Devoluciones_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');require_once('cnx_cfdi2.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

mysqli_query($cnx_cfdi2,"SET NAMES 'utf8'");


$rfcReceptor ="";
$razonReceptor = "";
$rfcEmisor = "";
$razonEmisor = "";

?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="12">
								<h2><b>INFORMACION ADICIONAL DEVOLUCIONES</b></h2>
							</th>
						</tr>
						<tr>
							<th align="center" style="font-size: 12px;" colspan="12">
								<h4>Periodo: <?php echo $fecha_inicio_t." - ".$fecha_fin_t; ?></h4>
							</th>
						</tr>
						<tr>
							<th class="input">UUID</th>
							<th class="input">SERIE</th>
							<th class="input">FOLIO</th>
							<th class="input">MONEDA</th>
							<th class="input">TIPO CAMBIO</th>
							<th class="input">METODO PAGO</th>
							<th class="input">FECHA EMISION</th>
							<th class="input">FECHA CERTIFICACION</th>
              <th class="input">RFC EMISOR</th>
              <th class="input">RAZON EMISOR</th>
              <th class="input">RFC RECEPTOR</th>
							<th class="input">RAZON RECEPTOR</th>
							<th class="input">CLAVES DE PRODUCTO</th>
							<th class="input">CONCEPTOS</th>
              <th class="input">DESCUENTO</th>
							<th class="input">SUBTOTAL</th>
              <th class="input">IVA TRASLADO</th>
              <th class="input">IVA RETENIDO</th>
              <th class="input">TOTAL PAGADO</th>

						</tr>
					</thead>
					<tbody>
					<?php

					
					//Agrupar por REMISION
          if($tipoFact==0){

					$resSQL01 = "SELECT * FROM ".$prefijobd."Factura WHERE cCanceladoT IS NULL AND cfdiuuid IS NOT NULL AND Date(cfdfchhra) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' ".$cntQuery." ORDER BY cfdfchhra;";
					$runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
          //die($resSQL01);

					while($rowSQL01 = mysqli_fetch_assoc($runSQL01)){
              $IDfact = $rowSQL01['ID'];
              $uuid = $rowSQL01['cfdiuuid'];
              $serie = $rowSQL01['cfdserie'];
              $folio = $rowSQL01['cfdfolio'];
              $moneda = $rowSQL01['Moneda'];
              $tipoCambio = $rowSQL01['TipoCambio'];
              $tipoCambio=number_format($tipoCambio, 2);

              $metodo = $rowSQL01['metodopago33_RID'];
              $query2 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE ID ='".$metodo."';"; 
              $runsql2 = mysqli_query($cnx_cfdi2, $query2);
              if (!$runsql2) {//debug
                  $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                  $mensaje .= 'Consulta completa: ' . $query2;
                  die($mensaje);
              }
              while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                  $metodoID2 = $rowsql2['ID2'];
              }

              $fechaEmision = $rowSQL01['cfdfchhra'];
              $fechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
              $receptor = $rowSQL01['CargoAFactura_RID'];

              $query3 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID ='".$receptor."';"; 
              $runsql3 = mysqli_query($cnx_cfdi2, $query3);
              if (!$runsql3) {//debug
                  $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                  $mensaje .= 'Consulta completa: ' . $query3;
                  die($mensaje);
              }
              while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                  $rfcReceptor = $rowsql3['RFC'];
                  $razonReceptor = $rowsql3['RazonSocial'];
              }

              $query = "SELECT * FROM ".$prefijobd."SystemSettings;"; 
              $runsql = mysqli_query($cnx_cfdi2, $query);
              if (!$runsql) {//debug
                  $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                  $mensaje .= 'Consulta completa: ' . $query;
                  die($mensaje);
              }
              while ($rowsql = mysqli_fetch_assoc($runsql)){
                  $rfcEmisor = $rowsql['RFC'];
                  $razonEmisor = $rowsql['RazonSocial'];
              }

              $subtotal = $rowSQL01['zSubtotal'];
              $subtotal=number_format($subtotal, 2);

              $impuesto = $rowSQL01['zImpuesto'];
              $impuesto=number_format($impuesto, 2);

              $retenido = $rowSQL01['zRetenido'];
              $retenido=number_format($retenido, 2);

              $total = $rowSQL01['zTotal'];
              $total=number_format($total, 2);
						
						
              $cont=0;

              $resSQL28 ="SELECT * FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID = ".$IDfact.";";
              $runSQL28 = mysqli_query($cnx_cfdi2,$resSQL28);
              $rowsNum=mysqli_num_rows($runSQL28);
              if($rowsNum==0){
                ?> 
                <tr>
              <td align="center"><?php echo $uuid; ?></td>
              <td align="center"><?php echo $serie; ?></td>
              <td align="center"><?php echo $folio; ?></td>
              <td align="center"><?php echo $moneda; ?></td>
              <td align="center"><?php echo "$".$tipoCambio; ?></td>
              <td align="center"><?php echo $metodoID2; ?></td>
              <td align="center"><?php echo $fechaEmision; ?></td>
              <td align="center"><?php echo $fechaTimbrado; ?></td>
              <td align="center"><?php echo $rfcEmisor; ?></td>
              <td align="center"><?php echo $razonEmisor; ?></td>
              <td align="center"><?php echo $rfcReceptor; ?></td>
              <td align="center"><?php echo $razonReceptor; ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php   ?></td>
              <td align="right"><?php   ?></td>
              <td align="right"><?php   ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php echo "$".$subtotal; ?></td>
              <td align="right"><?php echo "$".$impuesto; ?></td>
              <td align="right"><?php echo "$".$retenido; ?></td>
              <td align="right"><?php echo "$".$total; ?></td>
  
            </tr>
            <?php 
              }
  
              while($rowSQL28 = mysqli_fetch_array($runSQL28)){
                  $cveProdServ = $rowSQL28['prodserv33'];
  
                  $detalle = $rowSQL28['Detalle'];

                  $descuento = $rowSQL28['Descuento'];
                  $descuento=number_format($descuento, 2);

                  if($cont==0 && $rowsNum!=0){
                    ?>
                    <tr>
              <td align="center"><?php echo $uuid; ?></td>
              <td align="center"><?php echo $serie; ?></td>
              <td align="center"><?php echo $folio; ?></td>
              <td align="center"><?php echo $moneda; ?></td>
              <td align="center"><?php echo "$".$tipoCambio; ?></td>
              <td align="center"><?php echo $metodoID2; ?></td>
              <td align="center"><?php echo $fechaEmision; ?></td>
              <td align="center"><?php echo $fechaTimbrado; ?></td>
              <td align="center"><?php echo $rfcEmisor; ?></td>
              <td align="center"><?php echo $razonEmisor; ?></td>
              <td align="center"><?php echo $rfcReceptor; ?></td>
              <td align="center"><?php echo $razonReceptor; ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php echo $cveProdServ;  ?></td>
              <td align="right"><?php echo $detalle;  ?></td>
              <td align="right"><?php echo "$".$descuento;  ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php echo "$".$subtotal; ?></td>
              <td align="right"><?php echo "$".$impuesto; ?></td>
              <td align="right"><?php echo "$".$retenido; ?></td>
              <td align="right"><?php echo "$".$total; ?></td>
              
              
  
            </tr>
            <?php
            $cont++;
            
                  }
                  if($cont>0 && $rowsNum>1){
                    ?>
                    <tr>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <td align="center"><?php ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php echo $cveProdServ;  ?></td>
              <td align="right"><?php echo $detalle;  ?></td>
              <td align="right"><?php echo "$".$descuento;  ?></td>
              <!--td align="right"></td-->
              <td align="right"><?php ?></td>
              <td align="right"><?php ?></td>
              <td align="right"><?php ?></td>
              <td align="right"><?php ?></td>
              
              
  
            </tr>
            <?php
            $cont++;
                  }
  
              }
					?>


					<?php 
          }
						} 
            if($tipoFact!=0){
              $resSQL02 = "SELECT * FROM ".$prefijobd."AbonosSub WHERE Date(FechaAplicacion) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59'  ORDER BY FechaAplicacion;";
              $runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);
              //die($resSQL02);

              while($rowSQL02 = mysqli_fetch_assoc($runSQL02)){
              $IDfact = $rowSQL02['AbonoFactura_RID'];


              $resSQL01 = "SELECT * FROM ".$prefijobd."Factura WHERE cCanceladoT IS NULL AND cfdiuuid IS NOT NULL AND ID=".$IDfact." ".$cntQuery.";";
              $runSQL01 = mysqli_query($cnx_cfdi2, $resSQL01);
              //die($resSQL01);
    
              while($rowSQL01 = mysqli_fetch_assoc($runSQL01)){
                  //$IDfact = $rowSQL01['ID'];
                  $uuid = $rowSQL01['cfdiuuid'];
                  $serie = $rowSQL01['cfdserie'];
                  $folio = $rowSQL01['cfdfolio'];
                  $moneda = $rowSQL01['Moneda'];
                  $tipoCambio = $rowSQL01['TipoCambio'];
                  $tipoCambio=number_format($tipoCambio, 2);
    
                  $metodo = $rowSQL01['metodopago33_RID'];
                  $query2 = "SELECT * FROM ".$prefijobd."TablaGeneral WHERE ID ='".$metodo."';"; 
                  $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                  if (!$runsql2) {//debug
                      $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                      $mensaje .= 'Consulta completa: ' . $query2;
                      die($mensaje);
                  }
                  while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                      $metodoID2 = $rowsql2['ID2'];
                  }
    
                  $fechaEmision = $rowSQL01['cfdfchhra'];
                  $fechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
                  $receptor = $rowSQL01['CargoAFactura_RID'];
    
                  $query3 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID ='".$receptor."';"; 
                  $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                  if (!$runsql3) {//debug
                      $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                      $mensaje .= 'Consulta completa: ' . $query3;
                      die($mensaje);
                  }
                  while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                      $rfcReceptor = $rowsql3['RFC'];
                      $razonReceptor = $rowsql3['RazonSocial'];
                  }
    
                  $query = "SELECT * FROM ".$prefijobd."SystemSettings;"; 
                  $runsql = mysqli_query($cnx_cfdi2, $query);
                  if (!$runsql) {//debug
                      $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                      $mensaje .= 'Consulta completa: ' . $query;
                      die($mensaje);
                  }
                  while ($rowsql = mysqli_fetch_assoc($runsql)){
                      $rfcEmisor = $rowsql['RFC'];
                      $razonEmisor = $rowsql['RazonSocial'];
                  }
    
                  $subtotal = $rowSQL01['zSubtotal'];
                  $subtotal=number_format($subtotal, 2);
    
                  $impuesto = $rowSQL01['zImpuesto'];
                  $impuesto=number_format($impuesto, 2);
    
                  $retenido = $rowSQL01['zRetenido'];
                  $retenido=number_format($retenido, 2);
    
                  $total = $rowSQL01['zTotal'];
                  $total=number_format($total, 2);
                
                
                  $cont=0;
    
                  $resSQL28 ="SELECT * FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID = ".$IDfact.";";
                  $runSQL28 = mysqli_query($cnx_cfdi2,$resSQL28);
                  $rowsNum=mysqli_num_rows($runSQL28);
                  if($rowsNum==0){
                    ?> 
                    <tr>
                  <td align="center"><?php echo $uuid; ?></td>
                  <td align="center"><?php echo $serie; ?></td>
                  <td align="center"><?php echo $folio; ?></td>
                  <td align="center"><?php echo $moneda; ?></td>
                  <td align="center"><?php echo "$".$tipoCambio; ?></td>
                  <td align="center"><?php echo $metodoID2; ?></td>
                  <td align="center"><?php echo $fechaEmision; ?></td>
                  <td align="center"><?php echo $fechaTimbrado; ?></td>
                  <td align="center"><?php echo $rfcEmisor; ?></td>
                  <td align="center"><?php echo $razonEmisor; ?></td>
                  <td align="center"><?php echo $rfcReceptor; ?></td>
                  <td align="center"><?php echo $razonReceptor; ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php   ?></td>
                  <td align="right"><?php   ?></td>
                  <td align="right"><?php   ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php echo "$".$subtotal; ?></td>
                  <td align="right"><?php echo "$".$impuesto; ?></td>
                  <td align="right"><?php echo "$".$retenido; ?></td>
                  <td align="right"><?php echo "$".$total; ?></td>
      
                </tr>
                <?php 
                  }
      
                  while($rowSQL28 = mysqli_fetch_array($runSQL28)){
                      $cveProdServ = $rowSQL28['prodserv33'];
      
                      $detalle = $rowSQL28['Detalle'];
    
                      $descuento = $rowSQL28['Descuento'];
                      $descuento=number_format($descuento, 2);
    
                      if($cont==0 && $rowsNum!=0){
                        ?>
                        <tr>
                  <td align="center"><?php echo $uuid; ?></td>
                  <td align="center"><?php echo $serie; ?></td>
                  <td align="center"><?php echo $folio; ?></td>
                  <td align="center"><?php echo $moneda; ?></td>
                  <td align="center"><?php echo "$".$tipoCambio; ?></td>
                  <td align="center"><?php echo $metodoID2; ?></td>
                  <td align="center"><?php echo $fechaEmision; ?></td>
                  <td align="center"><?php echo $fechaTimbrado; ?></td>
                  <td align="center"><?php echo $rfcEmisor; ?></td>
                  <td align="center"><?php echo $razonEmisor; ?></td>
                  <td align="center"><?php echo $rfcReceptor; ?></td>
                  <td align="center"><?php echo $razonReceptor; ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php echo $cveProdServ;  ?></td>
                  <td align="right"><?php echo $detalle;  ?></td>
                  <td align="right"><?php echo "$".$descuento;  ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php echo "$".$subtotal; ?></td>
                  <td align="right"><?php echo "$".$impuesto; ?></td>
                  <td align="right"><?php echo "$".$retenido; ?></td>
                  <td align="right"><?php echo "$".$total; ?></td>
                  
                  
      
                </tr>
                <?php
                $cont++;
                
                      }
                      if($cont>0 && $rowsNum>1){
                        ?>
                        <tr>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <td align="center"><?php ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php echo $cveProdServ;  ?></td>
                  <td align="right"><?php echo $detalle;  ?></td>
                  <td align="right"><?php echo "$".$descuento;  ?></td>
                  <!--td align="right"></td-->
                  <td align="right"><?php ?></td>
                  <td align="right"><?php ?></td>
                  <td align="right"><?php ?></td>
                  <td align="right"><?php ?></td>
                  
                  
      
                </tr>
                <?php
                $cont++;
                      }
      
                  }
              ?>
    
    
              <?php 
              }
              }


            }
						  
				     ?>

				<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
					</tbody>
				</table>
<?php
	  }
?>
