<?php 
ini_set('memory_limit', '2048M');
set_time_limit(0);
ini_set('max_execution_time', 2000); 

require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['prefijodb'])) {
    die("Falta el prefijo de la DDBB");
} 

if (!isset($_GET['tipo']) || empty($_GET['tipo'])) {
    $tipo = 'ADM';
} else {
    $tipo = $_GET['tipo'];
}


require_once ('cnx_cfdi.php');
require_once ('cnx_cfdi2.php');

$prefijobd= @mysqli_escape_string($cnx_cfdi2, $_GET['prefijodb'] );

$id_liquidacion= $_GET['id'];
//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

//$prefijobd = 'prueba_';
$prefijo = rtrim($prefijobd, "_");


//require_once('lib_mpdf/pdf/mpdf.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

//multiemisor
$resSQL00= "SELECT * FROM {$prefijobd}systemsettings";
$runSQL00 = mysqli_query($cnx_cfdi2, $resSQL00);
while ($rowSQL00 = mysqli_fetch_array($runSQL00)) {
    if (isset($rowSQL00['MultiEmisor'])) {
        $Multi = $rowSQL00['MultiEmisor'];
    }else {
        $Multi='0';
    }
}

//systmsettngs
$resSQL01 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL01 = mysqli_query($cnx_cfdi2 ,$resSQL01);
while($rowSQL01 = mysqli_fetch_array($runSQL01)){
    $RazonSocial = $rowSQL01['RazonSocial'];
    $RFC = $rowSQL01['RFC'];
    $Calle = $rowSQL01['Calle'];
    $NumeroExterior = $rowSQL01['NumeroExterior'];
    $NumeroInterior = $rowSQL01['NumeroInterior'];
    $Colonia = $rowSQL01['Colonia'];
    $CodigoPostal = $rowSQL01['CodigoPostal'];
    $Ciudad = $rowSQL01['Ciudad'];
    $Estado = $rowSQL01['Estado'];
    $Pais = $rowSQL01['Pais'];
    $xml_dir= $rowSQL01['xmldir'];
    $Regimen = $rowSQL01['Regimen'];
    if (isset( $rowSQL01['LiquidacionesMultipartidas'])) {
        $comprobadoMultip = intval($rowSQL01['LiquidacionesMultipartidas']);
    }else{
       $comprobadoMultip = 0;
    }
    $codLocalidad = '';
}


$resSQL02 = "SELECT
                    liq.OficinaLiquidacion_RID, 
                    liq.UnidadLiqui_RID, 
                    liq.UnidadThermo_RID, 
                    liq.UnidadThermo2_RID,
                    un.Unidad,
                    liq.OperadorLiqui_RID, 
                    op.Operador, 
                    liq.Desde, 
                    liq.Hasta, 
                    liq.DiasLaborados,  
                    liq.KmsInicial, 
                    liq.KmsCargado, 
                    liq.KmsFinal, 
                    liq.KmsVacio, 
                    liq.KmsRecorridos as KmsTotales, 
                    liq.Fecha as FechaFolioEnc, 
                    liq.XFolio, 
                    liq.aaRendimiento as Rendimientohead,                    
                    liq.bbTPrecioLitro,
                    liq.aaDieselVale,
                    liq.aaDieselEfectivo,
                    liq.aaTotalDiesel,
                    liq.aaPrecioLitro,
                    liq.aaDieselDescontar,
                    liq.aaDieselConsumido,
                    liq.aaDieselAutorizado,
                    liq.aaTotalDieselCompu,
                    liq.aaTotalDescontar,
                    liq.TotalFlete,                    
                    liq.rLtsCompuDiferncia, 
                    liq.roSubtotal, 
                    liq.rLitrosComb,
                    liq.roComprobo, 
                    liq.yDeposito,
                    liq.yImpuestos,
                    liq.ImporteCombFaltante,
                    liq.yPrestamos,
                    liq.zTotalD, 
                    liq.zCombustibleLitros,
                    liq.zCombustible,
                    liq.zPeaje,
                    liq.zComisionViajeA,
                    liq.yComisionOperador,
                    liq.zRefacciones,
                    liq.zReparaciones,
                    liq.zPension,
                    liq.zVarios,
                    liq.zTotal,
                    liq.zTotalAuto,
                    liq.OperadorDiferencia,
                   liq.Infonavit,
                    liq.ISR,
                    liq.IMSS,
                    op.Apodo
                    FROM {$prefijobd}liquidaciones as liq
                LEFT JOIN {$prefijobd}oficinas as of on liq.OficinaLiquidacion_RID = of.ID 
                LEFT JOIN {$prefijobd}unidades as un on liq.UnidadLiqui_RID = un.ID
                LEFT JOIN {$prefijobd}operadores as op on liq.OperadorLiqui_RID = op.ID
                WHERE liq.ID = {$id_liquidacion}";
$runSQL02 = mysqli_query($cnx_cfdi2, $resSQL02);
while ($rowSQL02 = mysqli_fetch_array($runSQL02)) {
            $lq_oficinaID= $rowSQL02['OficinaLiquidacion_RID'];
            $lq_unidad_id = $rowSQL02['UnidadLiqui_RID'];
            $lq_thermo1_id = $rowSQL02['UnidadThermo_RID'];
            $lq_thermo2_id = $rowSQL02['UnidadThermo2_RID'];
            $lq_unidad_name_head = $rowSQL02['Unidad'];
            $lq_operador_name_head = $rowSQL02['Operador'];
            $lq_operador_numero_head = $rowSQL02['Apodo'];
            $lq_desde_head = date("d-m-Y", strtotime($rowSQL02['Desde']));
            $lq_hasta_head = date("d-m-Y", strtotime($rowSQL02['Hasta']));
            $lq_dias_laborados_head = $rowSQL02['DiasLaborados'];
            $lq_km_inicial_head= number_format((float)$rowSQL02['KmsInicial'],2,'.',',');
            $lq_km_cargado_head= number_format((float)$rowSQL02['KmsCargado'],2,'.',',');
            $lq_km_final_head= number_format((float)$rowSQL02['KmsFinal'],2,'.',',');
            $lq_km_vacio_head= number_format((float)$rowSQL02['KmsVacio'],2,'.',',');
            $lq_km_recorridos_head = $lq_km_cargado_head + $lq_km_vacio_head;
            $lq_km_totales_head= number_format((float)$rowSQL02['KmsTotales'],2,'.',',');
            $lq_rendimiento_head = number_format((float)$rowSQL02['Rendimientohead'],2,'.',',');
            $lq_fecha_creado = $rowSQL02['FechaFolioEnc'];
            $lq_xfolio = $rowSQL02['XFolio'];
            $lq_rOp_lts_comb  =number_format((float)$rowSQL02['zCombustibleLitros'],2,'.',',');
            $lq_rOp_comb  = number_format((float)$rowSQL02['zCombustible'],2,'.',',');
            $lq_rOp_casetas  = number_format((float)$rowSQL02['zPeaje'],2,'.',',');
            $lq_rOp_refacciones  = number_format((float)$rowSQL02['zRefacciones'],2,'.',',');
            $lq_rOp_reparaciones  = number_format((float)$rowSQL02['zReparaciones'],2,'.',',');
            $lq_rOp_pension  = number_format((float)$rowSQL02['zPension'],2,'.',',');
            $lq_rOp_varios  = number_format((float)$rowSQL02['zVarios'],2,'.',',');
            $lq_rOp_total  = number_format((float)$rowSQL02['zTotal'],2,'.',',');
            $lq_cOp_comision_operador = number_format((float)$rowSQL02['yComisionOperador'],2,'.',',');
            $lq_cOp_comision_operadors = $rowSQL02['yComisionOperador'];
            $lq_cOp_comprobado = $rowSQL02['zTotalAuto'];
            $lq_cOp_no_comprobado = $rowSQL02['zTotalD'];            
            $lq_cOp_no_comprobado_n = number_format((float)$rowSQL02['zTotalD'],2,'.',',');            
            $lq_cOp_comporbado = number_format((float)$rowSQL02['zTotalAuto'],2,'.',',');
            $lq_cOp_total = number_format((float)$lq_cOp_comision_operadors + $lq_cOp_comprobado - $lq_cOp_no_comprobado,2,'.',',');
            $lq_cOp_deposito_raw = $rowSQL02['yDeposito'];
            $lq_cOp_deposito = number_format((float)$rowSQL02['yDeposito'],2,'.',',');
            $lq_cOp_impuestos = number_format((float)$rowSQL02['yImpuestos'],2,'.',',');
            $lq_cOp_comb_faltante = number_format((float)$rowSQL02['ImportecOpmbFaltante'],2,'.',',');
            $lq_cOp_prestamos = number_format((float)$rowSQL02['yPrestamos'],2,'.',',');
            $lq_cOp_infonavit =  $rowSQL02 ['Infonavit'];
            $lq_cOp_isr = $rowSQL02 ['ISR'];
            $lq_cOp_imss =  $rowSQL02 ['IMSS'];
            $lq_cOp_resultado_op = number_format((float)$rowSQL02['roSubtotal'],2,'.',',');
            $lq_rCo_lt_diesel_vale = number_format((float)$rowSQL02['aaDieselVale'],2,'.',',');
            $lq_rCo_lt_diesel_comprob = number_format((float)$rowSQL02['aaDieselEfectivo'],2,'.',',');
            $lq_rCo_total_lt_diesel = number_format((float)$rowSQL02['aaTotalDiesel'],2,'.',',');
            $lq_rCo_precio_lt = number_format((float)$rowSQL02['aaPrecioLitro'],2,'.',',');
            $lq_rCo_lt_diesel_disc = number_format((float)$rowSQL02['aaDieselDescontar'],2,'.',',');
            $lq_rCo_lt_diesel_consum = number_format((float)$rowSQL02['aaDieselConsumido'],2,'.',',');
            $lq_rCo_lt_margen = number_format((float)$rowSQL02['aaDieselAutorizado'],2,'.',',');
            $lq_rCo_lt_diesel_ecm = number_format((float)$rowSQL02['aaTotalDieselCompu'],2,'.',',');
            $lq_rCo_b_total_descontar = number_format((float)$rowSQL02['aaTotalDescontar'],2,'.',',');
            



}

