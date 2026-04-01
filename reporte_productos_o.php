<?php  
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(500);
ini_set('memory_limit', '512M'); 

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
date_default_timezone_set("America/Mexico_City");
$id_producto = $_GET['producto'];

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$boton = $_GET['btnGenerar'];

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");


$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

////////////////Agregar nombre del Mes


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

if ($boton==='Enviar') {

$fecha = $dia_logs." de ".$mes2." de ". $anio_logs;
$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;

//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM {$prefijobd}systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){
	$RazonSocial = $rowSQL0['RazonSocial'];
	$RFC = $rowSQL0['RFC'];
	$CodigoPostal = $rowSQL0['CodigoPostal'];
	$Calle = $rowSQL0['Calle'];
	$NumeroExterior = $rowSQL0['NumeroExterior'];
	$Colonia = $rowSQL0['Colonia'];
	$Ciudad = $rowSQL0['Ciudad'];
	$Pais = $rowSQL0['Pais'];
	$Estado = $rowSQL0['Estado'];
	$Municipio = $rowSQL0['Municipio'];
  $kitRefacciones = (int)$rowSQL0['KitRefacciones'];
}

//Buscar Producto
$res_prod = "SELECT * FROM {$prefijobd}productos WHERE ID = ".$id_producto;
$runprod = mysql_query($res_prod, $cnx_cfdi);
while($rowprod = mysql_fetch_array($runprod)){
	$producto_nombre = $rowprod['Nombre'];
	$producto_descripcion = $rowprod['Descripcion'];
  $producto_codigo = $rowprod['Codigo'];
}

if ($kitRefacciones === 1 ){$ctnQuery = ' UNION ALL

								SELECT M.ID,
									SUM(KR.Cantidad) AS Cantidad,
									M.XFolio,
									M.Fecha,
									CONCAT("Mantenimiento: ", M.XFolio) AS Descripcion,
									"1" AS ordena
								FROM '.$prefijobd.'mantenimientos M
								INNER JOIN '.$prefijobd.'mantenimientos_ref REF ON M.ID = REF.ID
								INNER JOIN '.$prefijobd.'kitrefacciones KR ON REF.RID = KR.ID
								WHERE KR.Refaccion_RID = "'.$id_producto.'"
                GROUP BY M.ID, M.XFolio, M.Fecha '; }else{
                  $ctnQuery =  '';
                	}


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Kardex del Producto '.$producto_nombre.' / '.$producto_codigo.'</h1>';


       
            $html .='<div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              
              <div><br></div>


              <div>
                <table>
                  <thead>
                    <tr>
                      <th align="center" style="font-size: 12px;">Fecha</th>
                      <th align="center" style="font-size: 12px;">Descripción</th>
					  <th align="center" style="font-size: 12px;">Entradas</th>
                      <th align="center" style="font-size: 12px;">Salidas</th>
                      <th align="center" style="font-size: 12px;">Existencia</th>
                    </tr>
                  </thead>
                  <tbody>';


        $existencia = 0;
			  $entradas = 0;
				$salidas = 0;
				$total_registros_t=0;
                //Buscar Compras
                $resSQL01 = "SELECT *
							FROM (
								SELECT a.id,
									a.cantidad,
									b.xfolio,
									b.fecha,
									CONCAT('Compra: ', b.xfolio) AS Descripcion,
									'0' AS ordena
								FROM {$prefijobd}comprassub a
								INNER JOIN {$prefijobd}compras b ON a.foliosub_rid = b.id
								WHERE a.productoa_rid = '{$id_producto}'

								UNION ALL

								SELECT a.id,
									a.cantidad,
									b.xfolio,
									b.fecha,
									CONCAT('Vale de Entrada: ', b.xfolio) AS Descripcion,
									'0' AS ordena
								FROM {$prefijobd}valesentradasub a
								INNER JOIN {$prefijobd}valesentrada b ON a.foliosub_rid = b.id
								WHERE a.productoEnt_rid = '{$id_producto}'
                
                UNION ALL 

                SELECT a.id,
                       a.cantidad,
                       b.xfolio,
                       b.fecha,
                       CONCAT('Vale de Salida: ', b.xfolio) as Descripcion,
                       '1' as ordena
                FROM {$prefijobd}valessalidasub a
                INNER JOIN {$prefijobd}valessalida b ON a.foliosub_rid = b.id WHERE a.productov_rid = '{$id_producto}'


								{$ctnQuery}

							
								
							) X
							ORDER BY X.fecha, X.ordena, X.xfolio
							";

				
				//$resSQL01 = "SELECT * FROM {$prefijobd}comprassub WHERE ProductoA_RID=".$id_producto;
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
        //die($resSQL01);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$compras_cantidad_t = $rowSQL01['cantidad'];
					$compras_cantidad = number_format($compras_cantidad_t,2);
					$compras_xfolio = $rowSQL01['xfolio'];
					$compras_fecha_t = $rowSQL01['fecha'];
					$compras_fecha = date("d-m-Y", strtotime($compras_fecha_t));
					$tipomovimiento = $rowSQL01['ordena'];
					$compras_descripcion = $rowSQL01['Descripcion'];
					
					if ($tipomovimiento=='0'){
						$existencia = floatval($existencia) + floatval($compras_cantidad_t);
						$entradas = floatval($entradas) + floatval($compras_cantidad_t);
					}
				
					if ($tipomovimiento=='1'){
						$existencia = floatval($existencia) - floatval($compras_cantidad_t);
						$salidas = floatval($salidas) + floatval($compras_cantidad_t);
					}

					//$existencia = $existencia + $compras_cantidad;
					//$entradas = $entradas + $compras_cantidad;
					$total_registros_t = $total_registros_t +1;
				
					if ($tipomovimiento=='0'){
						$html.='
							<tr>
							 <td align="center">'.$compras_fecha.'</td>
							 <td align="left">'.$compras_descripcion.'</td>
							 <td align="center">'.$compras_cantidad.'</td>
							 <td align="center">0.00</td>
							 <td align="center">'.number_format($existencia,2).'</td>
							</tr>

						';
					}
					if ($tipomovimiento=='1'){
						$html.='
							<tr>
							 <td align="center">'.$compras_fecha.'</td>
							 <td align="left">'.$compras_descripcion.'</td>
							 <td align="center">0.00</td>
							 <td align="center">'.$compras_cantidad.'</td>
							 <td align="center">'.number_format($existencia,2).'</td>
							</tr>
						';
					}
				}
				
				
				//////Agregar Totales 
					
				$total_registros = number_format($total_registros_t,0);
				$total_entradas = number_format($entradas,2);
				$total_salidas = number_format($salidas,2);
					
					
				$html.='     
					<tr>
						<td colspan="5"><hr></td>
					</tr>
					<tr>
						<td align="right"><strong>TOTAL REGISTROS:</strong></td>
						<td align="left"><strong>'.$total_registros.'</strong></td>
						<td align="center"><strong>'.$total_entradas.'</strong></td>
						<td align="center"><strong>'.$total_salidas.'</strong></td>
						<td align="center"> </td>
					</tr>
						
				';
					

              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>

              ';

          
