<?php  

//Recibir variable
$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["prefijodb"];
$id_banco = $_POST["banco"];
$sucursal = $_POST["sucursal"];//trae sucursal

$boton = $_POST["btnGenerar"];

/*if ($v_serie != "") {
    //echo "Variable definida!!!";
	$sql_serie = "AND F.Serie = '".$v_serie."' ";
}else{
	//echo "Variable NO definida!!!";
	$sql_serie = "";
}*/


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

require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
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
}


//Buscar datos de Banco
$resSQLBanco = "SELECT * FROM ".$prefijobd."bancos WHERE ID=".$id_banco;
$runSQLBanco = mysql_query($resSQLBanco, $cnx_cfdi);
while($rowSQLBanco = mysql_fetch_array($runSQLBanco)){
	$nom_banco = $rowSQLBanco['Banco'];
}


$html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Estado de Cuenta</h1>
	  <div>
		<p><strong>Fecha Inicio: </strong>'.$fecha_inicio_t.'<br>
		<strong>Fecha Fin: </strong>'.$fecha_fin_t.'<br>
		<strong>Banco: </strong>'.$nom_banco.'</p>
      </div>
';


       
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
                      <th align="center" style="font-size: 12px;">Tipo Documento</th>
					  <th align="center" style="font-size: 12px;">Detalle</th>
					  <th align="center" style="font-size: 12px;">Folio</th>
                      <th align="center" style="font-size: 12px;">Comentario</th>
                      <th align="center" style="font-size: 12px;">Cargo</th>
                      <th align="center" style="font-size: 12px;">Abono</th>
                      <th align="center" style="font-size: 12px;">Saldo</th>
                    </tr>
                  </thead>
                  <tbody>';


               $saldo = 0;
				$resSQL01 = "SELECT A.ID as ID, A.Fecha as Fecha, 'ABONO' as tipo, A.XFolio as Folio, A.Comentarios as Comentario, A.TotalImporte as Importe, C.RazonSocial as detalle FROM ".$prefijobd."abonos A, ".$prefijobd."clientes C  WHERE A.CuentaBancaria_RID = ".$id_banco." AND Date(A.Fecha) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND A.Depositado=1 AND A.Cliente_RID=C.ID UNION SELECT G.ID as ID, G.Fecha as Fecha, 'DEP OPERADOR' as tipo, G.XFolio as Folio, G.Concepto as Comentario, G.Importe as Importe, CONCAT(' ', O.Operador, ' / ', G.XFolio) as detalle FROM ".$prefijobd."gastosviajes G, ".$prefijobd."operadores O  WHERE G.TransferenciaBanco_RID = ".$id_banco." AND Date(G.Fecha) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND G.Depositado=1 AND G.OperadorNombre_RID=O.ID UNION SELECT ID as ID, FechaMovimiento as Fecha, 'DEPOSITO' as tipo, Folio as Folio, Concepto as Comentario, Monto as Importe, '' as detalle FROM ".$prefijobd."depositos WHERE Banco_RID = ".$id_banco." AND Date(FechaMovimiento) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND Depositado=1 UNION SELECT ID as ID, FechaMovimiento as Fecha, 'RETIRO' as tipo, Folio as Folio, Concepto as Comentario, Monto as Importe, '' as detalle FROM ".$prefijobd."retiros WHERE Banco_RID = ".$id_banco." AND Date(FechaMovimiento) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND Depositado=1 UNION SELECT A.ID as ID, A.Fecha as Fecha, 'PAGO PROVEEDOR' as tipo, A.XFolio as Folio, A.Comentarios as Comentario, A.Total as Importe, P.RazonSocial as detalle FROM ".$prefijobd."pagos A, ".$prefijobd."proveedores P WHERE A.Banco_RID = ".$id_banco." AND Date(A.Fecha) Between '".$_POST["fechai"]." 00:00:00' AND '".$_POST["fechaf"]." 23:59:59' AND A.Depositado=1 AND A.Proveedor_RID=P.ID ORDER BY ID,Fecha";
        //echo $resSQL01;
        //echo "<br>";
        //echo "Reporte: reporte_edo_cuenta_bancos_sucursal.php";
				$runSQL01 = mysql_query($resSQL01, $cnx_cfdi);
				while($rowSQL01 = mysql_fetch_array($runSQL01)){
					$v_abono = 0;
					$v_cargo = 0;
					$v_fecha_t = $rowSQL01['Fecha'];
					$v_fecha = date("d-m-Y", strtotime($v_fecha_t));
					$v_tipo = $rowSQL01['tipo'];
					$v_folio = $rowSQL01['Folio'];
					$v_comentario = $rowSQL01['Comentario'];
					$v_detalle = $rowSQL01['detalle'];
					$v_importe = $rowSQL01['Importe'];
					
					if($v_tipo == 'ABONO' || $v_tipo == 'DEPOSITO'){
						$v_abono = $v_importe;
						$saldo= $saldo + $v_importe;
					} else{
						$v_cargo = $v_importe;
						$saldo= $saldo - $v_importe;
					}
					
				
						
				
                $html.='
                    <tr>
					  <td align="center">'.$v_fecha.'</td>
                      <td align="left">'.$v_tipo.'</td>
					  <td align="left">'.$v_detalle.'</td>
					  <td align="center">'.$v_folio.'</td>
                      <td align="left">'.$v_comentario.'</td>
                      <td align="right">'.$v_cargo.'</td>
                      <td align="right">'.$v_abono.'</td>
                      <td align="right" >'.$saldo.'</td>
                      

                    </tr>

                    ';
					

                    
                  } // FIN del WHILE $resSQL01

                  



              $html.='     
                   
                  </tbody>
                </table>  
              </div>

              <div><br></div>
              ';



           

          
$html.='</header>';







$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Estado_Cuenta_'.$fecha2.'.pdf', 'I');


?>