if ($Multi == 1) {
    $resSQL03="SELECT of.ID, em.ID as emiID FROM {$prefijobd}oficinas as of
                    LEFT JOIN {$prefijobd}emisores as em on of.Emisor_RID = em.ID
                    WHERE of.ID = {$lq_oficinaID}";
    $runSQL03 = mysqli_query($cnx_cfdi2, $resSQL03);
    while ($rowSQL03 = mysqli_fetch_array($runSQL03)) {
        $emisorID = $rowSQL03['emiID'];
    }

}
if ($emisorID >=1) {
    $resSQL04 = "SELECT *  FROM {$prefijobd}emisores WHERE ID={$emisorID}";
	//echo $resSQL04;
	$runSQL04 = mysqli_query($cnx_cfdi2, $resSQL04);
	while($rowSQL04 = mysqli_fetch_array($runSQL04)){
		$RazonSocial = $rowSQL04['RazonSocial'];
		$Calle = $rowSQL04['Calle'];
		$NumeroExterior = $rowSQL04['NumeroExterior'];
		$NumeroInterior = $rowSQL04['NumeroInterior'];
		$Colonia = $rowSQL04['Colonia'];
		$CodigoPostal = $rowSQL04['CodigoPostal'];
		$Ciudad = $rowSQL04['Ciudad'];
		$Estado = $rowSQL04['Estado'];
		//$codLocalidad = $rowSQL04['codLocalidad'];
		$Telefono = $rowSQL04['Telefono'];
		$RFC = $rowSQL04['RFC'];
		$Pais = $rowSQL04['Pais'];
		$Municipio = $rowSQL04['Municipio'];
		$xml_dir= $rowSQL04['xmldir'];
		$Regimen = $rowSQL04['Regimen'];
		$PermisoSCT = $rowSQL04['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL04['TipoPermisoSCT'];
		$ruta_logo_multi= $rowSQL04['RutaLogo'];
		$codLocalidad = '';
		
	}
} else {
	$resSQL0 = "SELECT * FROM {$prefijobd}systemsettings";
	$runSQL0 = mysqli_query($cnx_cfdi2 ,$resSQL0);
	while($rowSQL0 = mysqli_fetch_array($runSQL0)){
		$RazonSocial = $rowSQL0['RazonSocial'];
		$Calle = $rowSQL0['Calle'];
		$NumeroExterior = $rowSQL0['NumeroExterior'];
		$NumeroInterior = $rowSQL0['NumeroInterior'];
		$Colonia = $rowSQL0['Colonia'];
		$CodigoPostal = $rowSQL0['CodigoPostal'];
		$Ciudad = $rowSQL0['Ciudad'];
		$Estado = $rowSQL0['Estado'];
		//$codLocalidad = $rowSQL0['codLocalidad'];
		$Telefono = $rowSQL0['Telefono'];
		$RFC = $rowSQL0['RFC'];
		$Pais = $rowSQL0['Pais'];
		$Municipio = $rowSQL0['Municipio'];
		$xml_dir= $rowSQL0['xmldir'];
		$Regimen = $rowSQL0['Regimen'];
		$PermisoSCT = $rowSQL0['PermisoSCT'];
		$TipoPermisoSCT= $rowSQL0['TipoPermisoSCT'];
		$codLocalidad = '';
	}
}

$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';

if ($Multi == 1 ) {
   $rutalogo= $ruta_logo_multi;
}

$parametro_bgc = 921;
$resSQL921 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_bgc";
$runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
	 
while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
	$param= $rowSQL921['id2'];
	$color= $rowSQL921 ['VCHAR'];
}

$parametro_letra_color = 922;
$resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
$runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
	 
while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
	$param= $rowSQL922['id2'];
	$color_letra= $rowSQL922 ['VCHAR'];
}
//estilo de colores
$estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

if ($tipo == 'ADM') {
    $nombreLiquidacion = 'Liquidacion Administrativa';
} else {
    $nombreLiquidacion = 'Liquidacion Operador';
    
}


ob_start();    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?php echo $nombreLiquidacion.': '.$lq_xfolio?></title>
		<style>
			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
    		}
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: helvetica;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 0px; 
				font-family: helvetica;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: helvetica;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
