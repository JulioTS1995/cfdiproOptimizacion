<?php  

ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución

require 'phpqrcode/qrlib.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

$anio_logs = date('Y');
$mes_logs  = date('m');
$dia_logs  = date('d');

$fecha2_t = $anio_logs."-".$mes_logs."-".$dia_logs;  
$fecha2   = date("Y-m-d", strtotime($fecha2_t));

$prefijobd   = $_POST['prefijodb'];
$cliente_id  = $_POST['cliente'];
$moneda      = $_POST['moneda'];
$boton       = $_POST['btnEnviar'];

$prefijo = rtrim($prefijobd, "_");

if($cliente_id == 0){
    $sql_cliente="";
} else {
    $sql_cliente=" AND F.CargoAFactura_RID = ".$cliente_id;
}

if($moneda == 'PESOS'){
    $sql_moneda=" AND F.Moneda='PESOS' ";
} elseif($moneda == 'DOLARES') {
    $sql_moneda=" AND F.Moneda='DOLARES' ";
} else {
    $sql_moneda="";
}

if($boton == 'PDF'){

    mysqli_select_db($cnx_cfdi2, $database_cfdi);
    mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

    $resSQL07 = "SELECT * FROM ".$prefijobd."systemsettings";
    $runSQL07 = mysqli_query($cnx_cfdi2 ,$resSQL07);
    while($rowSQL07 = mysqli_fetch_array($runSQL07)){
        $RazonSocial    = $rowSQL07['RazonSocial'];
        $Calle          = $rowSQL07['Calle'];
        $NumeroExterior = $rowSQL07['NumeroExterior'];
        $NumeroInterior = $rowSQL07['NumeroInterior'];
        $Colonia        = $rowSQL07['Colonia'];
        $CodigoPostal   = $rowSQL07['CodigoPostal'];
        $Ciudad         = $rowSQL07['Ciudad'];
        $Estado         = $rowSQL07['Estado'];
        $Telefono       = $rowSQL07['Telefono'];
        $RFC            = $rowSQL07['RFC'];
        $Pais           = $rowSQL07['Pais'];
        $Municipio      = $rowSQL07['Municipio'];
        if ($regPorParametro) {
            $Regimen_prev= $rowSQL07['RegimenFiscal_RID'];
            $resSQL007= "SELECT * FROM {$prefijobd}c_regimenfiscal WHERE ID= {$Regimen_prev}" ;
            $runSQL007= mysqli_query($cnx_cfdi2, $resSQL007);
            $rowSQL007= mysqli_fetch_assoc($runSQL007);
            if ($rowSQL007){
                $Regimen = $rowSQL007['Clave'].", ".$rowSQL007['Descripcion'];
            }
        }else{
            $Regimen = $rowSQL07['Regimen'];
        }
        $codLocalidad = '';
        $rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';
    }

    if (!empty($NumeroExterior)) {
        $NumeroExterior = '# '.$NumeroExterior;
    } else {
        $NumeroExterior = '';
    }

    if (!empty($NumeroInterior)) {
        $NumeroInterior = 'int. '.$NumeroInterior;
    } else {
        $NumeroInterior = '';
    }

///////////////////domicilio restante
    if (!empty($Colonia || $Estado || $Ciudad)) {
        $domicilioRestante = 'Col. '.$Colonia.', </br>'.$Ciudad.', '.$Estado.', CP: '.$CodigoPostal;
    } else {
        $domicilioRestante = '';
    }

    $parametro_bgc = 921;
    $resSQL921 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_bgc";
    $runSQL921 = mysqli_query($cnx_cfdi2, $resSQL921);
    while ($rowSQL921 = mysqli_fetch_array($runSQL921)) {
        $color= $rowSQL921 ['VCHAR'];
    }

    $parametro_letra_color = 922;
    $resSQL922 = "SELECT id2, VCHAR, VLOGI FROM {$prefijobd}parametro Where id2 =$parametro_letra_color";
    $runSQL922 = mysqli_query($cnx_cfdi2, $resSQL922);
    while ($rowSQL922 = mysqli_fetch_array($runSQL922)) {
        $color_letra= $rowSQL922 ['VCHAR'];
    }

    $estilo_fondo= 'background-color:'.$color.'; color:'.$color_letra.';';

    $datosClientes = [];
    $resSQLCLientes = "SELECT 
							DISTINCT(C.ID) as id_cliente, 
							C.RazonSocial as RazonSocial
                        FROM {$prefijobd}factura F, {$prefijobd}oficinas O, {$prefijobd}clientes C 
                        WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID 
                        AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') 
                        AND F.CargoAFactura_RID = C.ID 
                        AND F.cfdfchhra IS NOT NULL ".$sql_cliente.$sql_moneda." 
                        ORDER BY C.RazonSocial";
    $runSQLClientes=mysqli_query($cnx_cfdi2, $resSQLCLientes);
    while ($rowSQLClientes = mysqli_fetch_array($runSQLClientes)) {
        $datosClientes[] = [
            'id_cliente'   => $rowSQLClientes['id_cliente'],
            'razon_social' => $rowSQLClientes['RazonSocial']
        ];
    }

    $datosFactura = [
        'moneda'      => $moneda,
        'estiloFondo' => $estilo_fondo,
        'prefijobd'   => $prefijobd
    ];

  function imprimeTabla($datosFactura, $datosClientes, $monedaSel, $cnx_cfdi2) {
        foreach ($datosClientes as $cliente) {
            
            $resSQL="SELECT 
							F.ID, 
							F.Moneda, 
							F.Ticket, 
							F.XFolio, 
							F.Creado, 
							F.zTotal,
							F.CobranzaAbonado, 
							F.CobranzaSaldo, 
							F.Vence, 
							F.Comentarios,
							F.cfdfchhra as FechaTimbrado, 
							F.DiasCredito, 
							F.TipoCambio 
                     FROM {$datosFactura['prefijobd']}factura F, {$datosFactura['prefijobd']}oficinas O 
                     WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID 
                     AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') 
                     AND F.CargoAFactura_RID = '{$cliente['id_cliente']}' 
                     AND F.Moneda='{$monedaSel}'
                     AND F.cfdfchhra IS NOT NULL ORDER BY F.XFolio";
            $runSQL = mysqli_query($cnx_cfdi2, $resSQL);
///////////////////////////////// Si no tiene facturas no imprimimos nada
            if (mysqli_num_rows($runSQL) == 0) {
                continue;
            }
//////////////////////////////// Arma la tabla del PDF
            echo '<table border="1" style="margin:0;border-collapse: collapse;" width="100%;">
                <thead>
                    <tr>
                        <td style="'.$datosFactura['estiloFondo'].' font-size:12px; font-family: Helvetica; font-family: Helvetica, sans-serif;" colspan="11" align="center"><b>'
                        .$cliente['razon_social'].' '.$monedaSel.' </b></td>
                    </tr>
                    <tr>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Fecha Timbrado</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Folio Factura</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Moneda</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Ticket</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Referencia</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Fecha Vencimiento</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Dias de Crédito</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Dias Vencido</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Dias Por Vencer</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Saldo Vencido</th>
                        <th style=" font-family: Helvetica; font-size:11px;'.$datosFactura['estiloFondo'].'" align="center">Saldo Factura</th>
                    </tr>
                </thead>
                <tbody>';

            $saldo_vencido_suma = 0;
            $saldo_suma         = 0;

            while ($row = mysqli_fetch_assoc($runSQL)) {
                $fecha_timbrado   = date("d-m-Y", strtotime($row['FechaTimbrado']));
                $vence_t          = $row['Vence'];
                $vence            = date("d-m-Y", strtotime($vence_t));
                $diascredito      = $row['DiasCredito'];
                $cobranza_saldo_t = $row['CobranzaSaldo'];
                $cobranza_saldo   = "$".number_format($cobranza_saldo_t,2);

/////////////////////////////// Cálculo de días vencidos y por vencer//
                $dias_vencidos   = '';
                $dias_por_vencer = '';
                $saldo_vencido_t = 0;

                if ($vence_t < '1990-01-01' || empty($vence_t)) {
                    $vence = '';
                } else {
                    $fecha_venc = new DateTime($vence_t);
                    $fecha_hoy  = new DateTime(date('Y-m-d'));
                    $interval   = $fecha_hoy->diff($fecha_venc);
                    $dias_diff  = $interval->days;

                    if ($fecha_venc < $fecha_hoy) {
                        $dias_vencidos   = $dias_diff;
                        $dias_por_vencer = '';
                        $saldo_vencido_t = $cobranza_saldo_t;
                    } else {
                        $dias_vencidos   = '';
                        $dias_por_vencer = $dias_diff;
                    }
                }

                $saldo_vencido = "$" . number_format($saldo_vencido_t, 2);
                $saldo_vencido_suma += $saldo_vencido_t;
                $saldo_suma += $cobranza_saldo_t;

                echo '<tr>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$fecha_timbrado.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$row['XFolio'].'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$row['Moneda'].'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$row['Ticket'].'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="left">'.$row['Comentarios'].'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$vence.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$diascredito.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;  color:red;" align="center">'.$dias_vencidos.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="center">'.$dias_por_vencer.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="right">'.$saldo_vencido.'</td>
                        <td style=" font-family: Helvetica; font-size:12px;" align="right">'.$cobranza_saldo.'</td>
                      </tr>';
            }

            echo '<tr>
                    <td colspan="9" align="right"></td>
                    <td style=" font-family: Helvetica;"><b>$'.number_format($saldo_vencido_suma,2).'</b></td>
                    <td style=" font-family: Helvetica;"><b>$'.number_format($saldo_suma,2).'</b></td>
                  </tr>
                </tbody>
                </table><br>';
        }
    }


ob_start();

?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<style>
			@page {
                margin: 150px 25px;
            }
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: Helvetica, sans-serif;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 170px; 
				font-family: Helvetica, sans-serif;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: Helvetica, sans-serif;

		  } 
		  .page-break {
				page-break-after: always;
		  }
		  
			
			
		</style>
		
		
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
		
		
		
		<title>ESTADO DE CUENTA</title>	
	</head>
	<body>
	<htmlpageheader name="myHeader">
        			<div style = "padding-top: -20px;">
				<table border="0" style="margin:0; border-collapse: collapse; " width="100%">
					<tr>
						<!-- LOGO IMG -->
						<td style="text-align:center; width:25%;"><img src=<?php echo $rutalogo;?> width="150px" alt=" "/></td>
						<td style="text-align:center; width:45%; font-size: 11px; font-family: Helvetica; font-family: Helvetica, sans-serif;">
							<strong><?php echo $RazonSocial ?></strong> <br/>
							<?php echo $RFC ?><br/>
							<?php echo $Calle.' #'.$NumeroExterior.'<br/>'.$NumeroInterior.' '.$Colonia.'<br/>Régimen Fiscal: '.$Regimen.''; ?><br/>
							<strong><?php echo "Lugar de expedición (C.P.): $CodigoPostal"; ?></strong><br/>
												
											
							
						</td>
						<td style="text-align:center; width:30%; font-size: 10px; font-family: Helvetica; padding-bottom: 0px; font-family: Helvetica, sans-serif;">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td colspan="2" style="text-align:center; font-size: 20px; font-family: Helvetica; padding: 1;vertical-align: center;<?php echo $estilo_fondo; ?> font-family: Helvetica, sans-serif;"><b>ESTADO DE CUENTA</b></td>
								</tr>
								
							</table>
						</td>
					</tr>

				</table>
			</div>
	</htmlpageheader>


	<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
	<main>

	<?php		

				    if ($datosFactura['moneda'] == 'AMBOS') {
						imprimeTabla($datosFactura, $datosClientes, 'PESOS', $cnx_cfdi2);
						imprimeTabla($datosFactura, $datosClientes, 'DOLARES', $cnx_cfdi2);
					} elseif ($datosFactura['moneda'] == 'PESOS') {
						imprimeTabla($datosFactura, $datosClientes, 'PESOS', $cnx_cfdi2);
					} elseif ($datosFactura['moneda'] == 'DOLARES') {
						imprimeTabla($datosFactura, $datosClientes, 'DOLARES', $cnx_cfdi2);
					}



			?>

	</main>

		</body>
		</html>

