<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');//archivos de conexion
mysqli_select_db($cnx_cfdi2,$database_cfdi);//selecciona la base de datos sobre la que se va a trabajar
$prefijobd = $_GET["prefijodb"];//trae prefijo
date_default_timezone_set("America/Mexico_City");//Toma la zona horiaria de CD de Mexico
$time = date('Y-m-d H:i:s');//CURRENT_TIME
$time2 = date_create($time);
$time2 = date_format($time2, 'd/m/Y G:i:s a');
$time2=str_replace("pm","p m",$time2);//Formatea la fecha a como es requerido en la poliza 
$time2=str_replace("am","a m",$time2);

function zero_fill ($valor, $long = 0){//funcion para rellenar ceros a la izquierda
    return str_pad($valor, $long, '0', STR_PAD_LEFT);
}
/*****
 * Funcion AgregaEspacios
 * Agrega la cantidad de espacios necesarios para que la cadena cumpla el tamaño enviado
 */
function AgregaEspacios($cadena, $tamaniototal){
	//Reviso el tamaño de la cadena enviada
	return str_pad($cadena, $tamaniototal); 
}

function AgregaEspaciosIzq($cadena, $tamaniototal){
	//Reviso el tamaño de la cadena enviada
	return str_pad($cadena, $tamaniototal, " ", STR_PAD_LEFT); 
}
$archivotxt = fopen("Poliza_Ingreso_CES.txt", "w+");
$archivo="Poliza_Ingreso_CES.txt";

