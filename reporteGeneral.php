<?php  
error_reporting(0);//NO REPORTAR ERRORES, NECESARIO PARA NO LLENAR EL EXCEL DE ERRORES CUANDO NO SE ENCUENTRA UN CAMPO
//Recibir variables
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$boton = $_POST["button"];
$id_cliente = $_POST['cliente'];


if($id_cliente!=0){
	$cntQuery="AND CargoAFactura_RID = ".$id_cliente."";
}else{$cntQuery="";}


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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="ReporteGeneral_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");




?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">

				<table class="table table-hover table-responsive table-condensed" border="1" id="table">
					<thead>
						<tr>
            <th class="input">FOLIO FACTURA</th>
            <th class="input">UUID</th>
            <th class="input">FECHA</th>
            <th class="input">CLIENTE</th>
            <th class="input">DESTINATARIO</th>
            <th class="input">CALLE</th>
            <th class="input">CLAVE DE PRODUCTO</th>
            <th class="input">DESCRIPCION</th>
            <th class="input">PESO</th>
            <th class="input">PEDIMENTO</th>
            <th class="input">REFERENCIA</th>
            <th class="input">FACTURA DETALLE</th>
            <th class="input">FLETE</th>
            <th class="input">AUTOPISTAS</th>
            <th class="input">CARGA</th>
            <th class="input">DESCARGA</th>
            <th class="input">COMISION POR SERVICIOS LOGISTICOS</th>
            <th class="input">DEMORAS</th>
            <th class="input">DESYCON</th>
            <th class="input">ESTADIAS</th>
            <th class="input">LAVADO</th>
            <th class="input">PESAJE</th>
            <th class="input">RECOLECCION</th>
            <th class="input">REPARTO</th>
            <th class="input">SEGURO</th>
            <th class="input">SERVICIOS LOGISTICOS</th>
            <th class="input">TRASLADO LOCAL</th>
            <th class="input">OTROS</th>
            <th class="input">SUBTOTAL</th>
            <th class="input">IVA 16%</th>
            <th class="input">RETENCION 4%</th>
            <th class="input">TOTAL</th>
            <th class="input">OPERADOR</th>
            <th class="input">UNIDAD</th>
            <th class="input">REMOLQUE</th>
            <th class="input">DOLLY</th>
            <th class="input">REMOLQUE2</th>
            <th class="input">OBSERVACIONES</th>
			  
			  

						</tr>
					</thead>
					<tbody>
					
          <?php

					//Agrupar por REMISION
					$resSQL01 = "SELECT DISTINCT * FROM ".$prefijobd."Factura WHERE cfdiuuid IS NOT NULL AND cCanceladoT IS NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' ".$cntQuery." ORDER BY Creado";
					$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
					while($rowSQL01 = mysql_fetch_array($runSQL01)){
            $IDfact = $rowSQL01['ID'];
            $xfolio = $rowSQL01['XFolio'];
            $uuid = $rowSQL01['cfdiuuid'];
            $fechaTimbrado = $rowSQL01['cfdifechaTimbrado'];
            $clienteID = $rowSQL01['CargoAFactura_RID'];
            $resSQL06 ="SELECT * FROM ".$prefijobd."Clientes WHERE ID = ".$clienteID.";";
            $runSQL06 = mysql_query($resSQL06, $cnx_cfdi);
            while($rowSQL06 = mysql_fetch_array($runSQL06)){
                $cliente = $rowSQL06['RazonSocial'];
            }

            $destinatario = $rowSQL01['Destinatario'];
            $destinatarioCalle = $rowSQL01['DestinatarioCalle'];

            /*//////////////////////////////////////////////////////////////////*/

            $resSQL07 ="SELECT * FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND Detalle IS NOT NULL AND ConceptoPartida='FLETE';";
            $runSQL07 = mysql_query($resSQL07, $cnx_cfdi);
            while($rowSQL07 = mysql_fetch_array($runSQL07)){
                $partidaDetalle = $rowSQL07['Detalle'];
            }

            $resSQL08 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='FLETE';";
            $runSQL08 = mysql_query($resSQL08, $cnx_cfdi);
            while($rowSQL08 = mysql_fetch_array($runSQL08)){
                $flete = $rowSQL08['SUM(PrecioUnitario)'];
            }
            $flete=number_format($flete, 2);

            $resSQL09 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='AUTOPISTAS';";
            $runSQL09 = mysql_query($resSQL09, $cnx_cfdi);
            while($rowSQL09 = mysql_fetch_array($runSQL09)){
                $autopistas = $rowSQL09['SUM(PrecioUnitario)'];
            }
            $autopistas=number_format($autopistas, 2);

            $resSQL10 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='CARGA';";
            $runSQL10 = mysql_query($resSQL10, $cnx_cfdi);
            while($rowSQL10 = mysql_fetch_array($runSQL10)){
                $carga = $rowSQL10['SUM(PrecioUnitario)'];
            }
            $carga=number_format($carga, 2);

            $resSQL11 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='DESCARGA';";
            $runSQL11 = mysql_query($resSQL11, $cnx_cfdi);
            while($rowSQL11 = mysql_fetch_array($runSQL11)){
                $descarga = $rowSQL11['SUM(PrecioUnitario)'];
            }
            $descarga=number_format($descarga, 2);

            $resSQL12 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='COMISION POR SERVICIOS LOGÍSTICOS';";
            $runSQL12 = mysql_query($resSQL12, $cnx_cfdi);
            while($rowSQL12 = mysql_fetch_array($runSQL12)){
                $comision = $rowSQL12['SUM(PrecioUnitario)'];
            }
            $comision=number_format($comision, 2);

            $resSQL13 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='DEMORAS';";
            $runSQL13 = mysql_query($resSQL13, $cnx_cfdi);
            while($rowSQL13 = mysql_fetch_array($runSQL13)){
                $demoras = $rowSQL13['SUM(PrecioUnitario)'];
            }
            $demoras=number_format($demoras, 2);

            $resSQL14 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='DESYCON';";
            $runSQL14 = mysql_query($resSQL14, $cnx_cfdi);
            while($rowSQL14 = mysql_fetch_array($runSQL14)){
                $desycon = $rowSQL14['SUM(PrecioUnitario)'];
            }
            $desycon=number_format($desycon, 2);

            $resSQL15 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='ESTADIAS';";
            $runSQL15 = mysql_query($resSQL15, $cnx_cfdi);
            while($rowSQL15 = mysql_fetch_array($runSQL15)){
                $estadias = $rowSQL15['SUM(PrecioUnitario)'];
            }
            $estadias=number_format($estadias, 2);

            $resSQL16 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='LAVADO';";
            $runSQL16 = mysql_query($resSQL16, $cnx_cfdi);
            while($rowSQL16 = mysql_fetch_array($runSQL16)){
                $lavado = $rowSQL16['SUM(PrecioUnitario)'];
            }
            $lavado=number_format($lavado, 2);

            $resSQL17 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='PESAJE';";
            $runSQL17 = mysql_query($resSQL17, $cnx_cfdi);
            while($rowSQL17 = mysql_fetch_array($runSQL17)){
                $pesaje = $rowSQL17['SUM(PrecioUnitario)'];
            }
            $pesaje=number_format($pesaje, 2);

            $resSQL18 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='RECOLECCION';";
            $runSQL18 = mysql_query($resSQL18, $cnx_cfdi);
            while($rowSQL18 = mysql_fetch_array($runSQL18)){
                $recoleccion = $rowSQL18['SUM(PrecioUnitario)'];
            }
            $recoleccion=number_format($recoleccion, 2);

            $resSQL19 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='REPARTO';";
            $runSQL19 = mysql_query($resSQL19, $cnx_cfdi);
            while($rowSQL19 = mysql_fetch_array($runSQL19)){
                $reparto = $rowSQL19['SUM(PrecioUnitario)'];
            }
            $reparto=number_format($reparto, 2);

            $resSQL20 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='SEGURO';";
            $runSQL20 = mysql_query($resSQL20, $cnx_cfdi);
            while($rowSQL20 = mysql_fetch_array($runSQL20)){
                $seguro = $rowSQL20['SUM(PrecioUnitario)'];
            }
            $seguro=number_format($seguro, 2);

            $resSQL21 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='SERVICIOS LOGÍSTICOS';";
            $runSQL21 = mysql_query($resSQL21, $cnx_cfdi);
            while($rowSQL21 = mysql_fetch_array($runSQL21)){
                $servLogisticos = $rowSQL21['SUM(PrecioUnitario)'];
            }
            $servLogisticos=number_format($servLogisticos, 2);

            $resSQL22 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='TRASLADO LOCAL';";
            $runSQL22 = mysql_query($resSQL22, $cnx_cfdi);
            while($rowSQL22 = mysql_fetch_array($runSQL22)){
                $trasladoLocal = $rowSQL22['SUM(PrecioUnitario)'];
            }
            $trasladoLocal=number_format($trasladoLocal, 2);

            $resSQL23 ="SELECT SUM(PrecioUnitario) FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID=".$IDfact." AND ConceptoPartida='OTROS';";
            $runSQL23 = mysql_query($resSQL23, $cnx_cfdi);
            while($rowSQL23 = mysql_fetch_array($runSQL23)){
                $otros = $rowSQL23['SUM(PrecioUnitario)'];
            }
            $otros=number_format($otros, 2);

            $subtotal = $rowSQL01['zSubtotal'];
            $subtotal=number_format($subtotal, 2);

            $impuesto = $rowSQL01['zImpuesto'];
            $impuesto=number_format($impuesto, 2);

            $retenido = $rowSQL01['zRetenido'];
            $retenido=number_format($retenido, 2);

            $total = $rowSQL01['zTotal'];
            $total=number_format($total, 2);

            $operadorID = $rowSQL01['Operador_RID'];
            $resSQL05 ="SELECT * FROM ".$prefijobd."Operadores WHERE ID = ".$operadorID.";";
            $runSQL05 = mysql_query($resSQL05, $cnx_cfdi);
            while($rowSQL05 = mysql_fetch_array($runSQL05)){
                $operador = $rowSQL05['Operador'];
            }

            $unidadID = $rowSQL01['Unidad_RID'];
            $resSQL24 ="SELECT * FROM ".$prefijobd."Unidades WHERE ID = ".$unidadID.";";
            $runSQL24 = mysql_query($resSQL24, $cnx_cfdi);
            while($rowSQL24 = mysql_fetch_array($runSQL24)){
                $unidad = $rowSQL24['Unidad'];
            }

            $RemolqueID = $rowSQL01['Remolque_RID'];
            $resSQL25 ="SELECT * FROM ".$prefijobd."Unidades WHERE ID = ".$RemolqueID.";";
            $runSQL25 = mysql_query($resSQL25, $cnx_cfdi);
            while($rowSQL25 = mysql_fetch_array($runSQL25)){
                $Remolque = $rowSQL25['Unidad'];
            }

            $dollyID = $rowSQL01['Dolly_RID'];
            $resSQL26 ="SELECT * FROM ".$prefijobd."Unidades WHERE ID = ".$dollyID.";";
            $runSQL26 = mysql_query($resSQL26, $cnx_cfdi);
            while($rowSQL26 = mysql_fetch_array($runSQL26)){
                $dolly = $rowSQL26['Unidad'];
            }

            $remolque2ID = $rowSQL01['Remolque2_RID'];
            $resSQL27 ="SELECT * FROM ".$prefijobd."Unidades WHERE ID = ".$remolque2ID.";";
            $runSQL27 = mysql_query($resSQL27, $cnx_cfdi);
            while($rowSQL27 = mysql_fetch_array($runSQL27)){
                $remolque2 = $rowSQL27['Unidad'];
            }

            $comentarios = $rowSQL01['Comentarios'];
            $cont=0;

            $resSQL28 ="SELECT * FROM ".$prefijobd."FacturasSub WHERE FolioSub_RID = ".$IDfact.";";
            $runSQL28 = mysql_query($resSQL28, $cnx_cfdi);
            $rowsNum=mysql_num_rows($runSQL28);
            if($rowsNum==0){
              ?> 
              <tr>
						<td align="center"><?php echo $xfolio; ?></td>
						<td align="center"><?php echo $uuid; ?></td>
						<td align="center"><?php echo $fechaTimbrado; ?></td>
						<td align="center"><?php echo $cliente; ?></td>
						<td align="center"><?php echo $destinatario; ?></td>
						<td align="center"><?php echo $destinatarioCalle; ?></td>
						<!--td align="right"></td-->
						<td align="right"><?php   ?></td>
						<td align="right"><?php   ?></td>
						<td align="right"><?php   ?></td>
						<td align="right"><?php   ?></td>
						<td align="right"><?php   ?></td>
            <!--td align="right"></td-->
						<td align="right"><?php echo $partidaDetalle; ?></td>
						<td align="right"><?php echo $flete; ?></td>
						<td align="right"><?php echo $autopistas; ?></td>
						<td align="right"><?php echo $carga; ?></td>
						<td align="right"><?php echo $descarga; ?></td>
						<td align="right"><?php echo $comision; ?></td>
						<td align="right"><?php echo $demoras; ?></td>
						<td align="right"><?php echo $desycon; ?></td>
						<td align="right"><?php echo $estadias; ?></td>
						<td align="right"><?php echo $lavado; ?></td>
						<td align="right"><?php echo $pesaje; ?></td>
						<td align="right"><?php echo $recoleccion; ?></td>
						<td align="right"><?php echo $reparto; ?></td>
						<td align="right"><?php echo $seguro; ?></td>
						<td align="right"><?php echo $servLogisticos; ?></td>
						<td align="right"><?php echo $trasladoLocal; ?></td>
						<td align="right"><?php echo $otros; ?></td>
						<td align="right"><?php echo $subtotal; ?></td>
						<td align="right"><?php echo $impuesto; ?></td>
						<td align="right"><?php echo $retenido; ?></td>
						<td align="right"><?php echo $total; ?></td>
						<td align="right"><?php echo $operador; ?></td>
						<td align="right"><?php echo $unidad; ?></td>
						<td align="right"><?php echo $Remolque; ?></td>
						<td align="right"><?php echo $dolly; ?></td>
						<td align="right"><?php echo $remolque2; ?></td>
						<td align="right"><?php echo $comentarios; ?></td>
						
						

          </tr>
          <?php 
            }

            while($rowSQL28 = mysql_fetch_array($runSQL28)){
                $cveProdServID = $rowSQL28['ClaveProdServCP_RID'];

                $resSQL29 ="SELECT * FROM ".$prefijobd."c_ClaveProdServCP WHERE ID = ".$cveProdServID.";";
                $runSQL29 = mysql_query($resSQL29, $cnx_cfdi);
                while($rowSQL29 = mysql_fetch_array($runSQL29)){
                    $cveProdServ = $rowSQL29['ClaveProducto'];
                }

                $descripcion = $rowSQL28['Descripcion'];
                $peso = $rowSQL28['Peso'];
                $peso=number_format($peso, 2);
                $pedimento = $rowSQL28['NumeroPedimento'];
                $referencia = $rowSQL28['Referencia'];
                if($cont==0 && $rowsNum!=0){
                  ?>
                  <tr>
						<td align="center"><?php echo $xfolio; ?></td>
						<td align="center"><?php echo $uuid; ?></td>
						<td align="center"><?php echo $fechaTimbrado; ?></td>
						<td align="center"><?php echo $cliente; ?></td>
						<td align="center"><?php echo $destinatario; ?></td>
						<td align="center"><?php echo $destinatarioCalle; ?></td>
						<!--td align="right"></td-->
						<td align="right"><?php echo $cveProdServ; ?></td>
						<td align="right"><?php echo $descripcion; ?></td>
						<td align="right"><?php echo $peso; ?></td>
						<td align="right"><?php echo $pedimento; ?></td>
						<td align="right"><?php echo $referencia; ?></td>
            <!--td align="right"></td-->
						<td align="right"><?php echo $partidaDetalle; ?></td>
						<td align="right"><?php echo $flete; ?></td>
						<td align="right"><?php echo $autopistas; ?></td>
						<td align="right"><?php echo $carga; ?></td>
						<td align="right"><?php echo $descarga; ?></td>
						<td align="right"><?php echo $comision; ?></td>
						<td align="right"><?php echo $demoras; ?></td>
						<td align="right"><?php echo $desycon; ?></td>
						<td align="right"><?php echo $estadias; ?></td>
						<td align="right"><?php echo $lavado; ?></td>
						<td align="right"><?php echo $pesaje; ?></td>
						<td align="right"><?php echo $recoleccion; ?></td>
						<td align="right"><?php echo $reparto; ?></td>
						<td align="right"><?php echo $seguro; ?></td>
						<td align="right"><?php echo $servLogisticos; ?></td>
						<td align="right"><?php echo $trasladoLocal; ?></td>
						<td align="right"><?php echo $otros; ?></td>
						<td align="right"><?php echo $subtotal; ?></td>
						<td align="right"><?php echo $impuesto; ?></td>
						<td align="right"><?php echo $retenido; ?></td>
						<td align="right"><?php echo $total; ?></td>
						<td align="right"><?php echo $operador; ?></td>
						<td align="right"><?php echo $unidad; ?></td>
						<td align="right"><?php echo $Remolque; ?></td>
						<td align="right"><?php echo $dolly; ?></td>
						<td align="right"><?php echo $remolque2; ?></td>
						<td align="right"><?php echo $comentarios; ?></td>
						
						

          </tr>
          <?php
          $cont++;
          
                }
                if($cont>0 && $rowsNum>1){
                  ?>
                  <tr>
						<td align="center"><?php  ?></td>
						<td align="center"><?php  ?></td>
						<td align="center"><?php  ?></td>
						<td align="center"><?php  ?></td>
						<td align="center"><?php  ?></td>
						<td align="center"><?php  ?></td>
						<!--td align="right"></td-->
						<td align="right"><?php echo $cveProdServ; ?></td>
						<td align="right"><?php echo $descripcion; ?></td>
						<td align="right"><?php echo $peso; ?></td>
						<td align="right"><?php echo $pedimento; ?></td>
						<td align="right"><?php echo $referencia; ?></td>
            <!--td align="right"></td-->
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						<td align="right"><?php  ?></td>
						
						

          </tr>
          <?php
          $cont++;
                }

            }

          }
						}
            ?> 
					
					 
					 
				<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
					</tbody>
				</table>