</head>
<body>
<htmlpageheader name="myHeader">
    <div>
        <table border="0" style="margin:0; border-collapse: collapse; width: 100%;">
            <tr>
                <!-- LOGO IMG -->
                <td style="text-align:center; width:25%;">
                    <img src="<?php echo $rutalogo;?>" width="130px" alt=" "/>
                </td>

                <!-- INFORMACIÓN DE LA EMPRESA -->
                <td style="text-align:center; width:45%; font-size: 11px;">
                    <strong><?php echo $RazonSocial ?></strong> <br/>
                    <?php echo $RFC ?><br/>
                    <?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
                    <strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
                </td>

                <!-- CUADRO DE LIQUIDACIÓN -->
                <td style="text-align:right; width:30%; height:50px; font-size:10px; padding:5px;">
                    <table border="0" cellspacing="0" cellpadding="5" width="100%" 
                        style="border-collapse: separate; border: 1px solid rgb(255, 255, 255); border-radius: 15px; overflow: hidden;">
                        
                        <!-- FILA 1: LIQUIDACIÓN -->
                        <tr>
                            <td colspan="2" style="text-align: center; font-size: 14px; vertical-align: middle;
                                <?php echo $estilo_fondo; ?> border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <b><?php echo $nombreLiquidacion?></b><br><?php echo $lq_xfolio; ?>
                            </td>
                        </tr>

                        <!-- FILA 2: FECHA -->
                        <tr>
                            <td colspan="2" style="text-align: center; font-size: 10px; padding: 2px; vertical-align: middle;
                                background-color: #ffffff; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <b>Fecha</b><br><?php echo $lq_fecha_creado; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>
<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
        <htmlpagefooter name="myFooter">
        <p style="text-align:center;font-size: 13px;"><b>ATENTAMENTE <br> <?php echo $lq_operador_name_head?></b></p>
        </htmlpagefooter>
        <sethtmlpagefooter name="myFooter" value="on" show-this-page="all" />
    <main>
        <div>
				<table border="0" style="margin:0;border-collapse: collapse; padding-top:-20px;border: 1px solid rgba(128, 128, 128, 0.5);" width="100%">
					<tr style="margin:0; padding:0">
						<td style="text-align:left; width:10%; font-size: 10px;">
							<b>Unidad:</b> 
						</td>
						
						<td style="text-align:left; width:28%; font-size: 10px;">
                        <?php echo $lq_unidad_name_head; ?>
						</td>
                        <td style="text-align:left; width:13%;font-size: 10px;"><b>N.º Empleado:</b>
						</td>
                        <td style="text-align:left; width:15%;font-size: 10px;">
                            <?php if (is_numeric($lq_operador_numero_head) && $lq_operador_numero_head > 0){?>
                                <p><?php echo $lq_operador_numero_head; ?></p>
                            <?php }?>
                        </td>
                       
                        <td style="text-align:left; width:20%;font-size: 10px;">
						</td>
                        <td style="text-align:left; width:25%;font-size: 10px;">
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left; font-size: 10px;">
							<b>Operador:</b> 
						</td>
						
						<td style="text-align:left; font-size: 10px;">
                            <?php echo $lq_operador_name_head; ?>
						</td>
                        <td style="text-align:left; font-size: 10px;">
							<b>Km Inicial:</b> 

						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_inicial_head; ?>
						</td>
                        <td style="text-align:left; font-size: 10px;">
							<b>Km Cargado:</b> 

						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_cargado_head; ?>
						</td>
                        
                        
					
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 10px;">
							<b>Desde:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_desde_head; ?>
						</td>
						<td style="text-align:left; font-size: 10px;">
							<b>Km Final:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_final_head; ?>
						</td>
                        <td style="text-align:left; font-size: 10px;">
							<b>Km Vacio:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_vacio_head; ?>
						</td>
					</tr>
					<tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 10px;">
							<b>Hasta:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_hasta_head; ?>
						</td>
						<td style="text-align:left; font-size: 10px;">
							<b>Km Totales:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_totales_head; ?>
						</td>
                        <td style="text-align:left; font-size: 10px;">
							<b>Km Recorridos:</b> 
						</td>
                        <td style="text-align:left; font-size: 10px;"><?php echo $lq_km_recorridos_head; ?>
						</td>
					</tr>
                    <tr style="margin:0; padding:0">
						<td style="text-align:left;font-size: 10px;">
							<b>Dias:</b> 
						</td>
						<td style="text-align:left; font-size: 10px;">
							 <?php echo $lq_dias_laborados_head; ?>
						</td>
                        <td style="text-align:left; font-size: 10px;"><b>Rendimiento:</b>
						</td><td style="text-align:left; font-size: 10px;"><?php echo $lq_rendimiento_head; ?>
						</td><td style="text-align:left; font-size: 10px;">
						</td><td style="text-align:left; font-size: 10px;">
						
					</tr>
				</table>
        </div>
        
        <!-- Tabla viajes -->
        <div>
            <table border="0" style="margin-top:3px; border-collapse: collapse;" width="100%">
                <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                <tr>
                        
                        <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan=" <?php if ($tipo == 'ADM') { echo "6"; } else { echo "5";} ?>">Viajes Liquidados</th>
                    </tr>
                    <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                        <th style="text-align:center; width:2%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                            <b>Folio</b>
                        </th>
                        <th style="text-align:center; width:7%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                            <b>Carga</b>
                        </th>
                        <th style="text-align:center; width:35%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                            <b>Cliente</b>
                        </th>
                        <th style="text-align:center; width:45%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                            <b>Ruta</b>
                        </th>
                        <?php if ($tipo == 'ADM') { ?>
                            
                            <th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                                <b>Flete</b>
                            </th>
                       <?php } ?>
                        <th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);">
                            <b>Comision</b>
                        </th>
                      
                    </tr>

                </thead>
               <tbody>
                <?php
                $total_flete= 0;
                $total_comision =0;


            

                    $resSQL05="SELECT rem.XFolio, 
                                      lqs.FechaInicio, 
                                      lqs.Cliente, 
                                      lqs.Ruta, 
                                      lqs.Flete, 
                                      lqs.Subtotal, 
                                      lqs.Impuesto, 
                                      lqs.RetenidoMB, 
                                      lqs.Total,
                                      lqs.ComisionOperador,
                                      lqs.ComisionDescuentos,
                                      lqs.OtrosDescuentos
                                      FROM {$prefijobd}liquidacionessub AS lqs 
                                LEFT JOIN {$prefijobd}remisiones AS rem ON lqs.RemisionLiq_RID = rem.ID
                                WHERE FolioSub_RID ={$id_liquidacion}";
                    $runSQL05= mysqli_query($cnx_cfdi2, $resSQL05);
                    while ($rowSQL05 = mysqli_fetch_array($runSQL05)) {
                         $lqs_rem_folio = $rowSQL05['XFolio'];
                         $lqs_carga = date("d-m-Y", strtotime($rowSQL05 ['FechaInicio']));
                         $lqs_cliente= $rowSQL05['Cliente'];
                         $lqs_ruta = $rowSQL05 ['Ruta'];
                         $lqs_flete = number_format((float)$rowSQL05['Flete'],2,'.',',');
                         $lqs_fletes = $rowSQL05['Flete'];
                         $lqs_comDSC = $rowSQL05['ComisionDescuentos'];
                         $lqs_otrsDSC = $rowSQL05['OtrosDescuentos'];
                         $lqs_fletetotal = $lqs_fletes - $lqs_comDSC - $lqs_otrsDSC;
                         $lqs_comisions = $rowSQL05['ComisionOperador'];
                         $lqs_comision = number_format((float)$rowSQL05['ComisionOperador'],2,'.',',');
                            $total_flete += $lqs_fletetotal;
                            $total_comision += $lqs_comisions;

                        ?>
                    <tr>
						<td style="text-align:left; width:5%; font-size: 10px;padding-bottom:2px;"><?php echo $lqs_rem_folio; ?></td>
						<td style="text-align:center; width:7%; font-size: 10px;padding-bottom:2px;"><?php echo $lqs_carga; ?></td>
						<td style="text-align:left; width:25%; font-size: 9px;padding-bottom:2px;"><?php echo $lqs_cliente; ?></td>
						<td style="text-align:center; width:25%; font-size: 9px;padding-bottom:2px;"><?php echo $lqs_ruta; ?></td>
                        <?php if ($tipo == 'ADM') { ?>
						<td style="text-align:center; width:8%; font-size: 10px;padding-bottom:2px;"><?php echo "$ ".number_format((float)$lqs_fletetotal,2,'.',','); ?></td>
                        <?php } ?>
						<td style="text-align:center; width:8%; font-size: 10px;padding-bottom:2px;"><?php echo "$ ".$lqs_comision; ?></td>
					</tr>
                    <?php }  ?>
                    <tr>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;" ></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;" ></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;" ></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>" ><b>Total: </b></td>
                        <?php if ($tipo == 'ADM') { ?>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>" ><b><u><?php echo "$ ".number_format((float)$total_flete,2,'.',','); ?> </u></b></td>
                        <?php } ?>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>" ><b><u><?php echo "$ ".number_format((float)$total_comision,2,'.',','); ?></u></b></td>
                    </tr>
               </tbody>

            </table>
        </div>
         
         <!-- vales -->
        <div>
            <table border="0" style="margin-top:5px; border-collapse: collapse;" width="100%">
                <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                <tr>
                        <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan="6">Gastos</th>
                    </tr>
                    <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                        <th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Gasto</b></th>
                        <th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Fecha</b></th>
                        <th style="text-align:center; width:15%; font-size: 10px;vertical-align:center; padding-left:4px;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Tipo</b></th>
                        <th style="text-align:center; width:32%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Concepto</b></th>
                        <th style="text-align:center; width:20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Comentarios</b></th>
                        <th style="text-align:center; width:8%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Importe</b></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $filas_en_vales= 0;
                        $resSQL06 = "SELECT 
                                            gv.XFolio,
                                            lqg.Fecha,
                                            lqg.Tipo, 
                                            lqg.Concepto, 
                                            lqg.Comentarios, 
                                            lqg.Importe
                                     FROM {$prefijobd}liquidacionesgastos AS lqg 
                                     LEFT JOIN {$prefijobd}gastosviajes AS gv on lqg.GastosLiq_RID = gv.ID
                                     WHERE FolioSubGastos_RID = {$id_liquidacion} ORDER BY Tipo";
                        $runSQL06 = mysqli_query($cnx_cfdi2, $resSQL06);
                        while($rowSQL06 = mysqli_fetch_array($runSQL06 )){
                             $lq_v_gasto = $rowSQL06 ['XFolio'];
                             $lq_v_fecha = date("d-m-Y", strtotime($rowSQL06['Fecha']));
                             $lq_v_tipo = $rowSQL06['Tipo'];
                             $lq_v_concepto = $rowSQL06 ['Concepto'];
                             $lq_v_coments= $rowSQL06 ['Comentarios'];
                             $lq_v_importes= $rowSQL06['Importe'];
                             $lq_v_importe= number_format((float)$rowSQL06['Importe'],2,'.',',');
                             $total_gastos += $lq_v_importes;
                             $filas_en_vales ++;

                    ?>
                    <tr>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_v_gasto; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_v_fecha; ?></td>
						<td style="text-align:left; font-size: 10px;padding-left:25px;padding-bottom:2px;"><?php echo $lq_v_tipo; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_v_concepto; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_v_coments; ?></td>
						<td style="text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;"><?php echo "$ ".$lq_v_importe; ?></td>
					</tr>
                   
                      <?php  }
                    ?>
                     <tr>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:left; font-size: 10px;padding-left:25px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>"><b>Total Gastos:</b></td>
                        <td style= "text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;<?php echo $estilo_fondo; ?>"><b><u><?php echo "$ ".number_format((float)$total_gastos,2,'.',','); ?></u></b></td>
                    </tr>
                </tbody>
            </table>