<?php
require_once __DIR__ . '/vendor/autoload.php';

$html = ob_get_clean();
 
$mpdf = new mPDF('utf-8', 'letter'); 


$mpdf->SetFont('helvetica');
$mpdf->WriteHTML($html);

$nombre_pdf = $prefijo . " - Estado de Cuenta por moneda.pdf";

$mpdf->Output($nombre_pdf, 'D');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'I');
exit;







} elseif($boton == 'Excel'){

////////////////////////////////////////////////////////////////////////////////////////////////////////////////Ejecutar Excel
header("Content-type: application/vnd.ms-excel");
$nombre="Estado_cuenta_clientes_".date("h:i:s")."_".date("d-m-Y").".xls";
header("Content-Disposition: attachment; filename=$nombre");
require_once('lib_mpdf/pdf/mpdf.php');
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");	



?>
	<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
		<table class="table table-hover table-responsive table-condensed" border="1" id="table">
			
			<thead>
				<tr>
					<td colspan="10" align="center"><b>ESTADO DE CUENTA <?php echo $fecha2; ?> </b></td>
				</tr>
				<tr>
					<th align="center">Fecha Timbrado</th>
					<th align="center">Folio Factura</th>
					<th align="center">Moneda</th>
					<th align="center">Ticket</th>
					<th align="center">Referencia</th>
					<th align="center">Fecha Vencimiento</th>
					<th align="center">Dias de Crédito</th>
					<th align="center">Dias Vencido</th>
					<th align="center">Saldo Vencido</th>
					<th align="center">Saldo Factura</th>
				</tr>
			</thead>
		<tbody>
	<?php
		//Buscar Clientes-Facturas
		$resSQL11="SELECT DISTINCT(C.ID) as id_cliente FROM ".$prefijobd."factura F, ".$prefijobd."oficinas O, ".$prefijobd."clientes C WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') AND F.CargoAFactura_RID = C.ID AND F.cfdfchhra IS NOT NULL ".$sql_cliente.$sql_moneda." ORDER BY C.RazonSocial";
			//echo "<br>".$resSQL11;
			$runSQL11=mysql_query($resSQL11);
			$total_clientes_t = mysql_num_rows($runSQL11);
			$total_clientes = number_format($total_clientes_t,0);
			while ($rowSQL11=mysql_fetch_array($runSQL11)){
			//Obtener_variables
			$id_cliente = $rowSQL11['id_cliente'];
									
			//Consultar Nombre del Cliente
			$resSQL12="SELECT * FROM ".$prefijobd."clientes WHERE ID = ".$id_cliente;
			//echo "<br>".$resSQL12;
			$runSQL12=mysql_query($resSQL12);
			while ($rowSQL12=mysql_fetch_array($runSQL12)){
			//Obtener_variables
				$nombre_cliente = $rowSQL12['RazonSocial'];
			}
								
	?>
					
			<tr>
				<td colspan="10"><b><?php echo $nombre_cliente; ?></b></td>
			</tr>
    <?php	
			$saldo_vencido_suma_pesos = 0;
			$saldo_suma_pesos = 0;
			$saldo_vencido_suma_dolares = 0;
			$saldo_suma_dolares = 0;
			$saldo_vencido_suma_dolares_en_pesos = 0;
			$saldo_suma_dolares_en_pesos = 0;

			$saldo_vencido_suma_t_dolares_en_mx = 0;
			$saldo_suma_t_dolares_en_mx = 0;

			$saldo_vencido_suma_t_final_pesos_t = 0;
			$saldo_suma_t_final_pesos_t = 0;
									
							
			//Buscar Facturas
			$resSQL4="SELECT F.ID as ID, 
							 F.Moneda as Moneda, 
							 F.Ticket as Ticket, 
							 F.XFolio as XFolio, 
							 F.Creado as Creado, 
							 F.zTotal as zTotal, 
							 F.CobranzaAbonado as CobranzaAbonado, 
							 F.CobranzaSaldo as CobranzaSaldo, 
							 F.Vence as Vence, 
							 F.Comentarios as Comentarios, 
							 F.cfdfchhra as 
							 FechaTimbrado, 
							 F.DiasCredito as DiasCredito, 
							 F.TipoCambio as TipoCambio 
						FROM {$prefijobd}factura F, {$prefijobd}oficinas O 
						WHERE F.CobranzaSaldo > 0 AND F.Oficina_RID = O.ID 
						AND (F.cCanceladoT IS NULL OR F.cCanceladoT = '') ".$sql_cliente.$sql_moneda." AND F.cfdfchhra IS NOT NULL ORDER BY F.XFolio";
			//echo "<br>".$resSQL4;
			$runSQL4=mysql_query($resSQL4);
			$total_registros_t2 = mysql_num_rows($runSQL4);
			$total_registros2 = number_format($total_registros_t2,0);
			while ($rowSQL4=mysql_fetch_array($runSQL4)){
				//Obtener_variables
				$id_factura = $rowSQL4['ID'];
				//$nom_cliente = $rowSQL4['nom_cliente'];
				$moneda = $rowSQL4['Moneda'];
				$TipoCambio = $rowSQL4['TipoCambio'];
				$comentario = $rowSQL4['Comentarios'];
				$ticket = $rowSQL4['Ticket'];
				$xfolio = $rowSQL4['XFolio'];
				$creado_t = $rowSQL4['Creado'];
				$creado = date("d-m-Y H:i:s", strtotime($creado_t));
				$fecha_timbrado_t = $rowSQL4['FechaTimbrado'];
				$fecha_timbrado = date("d-m-Y H:i:s", strtotime($fecha_timbrado_t));
				$total_t = $rowSQL4['zTotal'];
				$total = number_format($total_t,2);
				$cobranza_abonado_t = $rowSQL4['CobranzaAbonado'];
				$cobranza_abonado = number_format($cobranza_abonado_t,2);
				$cobranza_saldo_t = $rowSQL4['CobranzaSaldo'];
				$cobranza_saldo = "$".number_format($cobranza_saldo_t,2);
				$vence_t = $rowSQL4['Vence'];
				$vence = date("d-m-Y", strtotime($vence_t));
				$diascredito = $rowSQL4['DiasCredito'];
				$diff = abs(strtotime($fecha2) - strtotime($vence_t));
				//$years = floor($diff / (365*60*60*24));
				//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
				$years=0;
				$months=0;
				$atraso = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
				
				$atraso_t = number_format($atraso,2);
			
								
				//Validar si esta vigente el Vencimiento (Negativo)
				if($vence_t < $fecha2_t) {
					$atraso=$atraso*-1;
				}else {
				}
										
				if($vence_t < '1990-01-01'){
					$vence ='';
				}
										
				//Validar Estatus
				if($vence_t < $fecha2_t){
					$estatus='Vencido';
				} elseif($vence_t >= $fecha2_t){
					//Valida dias pendientes por vencer
					if($atraso > 7){
						$estatus='En Tiempo';
					} else {
						$estatus='Proximo a Vencer';
					}
				}
										
				$saldo_vencido_t = 0;
				if($estatus == 'Vencido'){
					$saldo_vencido_t = $cobranza_saldo_t;
				}
				$saldo_vencido = "$".number_format($saldo_vencido_t,2);
									
				

				//VALIDAR MONEDA en SUMA

				
				if($moneda == 'PESOS'){
					$saldo_vencido_suma_pesos = $saldo_vencido_suma_pesos + $saldo_vencido_t;					
					$saldo_suma_pesos = $saldo_suma_pesos + $cobranza_saldo_t;

				} elseif($moneda == 'DOLARES') {
					$saldo_vencido_suma_dolares = $saldo_vencido_suma_dolares + $saldo_vencido_t;					
					$saldo_suma_dolares = $saldo_suma_dolares + $cobranza_saldo_t;

					$saldo_vencido_suma_dolares_mx = $saldo_vencido_suma_dolares_mx + ($saldo_vencido_t * $TipoCambio);					
					$saldo_suma_dolares_mx = $saldo_suma_dolares_mx + ($cobranza_saldo_t * $TipoCambio);
				

				}
				
				
				
	?>   
			<tr>
				<td align="center"><?php echo $fecha_timbrado; ?></td>
				<td align="center"><?php echo $xfolio; ?></td>
				<td align="center"><?php echo $moneda; ?></td>
				<td align="center"><?php echo $ticket; ?></td>
				<td align="left"><?php echo $comentario; ?></td>
				<td align="center"><?php echo $vence; ?></td>
				<td align="center"><?php echo $diascredito; ?></td>
				<td align="center"><?php echo $atraso_t; ?></td>
				<td align="right"><?php echo $saldo_vencido; ?></td>
				<td align="right"><?php echo $cobranza_saldo; ?></td>				
			</tr>
	<?php 
			}  //Fin Buscar Facturas
															
			$saldo_vencido_suma_t_pesos = "$".number_format($saldo_vencido_suma_pesos,2);
			$saldo_suma_t_pesos = "$".number_format($saldo_suma_pesos,2);

			$saldo_vencido_suma_t_dolares = "$".number_format($saldo_vencido_suma_dolares,2);
			$saldo_suma_t_dolares = "$".number_format($saldo_suma_dolares,2);


			$saldo_vencido_suma_t_final_pesos_t = $saldo_vencido_suma_pesos + $saldo_vencido_suma_dolares_mx;
			$saldo_suma_t_final_pesos_t = $saldo_suma_pesos + $saldo_suma_dolares_mx;


			$saldo_vencido_suma_t_final_pesos = "$".number_format($saldo_vencido_suma_t_final_pesos_t,2);
			$saldo_suma_t_final_pesos = "$".number_format($saldo_suma_t_final_pesos_t,2);


			

	?>
			<tr>
				<td colspan="8" align="right">TOTAL FACTURAS EN PESOS</td>
				<td><b><?php echo $saldo_vencido_suma_t_pesos; ?></b></td>
				<td><b><?php echo $saldo_suma_t_pesos; ?></b></td>
			</tr>
			<tr>
				<td colspan="8" align="right">TOTAL FACTURAS EN DOLARES</td>
				<td><b><?php echo $saldo_vencido_suma_t_dolares; ?></b></td>
				<td><b><?php echo $saldo_suma_t_dolares; ?></b></td>
			</tr>
			<tr>
				<td colspan="8" align="right"><b>TOTAL FINAL EN PESOS</b></td>
				<td><b><?php echo $saldo_vencido_suma_t_final_pesos; ?></b></td>
				<td><b><?php echo $saldo_suma_t_final_pesos; ?></b></td>
			</tr>
	<?php
							
	} //Fin Busca Cliente
							
						  
	?>
		
		</tbody> 
					
	<!-- Fin Tabla --------------------------------------------------------------------------------------------------------->
	</table>





<?php

} //FIN EXCEL

?>

