<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('cnx_cfdi2.php');require_once('cnx_cfdi.php');//archivos de conexion
mysqli_select_db($cnx_cfdi2,$database_cfdi);//selecciona la base de datos sobre la que se va a trabajar
$prefijobd = $_GET["prefijodb"];//trae prefijo
date_default_timezone_set("America/Mexico_City");//Toma la zona horiaria de CD de Mexico
$time = date('Y-m-d H:i:s');//CURRENT_TIME
$time2 = date_create($time);
$time2 = date_format($time2, 'd/m/Y G:i:s a');
$cont=1;

$time = explode(" ", $time);
$timePoliza=$time[0];
$timePoliza=str_replace("-","",$timePoliza);//obtiene la fecha que se agrega a la poliza
//die($timePoliza);



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
$archivotxt = fopen("poliza-Facturacion.txt", "w+");
$archivo="poliza-Facturacion.txt";

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
    $query1 = "SELECT * FROM ".$prefijobd."Factura WHERE cfdiuuid IS NOT NULL AND Date(Creado) Between '".$_POST["fechai"]." 00:00:00' And '".$_POST["fechaf"]." 23:59:59' AND (cCanceladoT IS NULL) ORDER BY Creado;"; 
        $runsql1 = mysqli_query($cnx_cfdi2, $query1);
        if (!$runsql1) {//debug
            $mensaje  = 'Consulta no válida: ' . mysql_error() . "\n"; 
            $mensaje .= 'Consulta completa: ' . $query1;
            die($mensaje);
        }
        //die($query1);
        while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
			      $idFact = $rowsql1['ID'];

            $xfolio = $rowsql1['XFolio'];
            $cfdfolio = $rowsql1['cfdfolio'];
            $cfdserie = $rowsql1['cfdserie'];
            $impuesto = $rowsql1['zImpuesto'];
            $retenido = $rowsql1['zRetenido'];
            $total = $rowsql1['zTotal'];
            $flete = $rowsql1['yFlete'];
            $carga = $rowsql1['yCarga'];
            $descarga = $rowsql1['yDescarga'];
            $recoleccion = $rowsql1['yRecoleccion'];
            $autopistas = $rowsql1['yAutopistas'];
            $seguro = $rowsql1['ySeguro'];
            $otros = $rowsql1['yOtros'];
            $demoras = $rowsql1['yDemoras'];

            $impuesto=number_format($impuesto, 2);
            $retenido=number_format($retenido, 2);
            $total=number_format($total, 2);
            $flete=number_format($flete, 2);
            $carga=number_format($carga, 2);
            $descarga=number_format($descarga, 2);
            $recoleccion=number_format($recoleccion, 2);
            $autopistas=number_format($autopistas, 2);
            $seguro=number_format($seguro, 2);
            $otros=number_format($otros, 2);
            $demoras=number_format($demoras, 2);

            $impuesto=str_replace(",","",$impuesto);
            $retenido=str_replace(",","",$retenido);
            $total=str_replace(",","",$total);
            $flete=str_replace(",","",$flete);
            $carga=str_replace(",","",$carga);
            $descarga=str_replace(",","",$descarga);
            $recoleccion=str_replace(",","",$recoleccion);
            $autopistas=str_replace(",","",$autopistas);
            $seguro=str_replace(",","",$seguro);
            $otros=str_replace(",","",$otros);
            $demoras=str_replace(",","",$demoras);

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
			
    
    $datosFactura="Factura FolioSat: ".$cfdserie." ".$cfdfolio." / FolioTractoSoft: ".$xfolio."";
		$linea1="P  ".$_POST["poliza"]."    9".AgregaEspaciosIzq($cont,10)." 1 0          ".AgregaEspacios($datosFactura,100)." 11 0 0 ";

    fwrite($archivotxt, $linea1."\n");

    $linea2="M  ".$cuentaContable."                                   0 ".AgregaEspacios($total,21)."0          0.0                  CLIENTE                                                                                                   ";
    fwrite($archivotxt, $linea2."\n");

    if($flete>0){
      $linea3="M  4001000                                   1 ".AgregaEspacios($flete,21)."0          0.0                  Flete                                                                                                     ";
      fwrite($archivotxt, $linea3."\n");
    }

    if($seguro>0){
      $linea10="M  4002000                                   1 ".AgregaEspacios($seguro,21)."0          0.0                  Seguro                                                                                                    ";
      fwrite($archivotxt, $linea10."\n");
    }

    if($carga>0){
      $linea4="M  4003000                                   1 ".AgregaEspacios($carga,21)."0          0.0                  Carga                                                                                                     ";
      fwrite($archivotxt, $linea4."\n");
    }

    if($descarga>0){
      $linea5="M  4003000                                   1 ".AgregaEspacios($descarga,21)."0          0.0                  Descarga                                                                                                  ";
      fwrite($archivotxt, $linea5."\n");
    }

    if($recoleccion>0){
      $linea6="M  4001000                                   1 ".AgregaEspacios($recoleccion,21)."0          0.0                  Recoleccion                                                                                               ";
      fwrite($archivotxt, $linea6."\n");
    }

    if($autopistas>0){
      $linea7="M  4004000                                   1 ".AgregaEspacios($autopistas,21)."0          0.0                  Autopistas                                                                                                ";
      fwrite($archivotxt, $linea7."\n");
    }

    if($otros>0){
      $linea11="M  4005000                                   1 ".AgregaEspacios($otros,21)."0          0.0                  Otros                                                                                                     ";
      fwrite($archivotxt, $linea11."\n");
    }

    if($demoras>0){
      $linea12="M  4006000                                   1 ".AgregaEspacios($demoras,21)."0          0.0                  Demoras                                                                                                   ";
      fwrite($archivotxt, $linea12."\n");
    }


    $linea8="M  ".$cuentaIVARetenido."                                   0 ".AgregaEspacios($retenido,21)."0          0.0                  RETENCION                                                                                                 ";
    fwrite($archivotxt, $linea8."\n");

    $linea9="M  ".$cuentaIVA."                                   1 ".AgregaEspacios($impuesto,21)."0          0.0                  IMPUESTO                                                                                                  ";
    fwrite($archivotxt, $linea9."\n");
    $cont++;

 }
 $nomArchivo = "poliza_Facturacion_Periodo_".$_POST["fechai"]."__".$_POST["fechaf"].".txt"; 
 header( "Content-Type: application/octet-stream"); 
 header( "Content-Length: ".filesize($archivo)); 
 header( "Content-Disposition: attachment; filename=".$nomArchivo.""); 
 readfile($archivo); 
}



//abajo esta el front
?>

<!DOCTYPE html>  
<html>  
 <head>  
  <title>Poliza de ingreso Magaña</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
 </head>  
 <body>  
  <h3 align="center">Poliza de ingreso</h3><br />
  <form method="post" enctype="multipart/form-data">
         
      <div class="col-md-6">
				<div class="form-group">
					<label>Fecha Inicio:</label>
					<input type="date" name="fechai" id="fechai" class="form-control" required="required">
					<p class="help-block text-danger"></p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Fecha Fin:</label>
					<input type="date" name="fechaf" id="fechaf" class="form-control" required="required">
					<p class="help-block text-danger"></p>
				</div>

			</div>
      <div>
          <label>Poliza:</label>
					<input type="text" name="poliza" id="poliza" placeholder="<?php echo $timePoliza; ?>" value="<?php echo $timePoliza; ?>" minlength="8" maxlength="8" required>
			</div>

        
   <div align="center">  
    <input type="submit" name="submit" value="Generar" class="btn btn-info" />
   </div>
  </form>
 </body>  
</html>