<br>
        </div>
        
        <!-- Comprobacion -->
        <?php if ($comprobadoMultip >=1) { ?>
            <div>
                <table border="0" style="margin-top:5px; border-collapse: collapse;" width="100%">
                    <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                        <tr>
                            <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan="4">Comprobado Operador</th>
                        </tr>
                        <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                            <th style="text-align:center; width: 40%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Folio y Concepto</b></th>
                            <th style="text-align:center; width: 20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Depositado</b></th>
                            <th style="text-align:center; width: 20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Autorizado</b></th>
                            <th style="text-align:center; width: 20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Diferencia</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            
                            $filas_en_comp = 0;
                            $total_depositado = 0;
                            $total_autorizado = 0;
                            $total_diferencia = 0;
    
                            
    
                            $resSQL07 = "SELECT 
                                                
                                                clq.Concepto,
                                                lqcs.Deposito,
                                                lqcs.Autorizado,
                                                lqcs.Diferencia
                                                FROM {$prefijobd}liquidaciones as lq
                                                LEFT JOIN {$prefijobd}liquidacionescomprobadosub as lqcs on lqcs.FolioSub_RID = lq.ID 
                                                LEFT JOIN {$prefijobd}conceptosliquidaciones as clq on lqcs.Concepto_RID = clq.ID where lq.ID = {$id_liquidacion}";
                            $runSQL07= mysqli_query($cnx_cfdi2, $resSQL07);
                            if (!$runSQL07) {//debug
                                $mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
                                $mensaje .= 'Consulta completa: ' . $resSQL07;
                                die($mensaje);
                            }
                            
                            while($rowSQL07 = mysqli_fetch_array($runSQL07 )){
                                
                                $campo_concepto = $rowSQL07['Concepto'];
                                $campo_depositado = isset($rowSQL07['Deposito']) ? $rowSQL07['Deposito'] : 0;
                                $campo_autorizado = isset($rowSQL07['Autorizado']) ? $rowSQL07['Autorizado'] : 0;
                                $campo_diferencia = isset($rowSQL07['Diferencia']) ? $rowSQL07['Diferencia'] : 0;
                                $total_depositado += $campo_depositado;
                                $total_autorizado += $campo_autorizado;
                                $total_diferencia += $campo_diferencia;
                                $filas_en_comp ++;
                                
    
                               ?>
    
                                    <tr>
                                        <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo $campo_concepto; ?></td>
                                        <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo "$ ".number_format((float)$campo_depositado, 2,'.',','); ?></td>
                                        <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo "$ ".number_format((float)$campo_autorizado, 2,'.',','); ?></td>
                                        <td style="text-align:center; font-size: 10px;padding-bottom:5px; color: red;"><?php echo "$ ".number_format((float)$campo_diferencia, 2,'.',','); ?></td>
                                    </tr>
                                   
    
                            <?php 
                              
                              
                            }
                               
    
                            ?>
    
                           
                               
                    </tbody>
                    
                </table>
                                
            </div>
            <?php echo '<hr style="border: 1px solid black; margin: 10px 0;margin-top: 2px;">';?>
            <table>
                <tr>
                    <td style="text-align:center; font-size: 12px;padding-right:120px;">Totalizador:</td>
                    <td style="text-align:right; font-size: 12px;padding-right:15px;"><b>Total depositado $</b> <?php echo number_format($total_depositado, 2); ?></b></td>
                    <td style="text-align:right; font-size: 12px;padding-left:30px;"><b>Total diferencia $ <?php echo number_format($total_autorizado, 2); ?></b></td>
                    <td style="text-align:right; font-size: 12px;padding-left:40px; color: red;"><b>Total a Descontar $ <?php echo number_format($total_diferencia, 2); ?></b></td>
                </tr>
            </table>
      <?php  } else { ?>
         
        <div>
            <table border="0" style="margin-top:-10px; border-collapse: collapse;" width="100%">
                <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                    <tr>
                        <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan="4">Comprobado Operador</th>
                    </tr>
                    <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                        <th style="text-align:center; width:40%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Concepto</b></th>
                        <th style="text-align:center; width:20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Depositado</b></th>
                        <th style="text-align:center; width:20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Autorizado</b></th>
                        <th style="text-align:center; width:20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Diferencia</b></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $lqco_conceptos = [
                            'zPeaje' => 'Peaje',
                            'zRefacciones' => 'Refacciones',
                            'zReparaciones' => 'Reparaciones',
                            'zVarios' => 'Varios/otros',
                            'zAnticipoViaje' => 'Anticipo de Viaje',
                            'zAlimentos' => 'Alimentos',
                            'zBascula' => 'Báscula',
                            'zCasetas' => 'Casetas',
                            'zCombustibleDiesel' => 'Combustible Diesel',
                            'zCombustibleTicketCard' => 'Combustible Ticket Card',
                            'zComisionViaje' => 'Comisión de Viaje',
                            'zEstacionamiento' => 'Estacionamiento',
                            'zFitosanitarias' => 'Fitosanitarias',
                            'zGastos' => 'Gastos',
                            'zGratificaciones' => 'Gratificaciones',
                            'zHotel' => 'Hotel',
                            'zManiobras' => 'Maniobras',
                            'zPasajesTaxis' => 'Pasaje Taxis',
                            'zPension' => 'Pensión',
                            'zReparacionesTalachas' => 'Reparaciones Talachas',
                            'zTaller' => 'Taller',
                            'zTransito' => 'Tránsito',
                            'zVarios2' => 'Varios (telefono, regaderas, copias, comision bancaria e insumos)',
                            'zViaticos' => 'Viáticos'
                        ];
                        
                        $total_depositado = 0;
                        $total_autorizado = 0;
                        $total_diferencia = 0;

                        

                        $resSQL07 = "SELECT zPeaje, 
                                            zPeajeA, 
                                            zPeajeD, 
                                            zRefacciones, 
                                            zRefaccionesA, 
                                            zRefaccionesD, 
                                            zReparaciones,
                                            zReparacionesA,
                                            zReparacionesD,
                                            zVarios,
                                            zVariosA,
                                            zVariosD,
                                            zAnticipoViaje,
                                            zAnticipoViajeA,
                                            zAnticipoViajeD,
                                            zAlimentos,
                                            zAlimentosA,
                                            zAlimentosD,
                                            zBascula,
                                            zBasculaA,
                                            zBasculaD,
                                            zCasetas,
                                            zCasetasA,
                                            zCasetasD,
                                            zCombustibleDiesel,
                                            zCombustibleDieselA,
                                            zCombustibleDieselD,
                                            zCombustibleTicketCard,
                                            zCombustibleTicketCardA,
                                            zCombustibleTicketCardD,
                                            zComisionViaje,
                                            zComisionViajeA,
                                            zComisionViajeD,
                                            zEstacionamiento,
                                            zEstacionamientoA,
                                            zEstacionamientoD,
                                            zFitosanitarias,
                                            zFitosanitariasA,
                                            zFitosanitariasD,
                                            zGastos,
                                            zGastosA,
                                            zGastosD,
                                            zGratificaciones,
                                            zGratificacionesA,
                                            zGratificacionesD,
                                            zGratificacionesIVAa,
                                            zGratificacionesIVAb,
                                            zHotel,
                                            zHotelA,
                                            zHotelD,
                                            zManiobras,
                                            zManiobrasA,
                                            zManiobrasD,
                                            zPasajesTaxis,
                                            zPasajesTaxisA,
                                            zPasajesTaxisD,
                                            zPension,
                                            zPensionA,
                                            zPensionD,
                                            zReparacionesTalachas,
                                            zReparacionesTalachasA,
                                            zReparacionesTalachasD,
                                            zTaller,
                                            zTallerA,
                                            zTallerD,
                                            zTransito,
                                            zTransitoA,
                                            zTransitoD,
                                            zVarios2,
                                            zVarios2A,
                                            zVarios2D,
                                            zViaticos,
                                            zViaticosA,
                                            zViaticosD
                                            FROM {$prefijobd}liquidaciones where ID = {$id_liquidacion}";
                        $runSQL07= mysqli_query($cnx_cfdi2, $resSQL07);
                        if (!$runSQL07) {//debug
                            $mensaje  = 'Consulta no valida: ' . mysqli_error($cnx_cfdi2) . "\n";
                            $mensaje .= 'Consulta completa: ' . $resSQL07;
                            die($mensaje);
                        }
                        $rowSQL07 = mysqli_fetch_array($runSQL07);

                        foreach ($lqco_conceptos as $campo => $concepto) {
                            $campo_depositado = $campo;
                            $campo_autorizado = $campo . 'A';
                            $campo_diferencia = $campo . 'D';

                            $depositado     = isset($rowSQL07[$campo_depositado]) ? floatval($rowSQL07[$campo_depositado]) : 0;
                            $autorizado = isset($rowSQL07[$campo_autorizado]) ? floatval($rowSQL07[$campo_autorizado]) : 0;
                            $diferencia   = isset($rowSQL07[$campo_diferencia]) ? floatval($rowSQL07[$campo_diferencia]) : 0;

                            if ($depositado > 0 || $autorizado > 0 || $diferencia > 0) { ?>

                                <tr>
                                    <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo $concepto; ?></td>
                                    <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo "$ ".number_format($depositado, 2); ?></td>
                                    <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo "$ ".number_format($autorizado, 2); ?></td>
                                    <td style="text-align:center; font-size: 10px;padding-bottom:5px;"><?php echo "$ ".number_format($diferencia, 2); ?></td>
                                </tr>
                               

                        <?php 
                          }
                          $total_depositado += $depositado;
                          $total_autorizado += $autorizado;
                          $total_diferencia += $diferencia;
                        }
                           

                        ?>

                       
                           
                </tbody>
                
            </table>
                            
        </div>
        <?php echo '<hr style="border: 1px solid black; margin: 10px 0;margin-top: 2px;">';?>
        <table>
            <tr>
                <td style="text-align:center; font-size: 12px;padding-right:120px;">Totalizador:</td>
                <td style="text-align:right; font-size: 12px;padding-right:15px;"><b>Total depositado $</b> <?php echo number_format($total_depositado, 2); ?></b></td>
                <td style="text-align:right; font-size: 12px;padding-left:30px;"><b>Total diferencia $ <?php echo number_format($total_autorizado, 2); ?></b></td>
                <td style="text-align:right; font-size: 12px;padding-left:40px;"><b>Total a Descontar $ <?php echo number_format($total_diferencia, 2); ?></b></td>
            </tr>
        </table>
        
    <?php   } ?>
        <pagebreak /><sethtmlpageheader name="myHeader" value="on" show-this-page="all" />

        <?php
                        #casetas 
                        $resSQLc07 = "SELECT 
                                            lq.XFolio,
                                            ia.aCodigo,
                                            ia.cFecha,
                                            ia.dHora,
                                            ia.eCaseta,
                                            ia.gImporte,
                                            ia.Liquidacion
                                        FROM {$prefijobd}liquidaciones as lq 
                                        INNER JOIN {$prefijobd}iave as ia on lq.XFolio = ia.Liquidacion
                                        WHERE lq.ID = {$id_liquidacion}";
                        $runSQLc07 = mysqli_query($cnx_cfdi2, $resSQLc07);
                        $num_filas_c = mysqli_num_rows($runSQLc07);
                        if (isset($runSQLc07)) { ?>
                           <div style="page-break-inside: avoid; overflow: visible;">
                                <table  style="border-collapse: collapse; width: 100%; margin-top: 5px;" width="100%">
                                    <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                                    <tr>
                                            <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan="5">Casetas</th>
                                        </tr>
                                        <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                                            <th style="text-align:center; width:13%; font-size: 10px;vertical-align:center; padding-left:4px;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Codigo</b></th>
                                            <th style="text-align:center; width:10%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Fecha</b></th>
                                            <th style="text-align:center; width:10%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Hora</b></th>
                                            <th style="text-align:center; width:40%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Caseta</b></th>
                                            <th style="text-align:center; width:10%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Importe</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php   
                        while($rowSQLc07 = mysqli_fetch_array($runSQLc07 )){
                             $lq_c_Codigo = $rowSQLc07 ['aCodigo'];
                             $lq_c_Fecha = $rowSQLc07 ['cFecha'];
                             $lq_c_hora = $rowSQLc07 ['dHora'];
                             $lq_c_caseta = $rowSQLc07 ['eCaseta'];
                             $lq_c_importes= $rowSQLc07['gImporte'];
                             $lq_c_importe= number_format((float)$rowSQLc07['gImporte'],2,'.',',');
                             $total_casetas += $lq_c_importes;
                    ?>
                    <tr>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_c_Codigo; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_c_Fecha; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_c_hora; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_c_caseta; ?></td>
						<td style="text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;"><?php echo "$ ".$lq_c_importe; ?></td>
					</tr>
                   
                      <?php  }
                    ?>
                     <tr>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>"><b>Total Casetas:</b></td>
                        <td style= "text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;<?php echo $estilo_fondo; ?>"><b><u><?php echo "$ ".number_format((float)$total_casetas,2,'.',','); ?></u></b></td>
                    </tr>
                </tbody>
            </table>
                        
      
                    
    
        <?php }

                        #prestamos 
        
                        $resSQL06p = "SELECT pr.Folio,
                                            pr.Concepto, 
                                            pr.Importe, 
                                            lqpp.Importe as importeliq
                                        FROM {$prefijobd}liquidaciones as lq 
                                        LEFT JOIN {$prefijobd}liquidacionespagosparciales as lqpp on lq.ID = lqpp.FolioSubPagosPArciales_RID
                                        INNER JOIN {$prefijobd}prestamos as pr  on lqpp.FolioPagoParcialLiqui_RID = pr.ID where lq.ID = {$id_liquidacion}";
                        $runSQL06p = mysqli_query($cnx_cfdi2, $resSQL06p);
                        $num_filas = mysqli_num_rows($runSQL06p);
                        if ($runSQL06p && mysqli_num_rows($runSQL06p) > 0) {
                            $total_prestamos = 0; ?>
                           <div style="page-break-inside: avoid; overflow: visible;">
                                <table border="0" style="margin-top:5px; border-collapse: collapse; " width="100%">
                                    <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                                    <tr>
                                            <th style="text-align:center; width:100%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);" colspan="3">Prestamos</th>
                                        </tr>
                                        <tr style="border: 1px solid rgba(128, 128, 128, 0.5);">
                                            <th style="text-align:center; width:15%; font-size: 10px;vertical-align:center; padding-left:4px;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Folio</b></th>
                                            <th style="text-align:center; width:32%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Concepto</b></th>
                                            <th style="text-align:center; width:20%; font-size: 10px;vertical-align:center;<?php echo $estilo_fondo; ?>border: 1px solid rgba(128, 128, 128, 0.5);"><b>Importe</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php   
                        while($rowSQL06p = mysqli_fetch_array($runSQL06p )){
                             $lq_p_Folio = $rowSQL06p ['Folio'];
                             $lq_p_concepto = $rowSQL06p ['Concepto'];
                             $lq_p_importes= $rowSQL06p['importeliq'];
                             $lq_p_importe= number_format((float)$rowSQL06p['importeliq'],2,'.',',');
                             $total_prestamos += $lq_p_importes;
                    ?>
                    <tr>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_p_Folio; ?></td>
						<td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $lq_p_concepto; ?></td>
						<td style="text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;"><?php echo "$ ".$lq_p_importe; ?></td>
					</tr>
                   
                      <?php  }
                    ?>
                     <tr>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;"></td>
                        <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>"><b>Total Prestamos:</b></td>
                        <td style= "text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;<?php echo $estilo_fondo; ?>"><b><u><?php echo "$ ".number_format((float)$total_prestamos,2,'.',','); ?></u></b></td>
                    </tr>
                </tbody>
            </table>

        </div>

                   
        <?php   } 
         

                        #Impuestos 
                        $resSQL06im = "SELECT li.Importe, li.Periodo 
                        FROM {$prefijobd}liquidacionesimpuestos as li
                        WHERE li.FolioSubLiqImpuesto_RID = {$id_liquidacion}";
         $runSQL06im = mysqli_query($cnx_cfdi2, $resSQL06im);   
         
         $impuestos_validos = [];
         
         if ($runSQL06im) {
             while ($row = mysqli_fetch_array($runSQL06im)) {
                 // Verifica que al menos uno de los campos tenga datos
                 if (!is_null($row['Importe']) || !is_null($row['Periodo'])) {
                     $impuestos_validos[] = $row;
                 }
             }
         
             if (count($impuestos_validos) > 0) {
                 $total_impuestos = 0;
         ?>
                 <div>
                     <table border="0" style="border-collapse: collapse; width: 100%; margin-top: 5px;" width="100%">
                         <thead style="border: 1px solid rgba(128, 128, 128, 0.5);">
                             <tr>
                                 <th colspan="2" style="text-align:center; font-size: 10px;<?php echo $estilo_fondo; ?> border: 1px solid rgba(128, 128, 128, 0.5);">Impuestos</th>
                             </tr>
                             <tr>
                                 <th style="text-align:center; width:50%; font-size: 10px;<?php echo $estilo_fondo; ?> border: 1px solid rgba(128, 128, 128, 0.5);"><b>Periodo</b></th>
                                 <th style="text-align:center; width:50%; font-size: 10px;<?php echo $estilo_fondo; ?> border: 1px solid rgba(128, 128, 128, 0.5);"><b>Importe</b></th>
                             </tr>
                         </thead>
                         <tbody>
         <?php
                 foreach ($impuestos_validos as $row) {
                     $periodo = $row['Periodo'];
                     $importe_val = $row['Importe'];
                     $importe = number_format((float)$importe_val, 2, '.', ',');
                     $total_impuestos += $importe_val;
         ?>
                             <tr>
                                 <td style="text-align:center; font-size: 10px;padding-bottom:2px;"><?php echo $periodo; ?></td>
                                 <td style="text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;"><?php echo "$ " . $importe; ?></td>
                             </tr>
         <?php } ?>
                             <tr>
                                 <td style="text-align:center; font-size: 10px;padding-bottom:2px;<?php echo $estilo_fondo; ?>"><b>Total Impuestos:</b></td>
                                 <td style="text-align:right; font-size: 10px;padding-bottom:2px;padding-right:3px;<?php echo $estilo_fondo; ?>"><b><u><?php echo "$ " . number_format((float)$total_impuestos, 2, '.', ','); ?></u></b></td>
                             </tr>
                         </tbody>
                     </table>
                 </div>
         <?php
             } 
         } 
         ?>
    
    
        <!-- Resultados de operador -->
        <div style="margin-top: -13px;">
    <h4>Resultados Operador</h4>
    <?php echo '<hr style="border: 1px solid black; margin: 10px 0;margin-top: -10px;">';?>
    <table border="0" width="100%">
        <thead>
            <tr>
               
                <td style="text-align:left;margin-top: -10px;" colspan="2"><b>Cuentas Operador</b></td>
                <td></td>
                <td></td>
            </tr>
        </thead>
        <tbody>
           

            
            <tr>
              

                <td style="text-align: left; font-size:  10px;margin-right:-45px;">
                    <b>Comisión: </b> <br>
                    <b>Comprobado: </b> <br>
                    <b style="color:red;"> No comprobado: </b><br>
                    <b>Total: </b> <br> <br>
                    

                    
                </td>
                <td style="text-align:right; font-size: 10px;padding-left:-75px;">
                <?php echo $lq_cOp_comision_operador; ?><br>
                <?php echo $lq_cOp_comporbado; ?><br>
                <b style= "color:red;"><?php
                $nocomprobado = $lq_cOp_comprobado - $lq_cOp_deposito_raw; echo number_format((float)$nocomprobado,2,'.',','); ?></b><br>
                <?php echo $lq_cOp_total; ?><br><br>
                

                
                </td>
                <td style="padding-right: 30px;">

                </td>
            
              

                <td style="text-align: left; font-size:  10px;margin-right:-45px;">
                  
                    <b>Depósitos: </b> <br>
                    <b style="color:red;">Impuestos: </b> <br>
                    <b>Combustible faltante: </b> <br>
                    <b style="color:red;">Préstamos: </b> <br><br>
                    <b>Infonavit: </b> <br>
                    <b>ISR: </b> <br>
                    <b>IMSS: </b> <br>

                    <b style="font-size:    13px;">Resultado Operador:  </b> 
                </td>
                <td style="text-align:right; font-size: 10px;padding-left:-75px;">
              
                <?php echo $lq_cOp_deposito; ?> <br>
                <b style="color:red;"><?php echo $lq_cOp_impuestos; ?></b><br>
                <?php echo $lq_cOp_comb_faltante; ?><br>
                <b style= "color:red;"><?php echo $lq_cOp_prestamos; ?></b><br><br>
                <b style="color:red;"><?php echo $lq_cOp_infonavit; ?></b><br>
                <b style="color:red;"><?php echo $lq_cOp_isr; ?></b><br>
                <b style="color:red;"><?php echo $lq_cOp_imss; ?></b><br>

                <?php echo $lq_cOp_resultado_op; ?>
                </td>
                <td style="padding-right: 30px;">

                </td>
            </tr>
        </tbody>
    </table>