$html.='</header>';
//echo $html;
$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('reporte_productos_'.$anio_logs.'_'.$mes_logs.'_'.$dia_logs.'.pdf', 'I');

} elseif ($boton === 'Calcular') {

    if ($id_producto > 0) {

        $TotalCompras = 0.0;
        $TotalVEntradas = 0.0;
        $TotalVSalidas = 0.0;
        $TotalMantenimiento = 0.0;
        $TotalExistencia = 0.0;

        // Compras
        $sql91 = "SELECT COALESCE(SUM(cantidad),0) AS TotalCompras
                  FROM {$prefijobd}comprassub
                  WHERE productoA_rid = {$id_producto}";
        $runSQL91 = mysql_query($sql91, $cnx_cfdi);
        if ($runSQL91) {
            $row = mysql_fetch_assoc($runSQL91);
            $TotalCompras = (float)$row['TotalCompras'];
        }

        // Vales entradas
        $sql92 = "SELECT COALESCE(SUM(cantidad),0) AS TotalEntradas
                  FROM {$prefijobd}valesentradasub
                  WHERE productoEnt_rid = {$id_producto}";
        $runSQL92 = mysql_query($sql92, $cnx_cfdi);
        if ($runSQL92) {
            $row = mysql_fetch_assoc($runSQL92);
            $TotalVEntradas = (float)$row['TotalEntradas'];
        }

        // Vales salidas
        $sql93 = "SELECT COALESCE(SUM(cantidad),0) AS TotalSalidas
                  FROM {$prefijobd}valessalidasub
                  WHERE productov_rid = {$id_producto}";
        $runSQL93 = mysql_query($sql93, $cnx_cfdi);
        if ($runSQL93) {
            $row = mysql_fetch_assoc($runSQL93);
            $TotalVSalidas = (float)$row['TotalSalidas'];
        }

       if ($kitRefacciones === 1) {
        $sql94 = "SELECT COALESCE(SUM(KR.Cantidad),0) AS TotalMantenimiento
                   FROM {$prefijobd}kitrefacciones KR
                   INNER JOIN {$prefijobd}mantenimientos_ref REF ON REF.RID = KR.ID
                   INNER JOIN {$prefijobd}mantenimientos M ON M.ID = REF.ID
                   WHERE KR.Refaccion_RID = {$id_producto}";
         $runSQL94 = mysql_query($sql94, $cnx_cfdi);
         if ($runSQL94) {
             $row = mysql_fetch_assoc($runSQL94);
             $TotalMantenimiento = (float)$row['TotalMantenimiento'];
         }
  
         $TotalExistencia = (($TotalCompras + $TotalVEntradas) - ($TotalVSalidas + $TotalMantenimiento));

         
        } else {
          
          $TotalExistencia = (($TotalCompras + $TotalVEntradas) - $TotalVSalidas);
         
        
       }
       /* compras: SELECT COALESCE(SUM(cantidad),0) AS TotalCompras FROM TPSMORQUECHO_comprassub WHERE productoA_rid = 6893953 entradas: SELECT COALESCE(SUM(cantidad),0) AS TotalEntradas FROM TPSMORQUECHO_valesentradasub WHERE productoEnt_rid = 6893953 salidas: SELECT COALESCE(SUM(cantidad),0) AS TotalSalidas FROM TPSMORQUECHO_valessalidasub WHERE productov_rid = 6893953 */

        //die('compras: '.$sql91.' entradas: '.$sql92.' salidas: '. $sql93); 
        $updateexistencia = "UPDATE {$prefijobd}productos
                             SET Existencia = " . $TotalExistencia . "
                             WHERE Id = {$id_producto}";
        $band_upd = mysql_query($updateexistencia, $cnx_cfdi);

        if ($band_upd) {
            echo "<H2 align='center'>Se actualizo correctamente la existencia del producto.</H2>";
        } else {
            echo "<H2 align='center'>Error al actualizar las existencias: " . mysql_error($cnx_cfdi) . "</H2>";
        }

    } else {
        echo "<H2 align='center'>Seleccione un producto.</H2>";
    }
}


//67.205.112.109/cfdipro/reporte_productos.php?prefijodb=prbisage_&producto=311472

?>