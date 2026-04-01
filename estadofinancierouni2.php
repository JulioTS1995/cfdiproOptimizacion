<?php
    set_time_limit(355);
    require_once('../connections/cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);

    if (isset($_POST['desde']))
		  $fechad = $_POST['desde'];

    if (isset($_POST['hasta']))
		  $fechah = $_POST['hasta'];

    if(isset($_POST['base']))
      $base = $_POST['base'];

    if(isset($_POST['vista']))
      $vista = $_POST['vista'];

    if (isset($_POST['unidad']))
		 $unidad = $_POST['unidad'];

    if ($unidad == 0)
    	$CondicionUnidad =";";
    else
	$CondicionUnidad=" WHERE a.ID=".$unidad.";";

  //$resSQL1 = "SELECT a.id,a.Unidad,a.Porcen,a.NoUni,'".$fechad."' as fechad,'".$fechah."' as fechah FROM unidades_activas as a";

	$resSQL1 = "SELECT a.id,a.Unidad FROM ".$base."unidades as a".$CondicionUnidad;
	//echo "<p>$resSQL1</p>";
	$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
	$pconsulta = mysql_query($resSQL1, $cnx_cfdi);

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Estado Financiero Unidades</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width", content="initial-scale=1">

    <script src="http://code.jquery.com/jquery-latest.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>

    <link href="../bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="../bootstrap/css/flujoefectivo.css" rel="stylesheet">
</head>

<body>

  <section id="nCuenta">
         <p></p>
    	   <a id="a1" href="estadofinancierounigeneradorcsv.php?archivo=C:\\xampp\\htdocs\\ff\\EstadoFinancieroUnidades\\EstadoFinancieroUni.csv
               &consulta=<?php echo $resSQL1; ?>&base=<?php echo $base?>&vista=<?php echo $vista?>&unidad=<?php $unidad?>" class="btn btn-info btn-primary btn-sm">Exportar CSV
             </a>
         <p></p>
        <center><h3>ESTADO FINANCIERO UNIDADES</h3></center>
         <p></p>
      </section>

  <div id="div2">
      <center>
        <table class="table1 table-striped table-bordered table-condensed table-hover">
         <thead>
            <tr>
              <th>Unidad</th>
			  <th>Clase</th>
              <th>Facturacion</th>
              <!--<th>ComisionesVentas</th>-->
              <th>SueldoOper</th>
              <th>Combustible</th>
              <th>Casetas</th>
              <th>Mantenimiento</th>
              <th>Rastreo</th>
              <!--<th>ViaticosOper</th>-->
              <th>Pension</th>
              <th>Multas</th>
              <th>Maniobras</th>
              <!--<th>Permisionarios</th>-->
			  <th>Margen de Contribucion</th>
            </tr>
        </thead>

        <tbody>

      <?php
    	$rowSQL1 = mysql_fetch_assoc($runSQL1);
      ?>
      <?php do { ?>
      <tr>
        <td><?php echo utf8_encode($rowSQL1['Unidad']); ?></td>
		<td><?php echo utf8_encode($rowSQL1['Clase']); ?></td>

    	<?php
			$renglontotal=0;
    		$resSQL2 = "SELECT Round(Sum(IF(a.zSubTotalConvertido is null,0,a.zSubTotalConvertido)),2) as Facturacion FROM ".$base."_remisiones as a,".$base."_factura as b WHERE a.sefacturoen=b.xfolio AND (a.cCanceladoT IS NULL OR a.cCanceladoT='') AND a.zsubtotalconvertido > 0 AND a.Unidad_RID=".$rowSQL1['id']." AND DATE(b.creado) BETWEEN '".$fechad."' AND '".$fechah."' AND (b.cCanceladoT IS NULL OR b.cCanceladoT='') AND (b.Oficina_RID=12763 OR b.Oficina_RID=16985);";//",Round(if(Sum(a.comision) is null,0,Sum(a.comision)),2) as ComisionesVentas";
    		//$resSQL2 = $resSQL2." FROM (SELECT if(b.zSubTotalConvertido IS NULL,0,b.zSubTotalConvertido) as subtotal,if((b.zSubTotalConvertido*(b.porcentajecomision/100)) is null,0,(b.zSubTotalConvertido*(b.porcentajecomision/100))) as comision FROM ".$base."_remisiones as b,".$base."_factura as c WHERE b.sefacturoen=c.xfolio AND (b.cCanceladoT IS NULL OR b.cCanceladoT='') AND (b.Porcentajecomision > 0 or b.zsubtotalconvertido > 0) AND b.Unidad_RID=".$rowSQL1['id']." AND DATE(c.creado) BETWEEN '".$fechad."' AND '".$fechah."' AND (c.cCanceladoT IS NULL OR c.cCanceladoT='') AND (c.Oficina_RID=12763 OR c.Oficina_RID=16985)) as a;";
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
    		$rowSQL2 = mysql_fetch_assoc($runSQL2);
			$RFacturacion=Round($rowSQL2['Facturacion'],2);
			$renglontotal=$renglontotal+$RFacturacion;
    	?>
        <td><?php echo number_format($RFacturacion,2); ?></td>
        <!--<td><?php //echo number_format($rowSQL2['ComisionesVentas'],2); ?></td>-->

    	<?php
    		$resSQL3 = "SELECT Round(SUM(if(b.zCombustibleIVAb IS NULL,0,b.zCombustibleIVAb)),2) as Combustible FROM ".$base."_liquidaciones as b WHERE b.zCombustibleIVAb>0 AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		//$resSQL3 = $resSQL3." FROM (SELECT if(b.zCombustibleIVAb IS NULL,0,b.zCombustibleIVAb) as comb FROM ".$base."_liquidaciones as b WHERE b.zCombustibleIVAb>0 AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].") as a;";
    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
    		$rowSQL3 = mysql_fetch_assoc($runSQL3);

    		$resSQL9 = "SELECT Round(SUM(if(b.SubTotalComb IS NULL,0,b.SubTotalComb)),2) as GCombustible FROM ".$base."_GastosViajes as b WHERE b.TipoVale='Combustible' AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Completado' AND b.Unidad_RID=".$rowSQL1['id'].";";
    		//$resSQL9 = $resSQL9." FROM (SELECT if(b.SubTotalComb IS NULL,0,b.SubTotalComb) as GComb FROM ".$base."_GastosViajes as b WHERE b.TipoVale='Combustible' AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Completado' AND b.Unidad_RID=".$rowSQL1['id'].") as a;";
    		$runSQL9 = mysql_query($resSQL9, $cnx_cfdi);
    		$rowSQL9 = mysql_fetch_assoc($runSQL9);

    		$resSQL4 = "SELECT Round(SUM(if(b.yComisionOperador IS NULL,0,b.yComisionOperador)),2) as Sueldo,Round(SUM(if(b.ySueldoGarantia IS NULL,0,b.ySueldoGarantia)),2) as SueldoG FROM ".$base."_liquidaciones as b WHERE DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id']."";//b.rosubtotal<>0 AND 
    		//$resSQL4 = $resSQL4." FROM (SELECT if(b.rosubtotal IS NULL,0,b.rosubtotal) as sueldo FROM ".$base."_liquidaciones as b WHERE b.rosubtotal<>0 AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].") as a;";
    		$runSQL4 = mysql_query($resSQL4, $cnx_cfdi);
    		$rowSQL4 = mysql_fetch_assoc($runSQL4);
			$RSueldo=Round($rowSQL4['Sueldo']+$rowSQL4['SueldoG'],2);
			$RCombustible=Round($rowSQL3['Combustible']+$rowSQL9['GCombustible'],2);
			$renglontotal=$renglontotal-$RSueldo-$RCombustible;
    	?>
        <td><?php echo number_format($RSueldo,2); ?></td>
        <td><?php echo number_format($RCombustible,2); ?></td>

    	<?php
    		$resSQL5 = "SELECT Round(SUM(If(b.zCasetasIVAb IS NULL,0,b.zCasetasIVAb)),2) as Casetas";
    		$resSQL5 = $resSQL5." FROM ".$base."_liquidaciones as b WHERE b.zCasetasIVAb>0 AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND b.Estatus='Sellada' AND b.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		//$resSQL5 = $resSQL5." WHERE (b.Liquidacion IS NOT NULL OR b.Liquidacion='') and b.Unidad_RID=".$rowSQL1['id']." AND DATE(b.Fecha) BETWEEN '".$fechad."' AND '".$fechah."';";
    		$runSQL5 = mysql_query($resSQL5, $cnx_cfdi);
    		$rowSQL5 = mysql_fetch_assoc($runSQL5);

    		$resSQL6 = "SELECT Round(SUM(If(b.gimporte IS NULL,0,b.gimporte)),2) as Casetas";
    		$resSQL6 = $resSQL6." FROM ".$base."_iave as b";
    		$resSQL6 = $resSQL6." WHERE b.bUnidad='".$rowSQL1['Unidad']."' AND DATE(b.cfecha) BETWEEN '".$fechad."' AND '".$fechah."';";
    		$runSQL6 = mysql_query($resSQL6, $cnx_cfdi);
    		$rowSQL6 = mysql_fetch_assoc($runSQL6);
			$RCasetas=Round(($rowSQL6['Casetas']+$rowSQL5['Casetas'])/1.16,2);
			$renglontotal=$renglontotal-$RCasetas;
    	?>
        <td><?php echo number_format($RCasetas,2); ?></td>

    	<?php
    		$resSQL11 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMP,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DES";
    		$resSQL11 = $resSQL11." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsMantenimiento='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
    		$runSQL11 = mysql_query($resSQL11, $cnx_cfdi);
    		$rowSQL11 = mysql_fetch_assoc($runSQL11);

    		$resSQL12 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
    		$resSQL12 = $resSQL12." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsRastreo='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
    		$runSQL12 = mysql_query($resSQL12, $cnx_cfdi);
    		$rowSQL12 = mysql_fetch_assoc($runSQL12);
			$RMantenimiento=Round($rowSQL11['IMP']-$rowSQL11['DES'],2);
			$RRastreo=Round($rowSQL12['IMPO']-$rowSQL12['DESO'],2);
			$renglontotal=$renglontotal-$RMantenimiento-$RRastreo;
    	?>
    	  <td><?php echo number_format($RMantenimiento,2); ?></td>
        <td><?php echo number_format($RRastreo,2); ?></td>

    	<?php
        $resSQL13 = "SELECT Round(SUM(IF(a.Importe IS NULL,0,a.Importe)),2) as IMPO,Round(SUM(IF(a.Descuento IS NULL,0,a.Descuento)),2) as DESO";
        $resSQL13 = $resSQL13." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsPension='1' AND a.Unidad_RID=".$rowSQL1['id'].";";
        $runSQL13 = mysql_query($resSQL13,$cnx_cfdi);
        $rowSQL13 = mysql_fetch_assoc($runSQL13);

    		$resSQL7 = "SELECT Round(SUM(If(a.zPensionIVAb is null,0,a.zPensionIVAb)),2) as Pension";
    		$resSQL7 = $resSQL7." FROM ".$base."_liquidaciones as a WHERE a.zPensionIVAb>0 AND DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL7 = mysql_query($resSQL7, $cnx_cfdi);
    		$rowSQL7 = mysql_fetch_assoc($runSQL7);
			$RPension=Round($rowSQL7['Pension']+($rowSQL13['IMPO']-$rowSQL13['DESO']),2);
			$renglontotal=$renglontotal-$RPension;
    	?>
        <!-- <td><?php //echo number_format($rowSQL7['Viaticos1']+$rowSQL7['Viaticos2']+$rowSQL7['Viaticos3'],2); ?></td>-->
        <td><?php echo number_format($RPension,2); ?></td>

    	<?php
    		$resSQL8 = "SELECT Round(SUM(If(a.zFederalesIVAb IS NULL,0,a.zFederalesIVAb)),2) as Multas,Round(if(SUM(a.zManiobrasIVAb) IS NULL,0,SUM(a.zManiobrasIVAb)),2) as Maniobras";
    		$resSQL8 = $resSQL8." FROM ".$base."_liquidaciones as a WHERE DATE(a.Fecha) BETWEEN '".$fechad."' AND '".$fechah."' AND a.Estatus='Sellada' AND a.UnidadLiqui_RID=".$rowSQL1['id'].";";
    		$runSQL8 = mysql_query($resSQL8, $cnx_cfdi);
    		$rowSQL8 = mysql_fetch_assoc($runSQL8);

    		$resSQL10 = "SELECT Round(IF(SUM(a.Importe) IS NULL,0,SUM(a.Importe)),2) as IMP,Round(IF(SUM(a.Descuento) IS NULL,0,SUM(a.Descuento)),2) as DES FROM (SELECT if(Importe IS NULL,0,Importe) as Importe,if(Descuento IS NULL,0,Descuento) as Descuento";
    		$resSQL10 = $resSQL10." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.seprorratea='1') as a;";
    		$runSQL10 = mysql_query($resSQL10, $cnx_cfdi);
    		$rowSQL10 = mysql_fetch_assoc($runSQL10);
			$RMultas=Round($rowSQL8['Multas'],2);
			$RManiobras=Round($rowSQL8['Maniobras'],2);
			$renglontotal=$renglontotal-$RMultas-$RManiobras;
    	?>
        <td><?php echo number_format($RMultas,2); ?></td>
        <td><?php echo number_format($RManiobras,2); ?></td>
        <!-- <td width="60" class="table"><?php echo number_format((($rowSQL10['IMP']-$rowSQL10['DES'])*($rowSQL1['Porcen']/100))/$rowSQL1['NoUni'],2); ?></td> -->
		<td><?php echo number_format($renglontotal,2); ?></td>
      <?php
        $resSQL14 = "SELECT Round(IF(SUM(a.Importe) IS NULL,0,SUM(a.Importe)),2) as IMPO,Round(IF(SUM(a.Descuento) IS NULL,0,SUM(a.Descuento)),2) as DESO FROM (SELECT if(Importe IS NULL,0,Importe) as Importe,if(Descuento IS NULL,0,Descuento) as Descuento";
        $resSQL14 = $resSQL14." FROM ".$base."_comprassub as a Left Join ".$base."_Compras as b ON a.FolioSub_RID=b.ID Left Join ".$base."_Servicios as c ON a.ServicioA_RID=c.ID Left Join ".$base."_proveedores as d ON b.ProveedorNo_RID=d.ID WHERE (date(b.fecha) Between '".$fechad."' AND '".$fechah."') AND b.Estatus='Completado' AND c.EsFlete='1' AND d.Permisionario='1' AND a.Unidad_RID=".$rowSQL1['id'].") as a;";
        $runSQL14 = mysql_query($resSQL14, $cnx_cfdi);
        $rowSQL14 = mysql_fetch_assoc($runSQL14);
      ?>
        <!--<td><?php echo number_format($rowSQL14['IMPO']-$rowSQL14['DESO'],2); ?></td>-->
      </tr>
     <?php } while ($rowSQL1 = mysql_fetch_assoc($runSQL1)); ?>
     <?php
  mysql_close($cnx_cfdi);
  ?>
     </tbody>
    </table>
    </center>

  </div>
  <footer class="footer">
		<p align="center">Transportes JOW S.A. de C.V.</p>
	</footer>
</body>
</html>