</div>

      
        <!-- Resultados comb -->
        <?php echo '<hr style="border:  1px solid black; margin:  10px 0;">';?>
<div style="margin-top:  -13px;">
    <h4>Resultados Combustible</h4>

    <table border="0" width="100%" style="position:  absolute;">
        <thead>
            <tr>
                <td>Resultados Unidad: <?php echo $lq_unidad_name_head; ?></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Lado izquierdo -->
                <td style="text-align: left; font-size:  10px;margin-top:  -10px;">
                    <b>Lt. Diesel Vale: </b> <br>
                    <b>Lt. Diesel Comprobado: </b> <br>
                    <b>Total Lt. Diesel: </b> <br><br>
                    <b>Precio Lt: </b> <br>
                    <b>Lt. Diesel Descontar: </b> <br><br>
                    <b style="font-size:  13px;">Total Descontar: </b> 
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-18px;">
                    <?php echo $lq_rCo_lt_diesel_vale; ?><br>
                    <?php echo $lq_rCo_lt_diesel_comprob; ?><br>
                    <?php echo $lq_rCo_total_lt_diesel; ?><br><br>
                    <?php echo $lq_rCo_precio_lt; ?><br>
                    <?php echo $lq_rCo_lt_diesel_disc; ?><br><br>
                    <?php echo $lq_rCo_b_total_descontar; ?>
                </td>
                <td style="padding-left: 50px; padding-right:20px;">