if(isset($_POST["submit"])){//cuando se presiona el votor enviar... 

    

    $fechaConsulta=$_POST['fechaC'];
    $fechaConsulta=explode("-", $fechaConsulta);//separa la fecha en mes, año, dia... aprovechamos que este delimitada por guiones.
	/******/
	  switch ($fechaConsulta[1]) {
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
	}
	/******/
    $query1 = "SELECT * FROM ".$prefijobd."Factura WHERE cfdiuuid IS NOT NULL AND MONTH(Creado) = '".$fechaConsulta[1]."' AND YEAR(Creado) = '".$fechaConsulta[0]."' ORDER BY Creado;"; 
        $runsql1 = mysqli_query($cnx_cfdi2, $query1);
        if (!$runsql1) {//debug
            $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
            $mensaje .= 'Consulta completa: ' . $query1;
            die($mensaje);
        }
        //die($query1);
        while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
			$idFact = $rowsql1['ID'];
			$query3 = "SELECT * FROM ".$prefijobd."FacturaPartidas WHERE FolioSub_RID ='".$idFact."';"; 
				$runsql3 = mysqli_query($cnx_cfdi2, $query3);
				if (!$runsql3) {//debug
					$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
					$mensaje .= 'Consulta completa: ' . $query3;
					die($mensaje);
				}
				//die($query3);
				while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
					$IVA = $rowsql3['IVA'];
					$Retencion = $rowsql3['Retencion'];
				}
			
            /*$IVA = $rowsql1['xIVA'];
            $Retencion = $rowsql1['xRetencion'];*/
            $xfolio = $rowsql1['XFolio'];
            $ticket = $rowsql1['Ticket'];
            $fchaTimbrado = $rowsql1['cfdifechaTimbrado'];
            $fchaCancelado = $rowsql1['cCanceladoT'];
			//die($fchaTimbrado);
            $subtotal = $rowsql1['zSubtotal1'];
            $impuesto = $rowsql1['zImpuesto'];
            $retenido = $rowsql1['zRetenido'];
            $total = $rowsql1['zTotal'];
            $subtotal2 = $subtotal+$impuesto;

            $subtotal=number_format($subtotal, 2);//formatea numeros a dos decimales y sin coma
            $impuesto=number_format($impuesto, 2);
            $retenido=number_format($retenido, 2);
            $total=number_format($total, 2);
            $subtotal2=number_format($subtotal2, 2);

            $subtotal=str_replace(",","",$subtotal);
            $impuesto=str_replace(",","",$impuesto);
            $retenido=str_replace(",","",$retenido);
            $total=str_replace(",","",$total);
            $subtotal2=str_replace(",","",$subtotal2);

            $fchaTimbrado=explode("-", $fchaTimbrado);
            $diaTimbrado=$fchaTimbrado[2];
            $diaTimbrado=explode("T", $diaTimbrado);//obviene unicamente el dia de timbrado a partir de la fecha de timbrado INDICE[0]			
            
            $cliente = $rowsql1['CargoAFactura_RID'];
                $query2 = "SELECT * FROM ".$prefijobd."Clientes WHERE ID ='".$cliente."';"; 
                $runsql2 = mysqli_query($cnx_cfdi2, $query2);
                if (!$runsql2) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query2;
                    die($mensaje);
                }
                while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
                    $razonSocial = $rowsql2['RazonSocial'];
                    $cuentaContable = $rowsql2['CuentaContable'];
					          $RFC = $rowsql2['RFC'];
                }
			          $query3 = "SELECT * FROM ".$prefijobd."SystemSettings;"; 
                $runsql3 = mysqli_query($cnx_cfdi2, $query3);
                if (!$runsql3) {//debug
                    $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
                    $mensaje .= 'Consulta completa: ' . $query3;
                    die($mensaje);
                }
                while ($rowsql3 = mysqli_fetch_assoc($runsql3)){
                    $cuentaIVARetenido = $rowsql3['ctaIVARetenidoA'];
                    $cuentaIngresos16 = $rowsql3['ctaIngresosA'];
                    $cuentaIngresos0 = $rowsql3['ctaIngresosC'];
					          $cuentaIVA = $rowsql3['ctaIVAA'];
                }

			$razonSocial2=substr($razonSocial, 0, 29);
			
            if($RFC=='GAL900207HI9'){
              $cuentaIngresos16="41010002";
            }

            if($fchaCancelado!=''){
              $linea1="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."                    ".AgregaEspacios("C A N C E L A D O", 50)."0".AgregaEspaciosIzq("0.00",16)." 0                                                               ".$time2."          ";
				      fwrite($archivotxt, $linea1."\n");

              $linea2="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaContable."00000000".AgregaEspacios("C A N C E L A D O", 50)."2".AgregaEspaciosIzq("0.00",16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00".AgregaEspacios($razonSocial2, 53)."".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
              fwrite($archivotxt, $linea2."\n");

              $finRegistro="//";
              fwrite($archivotxt, $finRegistro."\n");

            }

            if((($IVA=='0') AND ($Retencion=='0'))AND $fchaCancelado==''){
              $linea1="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."                    ".AgregaEspacios($razonSocial, 50)."0".AgregaEspaciosIzq($total,16)." 0                                                               ".$time2."          ";
              fwrite($archivotxt, $linea1."\n");

              $linea2="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaContable."00000000".AgregaEspacios($razonSocial, 50)."1".AgregaEspaciosIzq($total,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00".AgregaEspacios($razonSocial2, 53)."".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
              fwrite($archivotxt, $linea2."\n");

              $linea4="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaIngresos0."00".$fechaConsulta[1]."00000000".AgregaEspacios($razonSocial, 50)."2".AgregaEspaciosIzq($total,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00".AgregaEspacios($mes2,53)."".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
              fwrite($archivotxt, $linea4."\n");

              $finRegistro="//";
              fwrite($archivotxt, $finRegistro."\n");

            }

            //if((($IVA=='16') AND ($Retencion=='4'))AND $fchaCancelado==''){
			if(((($IVA=='16') AND ($Retencion=='4')) OR (($IVA=='16') AND ($Retencion=='0')))AND $fchaCancelado==''){
                $linea1="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."                    ".AgregaEspacios($razonSocial, 50)."0".AgregaEspaciosIzq($subtotal2,16)." 0                                                               ".$time2."          ";
                fwrite($archivotxt, $linea1."\n");
                
                $linea2="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaContable."00000000".AgregaEspacios($razonSocial, 50)."1".AgregaEspaciosIzq($total,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00".AgregaEspacios($razonSocial2, 53)."".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
                fwrite($archivotxt, $linea2."\n");
                
                $linea3="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaIVARetenido."000000000000".AgregaEspacios($RFC, 50)."1".AgregaEspaciosIzq($retenido,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00IVA ACRED PND POR RET 4%                             ".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
                fwrite($archivotxt, $linea3."\n");
                
                $linea4="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaIngresos16."00".$fechaConsulta[1]."00000000".AgregaEspacios($razonSocial, 50)."2".AgregaEspaciosIzq($subtotal,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00".AgregaEspacios($mes2,53)."".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
                fwrite($archivotxt, $linea4."\n");
                
                $linea5="".$fechaConsulta[0]."".$fechaConsulta[1]."005".zero_fill($xfolio, 6)."".$diaTimbrado[0]."".$cuentaIVA."000000000000".AgregaEspacios($RFC, 50)."2".AgregaEspaciosIzq($impuesto,16)." 0".AgregaEspaciosIzq($xfolio,11)."                  0.00IVA CAUSADO PND DE COBRO 16%                         ".$time2."                0.00  0.00  0.00    0.0000    ".AgregaEspacios($ticket, 20);
                fwrite($archivotxt, $linea5."\n");
                
                $finRegistro="//";
                fwrite($archivotxt, $finRegistro."\n");
				
            }

        }
 $TheFile = basename($archivo); 
 header( "Content-Type: application/octet-stream"); 
 header( "Content-Length: ".filesize($archivo)); 
 header( "Content-Disposition: attachment; filename=".$TheFile.""); 
 readfile($archivo); 
}



//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Poliza de ingreso CES</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Poliza de ingreso CES</h3><br />
  <form method="post" enctype="multipart/form-data">
        

        
        <div class="form-group">
					<label>Selecciona mes a consultar:</label>
					<input type="date" name="fechaC" id="fechaC" class="form-control">
					<p class="help-block text-danger"></p>
		</div>

        
   <div align="center">  
    <input type="submit" name="submit" value="Generar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