</td>
                <!-- Lado derecho -->
                <td style="text-align: left; font-size:  10px;">
                    <b>Lts. Diesel Consumido: </b> <br>
                    <b>Lts. Margen: </b> <br>
                    <b>Lts. Diesel ECM: </b> <br><br><br><br><br>
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-5px;">
                <?php echo $lq_rCo_lt_diesel_consum; ?><br>
                <?php echo $lq_rCo_lt_margen; ?><br>
                <?php echo $lq_rCo_lt_diesel_ecm; ?><br><br><br><br><br>
                </td>
                <td style="padding-right: 30px;">

                </td>
            </tr>
        </tbody>
    </table>
         <?php if ($lq_thermo1_id > 0 && !empty($lq_thermo1_id)) {
               $resSQLTh1= "SELECT 
                            lqt.bbTCobrarLtsOperador,
                            lqt.bbTDieselAutorizado,
                            lqt.bbTDieselConsumido,
                            lqt.bbTDieselDescontar,
                            lqt.bbTDieselEfectivo,
                            lqt.bbTDieselVale,
                            lqt.bbTHorasFinal,
                            lqt.bbTHorasInicial,
                            lqt.bbTHorasRecorridas,
                            lqt.bbTPrecioLitro,
                            lqt.bbTRendimiento,
                            lqt.bbTTotalDescontar,
                            lqt.bbTTotalDiesel,
                            lqt.bbTTotalDieselCompu,
                            lqtn.Unidad 
                            FROM {$prefijobd}liquidaciones AS lqt 
                            INNER JOIN {$prefijobd}unidades AS lqtn ON lqt.UnidadThermo_RID = lqtn.ID
                            WHERE lqt.ID = {$id_liquidacion}";
            $runSQLTh1 = mysqli_query($cnx_cfdi2, $resSQLTh1);
            while ($rowSQLTh1 = mysqli_fetch_array($runSQLTh1)) {
                $lqtrCobrarLtsOperador = $rowSQLTh1['bbTCobrarLtsOperador'];
                $lqtrDieselAutorizado = $rowSQLTh1['bbTDieselAutorizado'];
                $lqtrDieselConsumido = $rowSQLTh1['bbTDieselConsumido'];
                $lqtrDieselDescontar = $rowSQLTh1['bbTDieselDescontar'];
                $lqtrTDieselEfectivo = $rowSQLTh1['bbTDieselEfectivo'];
                $lqtrDieselVale = $rowSQLTh1['bbTDieselVale'];
                $lqtrHorasFinal = $rowSQLTh1['bbTHorasFinal'];
                $lqtrHorasInicial = $rowSQLTh1['bbTHorasInicial'];
                $lqtrHorasRecorridas = $rowSQLTh1['bbTHorasRecorridas'];
                $lqtrPrecioLitro = $rowSQLTh1['bbTPrecioLitro'];
                $lqtrRendimiento = $rowSQLTh1['bbTRendimiento'];
                $lqtrTotalDescontar = $rowSQLTh1['bbTTotalDescontar'];
                $lqtrTotalDiesel = $rowSQLTh1['bbTTotalDiesel'];
                $lqtrTotalDieselCompu = $rowSQLTh1['bbTTotalDieselCompu'];
                $lqtrUnidad = $rowSQLTh1['Unidad'];
            }
            
            
            ?>
        <?php echo '<hr style="border:  1px solid black; margin:  10px 0; width:30%; text-align:left;">';?>

                <table border="0" width="100%" style="position:  absolute;">
        <thead>
            <tr>
                <td>Resultados Thermo : <?php echo $lqtrUnidad;?>1</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Lado izquierdo -->
                <td style="text-align: left; font-size:  10px;margin-top:  -10px;">
                    <b>Lt. Diesel Vale: </b> <br>
                    <b>Lt. Diesel Comprobado: </b> <br>
                    <b>Total Lt. Diesel: </b> <br><br>
                    <b>Precio Lt: </b> <br>
                    <b>Lt. Diesel Descontar: </b> <br><br>
                    <b style="font-size:  13px;">Total Descontar: </b> 
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-18px;">
                    <?php echo $lqtrDieselVale; ?><br>
                    <?php echo $lqtrTDieselEfectivo; ?><br>
                    <?php echo $lqtrTotalDiesel; ?><br><br>
                    <?php echo $lqtrPrecioLitro; ?><br>
                    <?php echo $lqtrDieselDescontar; ?><br><br>
                    <?php echo $lqtrTotalDescontar; ?>
                </td>
                <td style="padding-left: 50px; padding-right:20px;">

</td>
                <!-- Lado derecho -->
                <td style="text-align: left; font-size:  10px;">
                    <b>Lts. Diesel Consumido: </b> <br>
                    <b>Lts. Margen: </b> <br>
                    <b>Lts. Diesel ECM: </b> <br><br><br><br><br>
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-5px;">
                <?php echo $lqtrDieselConsumido; ?><br>
                <?php echo $lqtrDieselAutorizado; ?><br>
                <?php echo $lqtrTotalDieselCompu; ?><br><br><br><br><br>
                </td>
                <td style="padding-right: 30px;">

                </td>
            </tr>
        </tbody>
    </table>
        <?php }?>

        <?php if ($lq_thermo2_id > 0 && !empty($lq_thermo2_id)) { 
            $resSQLTh2= "SELECT 
                            lqt.bbTCobrarLtsOperador2,
                            lqt.bbTDieselAutorizado2,
                            lqt.bbTDieselConsumido2,
                            lqt.bbTDieselDescontar2,
                            lqt.bbTDieselEfectivo2,
                            lqt.bbTDieselVale2,
                            lqt.bbTHorasFinal2,
                            lqt.bbTHorasInicial2,
                            lqt.bbTHorasRecorridas2,
                            lqt.bbTPrecioLitro2,
                            lqt.bbTRendimiento2,
                            lqt.bbTTotalDescontar2,
                            lqt.bbTTotalDiesel2,
                            lqt.bbTTotalDieselCompu2,
                            lqtn.Unidad 
                            FROM {$prefijobd}liquidaciones AS lqt 
                            INNER JOIN {$prefijobd}unidades AS lqtn ON lqt.UnidadThermo2_RID = lqtn.ID
                            WHERE lqt.ID = {$id_liquidacion}";
            $runSQLTh2 = mysqli_query($cnx_cfdi2, $resSQLTh2);
            while ($rowSQLTh2 = mysqli_fetch_array($runSQLTh2)) {
                $lqtrCobrarLtsOperador2 = $rowSQLTh2['bbTCobrarLtsOperador2'];
                $lqtrDieselAutorizado2 = $rowSQLTh2['bbTDieselAutorizado2'];
                $lqtrDieselConsumido2 = $rowSQLTh2['bbTDieselConsumido2'];
                $lqtrDieselDescontar2 = $rowSQLTh2['bbTDieselDescontar2'];
                $lqtrTDieselEfectivo2 = $rowSQLTh2['bbTDieselEfectivo2'];
                $lqtrDieselVale2 = $rowSQLTh2['bbTDieselVale2'];
                $lqtrHorasFinal2 = $rowSQLTh2['bbTHorasFinal2'];
                $lqtrHorasInicial2 = $rowSQLTh2['bbTHorasInicial2'];
                $lqtrHorasRecorridas2 = $rowSQLTh2['bbTHorasRecorridas2'];
                $lqtrPrecioLitro2 = $rowSQLTh2['bbTPrecioLitro2'];
                $lqtrRendimiento2 = $rowSQLTh2['bbTRendimiento2'];
                $lqtrTotalDescontar2 = $rowSQLTh2['bbTTotalDescontar2'];
                $lqtrTotalDiesel2 = $rowSQLTh2['bbTTotalDiesel2'];
                $lqtrTotalDieselCompu2 = $rowSQLTh2['bbTTotalDieselCompu2'];
                $lqtrUnidad2 = $rowSQLTh2['Unidad'];
            }
            
            ?>
        <?php echo '<hr style="border:  1px solid black; margin:  10px 0; width:30%; text-align:left;">';?>

            <table border="0" width="100%" style="position:  absolute;">
        <thead>
            <tr>
                <td>Resultados Thermo 2: <?php echo $lqtrUnidad2;?></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Lado izquierdo -->
                <td style="text-align: left; font-size:  10px;margin-top:  -10px;">
                    <b>Lt. Diesel Vale: </b> <br>
                    <b>Lt. Diesel Comprobado: </b> <br>
                    <b>Total Lt. Diesel: </b> <br><br>
                    <b>Precio Lt: </b> <br>
                    <b>Lt. Diesel Descontar: </b> <br><br>
                    <b style="font-size:  13px;">Total Descontar: </b> 
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-18px;">
                    <?php echo $lqtrDieselVale2; ?><br>
                    <?php echo $lqtrTDieselEfectivo2; ?><br>
                    <?php echo $lqtrTotalDiesel2; ?><br><br>
                    <?php echo $lqtrPrecioLitro2; ?><br>
                    <?php echo $lqtrDieselDescontar2; ?><br><br>
                    <?php echo $lqtrTotalDescontar2; ?>
                </td>
                <td style="padding-left: 50px; padding-right:20px;">

</td>
                <!-- Lado derecho -->
                <td style="text-align: left; font-size:  10px;">
                    <b>Lts. Diesel Consumido: </b> <br>
                    <b>Lts. Margen: </b> <br>
                    <b>Lts. Diesel ECM: </b> <br><br><br><br><br>
                </td>
                <td style="text-align: right; font-size:  10px;margin-top:  -10px;padding-left:-5px;">
                <?php echo $lqtrDieselConsumido2; ?><br>
                <?php echo $lqtrDieselAutorizado2; ?><br>
                <?php echo $lqtrTotalDieselCompu; ?><br><br><br><br><br>
                </td>
                <td style="padding-right: 30px;">

                </td>
            </tr>
        </tbody>
    </table>
     <?php   } ?>

</div>
   <sethtmlpagefooter name="myfooter" value="on" show-this-page="all" />
    
    </main>
</body>
</html>
<?php
	
$html = ob_get_clean();

// Incluir mPDF (versión 6.1)
require_once __DIR__ . '/vendor/autoload.php'; // o el path donde tengas mPDF v6.1
$mpdf = new mPDF('utf-8', 'Letter');
$mpdf->SetFont('helvetica');

// Escribir contenido
$mpdf->WriteHTML($html);

// Definir carpeta
if ($Multi >= 1) {
	$folder_path = "{$xml_dir}";
	
}else {
	
	$folder_path = "C:/xampp/htdocs{$xml_dir}";
}
	

// Asegurar que exista la carpeta
if (!is_dir($folder_path)) {
    mkdir($folder_path, 0777, true);
}

// Nombre del archivo
$nombre_pdf = "{$prefijo}_{$lq_xfolio}";
$file_path = $folder_path . "/" . $nombre_pdf . ".pdf";

// Borrar si ya existe
if (file_exists($file_path)) {
    unlink($file_path);
}

// Guardar en archivo (modo 'F')
$mpdf->Output($file_path, 'F');

//descargar al cliente
if (file_exists($file_path)) {
    // Limpiar buffers antes de enviar headers
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombre_pdf . '.pdf"');
    header('Content-Length: ' . filesize($file_path));
    flush();
    readfile($file_path);
    exit;
} else {
    echo "Error: El archivo PDF no se generó.";
}
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703 
//http://localhost/cfdipro/factura_formato.php?prefijodb=cljif_&id=4183703


?>