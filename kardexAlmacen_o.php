<?php  

$fecha_inicio = $_POST["fechai"];
$fecha_fin = $_POST["fechaf"];
$prefijobd = $_POST["base"];
$prefijobd2 = "basdb.".$prefijobd."";
$IDprod = $_POST["prod"];
$IDdocu = $_POST["docu"];


$Wextra1="b.ProductoA_RID";
$Wextra2="b.ProductoEnt_RID";
$Wextra3="b.ProductoV_RID";
$Wextra4="b.Refaccion_RID";

$resSQL="";

//Valida se se selecciono un producto para filtrar
if ($_POST['prod']>0){
  $Wextra1 ="".$IDprod."";
  $Wextra2 ="".$IDprod."";
  $Wextra3 ="".$IDprod."";
  $Wextra4 ="".$IDprod."";
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

$fecha = $dia_logs." de ".$mes2." de ". $anio_logs;

$fecha2 = $anio_logs."-".$mes_logs."-".$dia_logs;

//Elimina la tabla temporal si existe

$resSQLdropTT = "DROP TEMPORARY TABLE ".$prefijobd."temporalKardex";
$runSQLdropTT = mysql_query($resSQLdropTT, $cnx_cfdi);

if (!$runSQLdropTT) {
	$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQLdropTT;
	//echo($mensaje);
}//catch error*/
//$runSQLdropTT=true;
//while($runSQLdropTT){

//Buscar datos para encabezado
$resSQL0 = "SELECT * FROM ".$prefijobd."systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);

if (!$runSQL0) {
	$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQL0;
	die($mensaje);
}//catch error

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

$fechai2 = date("d/m/Y", strtotime($fecha_inicio));
$fechaf2 = date("d/m/Y", strtotime($fecha_fin));

  $html = '
<header class="clearfix">
      <meta charset="utf-8">
      <div id="logo">
		<p><strong>'.$RazonSocial.'</strong> <br>'.$Calle.' '.$NumeroExterior.', '.$Colonia.' <br>'.$Municipio.', '.$Estado.' <br> '.$RFC.' </p>
        <!--<img src="img/img.png" width="150px">-->
      </div>
      <h1 style="font-size: 20px;">Kardex de Almacen desde '.$fechai2.' hasta '.$fechaf2.'</h1>
      <div id="company" class="clearfix">
              
            </div>
            <div id="project">
              <div style="font-size: 15px; text-align: right;"><span>'.$fecha.'</span></div>
              <!--div><br></div-->
              <div>
                <table border="1">
                  <thead>
                  <tr>
                    <th align="center" style="font-size: 12px;">Fecha</th>
                    <th align="center" style="font-size: 12px;">Nombre</th>
                    <th align="center" style="font-size: 12px;">Codigo</th>
                    <th align="center" style="font-size: 12px;">Descripcion</th>
                    <th align="center" style="font-size: 12px;">Entradas</th>
                    <th align="center" style="font-size: 12px;">Salidas</th>                 
                  </tr>
                  </thead>
                  <tbody>';

  $entradas = 0;
  $salidas = 0;
  $total_registros_t=0;


//Valida si se selecciono un filtro para el tipo de documento
if ($_POST['docu'] >0) {
  if ($_POST['docu']==1){
    $resSQLcreateTT="CREATE TEMPORARY TABLE ".$prefijobd."temporalKardex
	SELECT 'compras' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.Nombre, c.ID as IDProd,c.Codigo, b.ProductoA_RID ";
	$resSQLcreateTT.="FROM ".$prefijobd."compras a, ".$prefijobd."comprassub b,  ".$prefijobd."productos c ";
	$resSQLcreateTT.="WHERE a.ID = b.FolioSub_RID 
             
            AND c.ID IN(SELECT ProductoA_RID FROM ".$prefijobd."comprassub WHERE ProductoA_RID = ".$Wextra1.") 
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."compras WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."');
              ";
              $resSQL="SELECT * FROM ".$prefijobd."temporalKardex WHERE IDProd = ProductoA_RID AND IDMain = FolioSub_RID ORDER BY Fecha";
			
  }elseif($_POST['docu']==2){
    $resSQLcreateTT="CREATE TEMPORARY TABLE ".$prefijobd."temporalKardex
	SELECT 'entradas' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.Nombre, c.ID as IDProd,c.Codigo ,b.ProductoEnt_RID ";
	$resSQLcreateTT.="FROM ".$prefijobd."valesentrada a, ".$prefijobd."valesentradasub b,  ".$prefijobd."productos c ";
	$resSQLcreateTT.="WHERE a.ID= b.FolioSub_RID 
            
            AND c.ID IN(SELECT ProductoEnt_RID FROM ".$prefijobd."valesentradasub WHERE ProductoEnt_RID = ".$Wextra2.")  
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."valesentrada WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."') ;
             ";
             $resSQL="SELECT * FROM ".$prefijobd."temporalKardex WHERE IDProd = ProductoEnt_RID AND IDMain = FolioSub_RID ORDER BY Fecha";
			
  }elseif($_POST['docu']==3){
    $resSQLcreateTT="CREATE TEMPORARY TABLE ".$prefijobd."temporalKardex
	SELECT 'salidas' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.Nombre, c.ID as IDProd,c.Codigo , b.ProductoV_RID  ";
	$resSQLcreateTT.="FROM ".$prefijobd."valessalida a, ".$prefijobd."valessalidasub b,  ".$prefijobd."productos c ";
	$resSQLcreateTT.="WHERE a.ID= b.FolioSub_RID 
             
            AND c.ID IN(SELECT ProductoV_RID FROM ".$prefijobd."valessalidasub WHERE ProductoV_RID = ".$Wextra3.") 
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."valessalida WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."')
                ";
                $resSQL="SELECT * FROM ".$prefijobd."temporalKardex WHERE IDProd = ProductoV_RID AND IDMain = FolioSub_RID ORDER BY Fecha";
  }elseif($_POST['docu']==4){
    $resSQLcreateTT="CREATE TEMPORARY TABLE ".$prefijobd."temporalKardex
	SELECT 'mantenimientos' as Tabla, a.ID as IDMain, d.ID as FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.Nombre, c.ID as IDProd, c.Codigo , b.Refaccion_RID  ";
	$resSQLcreateTT.="FROM ".$prefijobd."mantenimientos a, ".$prefijobd."kitrefacciones b,  ".$prefijobd."productos c,  ".$prefijobd."mantenimientos_ref d  ";
	$resSQLcreateTT.="WHERE a.ID= d.ID AND d.RID=b.ID AND b.Refaccion_RID=c.ID

             
            AND c.ID IN(SELECT Refaccion_RID FROM ".$prefijobd."kitrefacciones WHERE Refaccion_RID = ".$Wextra4.") 
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."mantenimientos WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."')
                ";
                $resSQL="SELECT * FROM ".$prefijobd."temporalKardex WHERE IDProd = Refaccion_RID AND IDMain = FolioSub_RID ORDER BY Fecha";

                //echo "Docu4";
                //echo "<br>";
  }
  //echo $resSQLcreateTT; 
}else{


  //consulta principal; une las tablas compras, valesentradas y valessalida
  $resSQLcreateTT="
          CREATE TEMPORARY TABLE ".$prefijobd."temporalKardex 
          SELECT 'compras' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.ID as IDProd,c.Codigo, b.ProductoA_RID as ProductoA_RID , c.Nombre ";
  $resSQLcreateTT.="FROM ".$prefijobd."compras a, ".$prefijobd."comprassub b,  ".$prefijobd."productos c ";
  $resSQLcreateTT.="WHERE a.ID = b.FolioSub_RID 
          AND c.ID IN(SELECT ProductoA_RID FROM ".$prefijobd."comprassub WHERE ProductoA_RID = ".$Wextra1.")
          AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."compras WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."')
                ";
  $resSQLcreateTT.="UNION ";
  $resSQLcreateTT.="SELECT 'entradas' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.ID as IDProd, c.Codigo,b.ProductoEnt_RID as ProductoA_RID , c.Nombre ";
  $resSQLcreateTT.="FROM ".$prefijobd."valesentrada a, ".$prefijobd."valesentradasub b,  ".$prefijobd."productos c ";
  $resSQLcreateTT.="WHERE a.ID= b.FolioSub_RID       
            AND c.ID IN(SELECT ProductoEnt_RID FROM ".$prefijobd."valesentradasub WHERE ProductoEnt_RID = ".$Wextra2.") 
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."valesentrada WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."') 
              ";
  $resSQLcreateTT.="UNION ";
  $resSQLcreateTT.="SELECT 'salidas' as Tabla, a.ID as IDMain, b.FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.ID as IDProd, c.Codigo,b.ProductoV_RID as ProductoA_RID , c.Nombre ";
  $resSQLcreateTT.="FROM ".$prefijobd."valessalida a, ".$prefijobd."valessalidasub b,  ".$prefijobd."productos c ";
  $resSQLcreateTT.="WHERE a.ID= b.FolioSub_RID 
            AND c.ID IN(SELECT ProductoV_RID FROM ".$prefijobd."valessalidasub WHERE ProductoV_RID = ".$Wextra3.")  
            AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."valessalida WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."')
            ";
  $resSQLcreateTT.="UNION ";
  $resSQLcreateTT.="SELECT 'mantenimientos' as Tabla, a.ID as IDMain, d.ID as FolioSub_RID, a.xfolio, a.Fecha, b.Cantidad, c.ID as IDProd, c.Codigo, b.Refaccion_RID as ProductoA_RID , c.Nombre ";
  $resSQLcreateTT.="FROM ".$prefijobd."mantenimientos a, ".$prefijobd."kitrefacciones b,  ".$prefijobd."productos c, ".$prefijobd."mantenimientos_ref d ";
  $resSQLcreateTT.="WHERE a.ID= d.ID AND d.RID=b.ID AND b.Refaccion_RID=c.ID
             AND c.ID IN(SELECT Refaccion_RID FROM ".$prefijobd."kitrefacciones WHERE Refaccion_RID = ".$Wextra4.")  
             AND a.Fecha IN(SELECT Fecha FROM ".$prefijobd."mantenimientos WHERE Date(a.Fecha) Between '".$_POST["fechai"]."' And '".$_POST["fechaf"]."')
             ";
  $resSQL="SELECT * FROM ".$prefijobd."temporalKardex WHERE IDMain = FolioSub_RID ORDER BY Fecha";


  
}
$runSQLcreateTT = mysql_query($resSQLcreateTT, $cnx_cfdi);
if (!$runSQLcreateTT) {
	$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
	$mensaje .= 'Consulta completa: ' . $resSQLcreateTT;
	echo($mensaje);
}//catch error


while ($runSQLcreateTT){

  
	$runSQL=mysql_query($resSQL, $cnx_cfdi);

	if (!$runSQL) {
		$mensaje  = 'Consulta no válida: ' . mysql_error() . "\n";
		$mensaje .= 'Consulta completa: ' . $resSQL;
		die($mensaje);
	}
	while ($rowSQL=mysql_fetch_array($runSQL)){
		//Obtener_variables
        $xfolioG = $rowSQL['xfolio'];
        $fechaG1 = $rowSQL['Fecha'];
        $fechaG = date("d-m-Y", strtotime($fechaG1));
        $cantidadG = $rowSQL['Cantidad'];
        $nombreG = $rowSQL['Nombre'];
        $codigoG = $rowSQL['Codigo'];
        /*$Tabla = $rowSQL['Tabla'];*/
			  
          //Buscar Compras
			if ((($rowSQL['Tabla']) == ("compras")) || (($rowSQL['Tabla']) == ("entradas"))) {
        $entradas = $entradas + $cantidadG;
        $total_registros_t = $total_registros_t +1;
        if(($rowSQL['Tabla']) == ("compras")){
          $entrada_descripcion = "Compra: ".$xfolioG;
          //buscar Vales de Entrada
        }else{
          $entrada_descripcion = "Vale de Entrada: ".$xfolioG;
        }

                
				$html.='
					<tr>
              <td align="center">'.$fechaG.'</td>
              <td align="center">'.$nombreG.'</td>
              <td align="center">'.$codigoG.'</td>
              <td align="left">'.$entrada_descripcion.'</td>
              <td align="center">'.$cantidadG.'</td>
              <td align="center">0.00</td>
          </tr>

                    ';
			
				//Buscar Vales de Salida	
      }elseif((($rowSQL['Tabla']) == ("salidas")) || (($rowSQL['Tabla']) == ("mantenimientos"))){

        //echo "ROW: ".$rowSQL['Tabla'];
        //echo "<br>";
        //echo "<br>";
        //echo "DOC No:".$IDdocu;
        
              $salidas = $salidas + $cantidadG;
					    $total_registros_t = $total_registros_t +1;

              if(($rowSQL['Tabla']) == ("salidas")){
                $vale_salida_descripcion = "Vale de Salida: ".$xfolioG;
                //buscar Vales de Entrada
              }else{
                $vale_salida_descripcion = "Mantenimiento: ".$xfolioG;
              }

              
              $html.='
					<tr>
              <td align="center">'.$fechaG.'</td>
              <td align="center">'.$nombreG.'</td>
              <td align="center">'.$codigoG.'</td>
              <td align="left">'.$vale_salida_descripcion.'</td>
              <td align="center">0.00</td>
              <td align="center">'.$cantidadG.'</td>
          </tr>

                    ';
      }
				//////Agregar Totales 
					


//echo $html;
					}//llave de consulta principal
          $runSQLcreateTT=false;
        }
        mysql_free_result($resSQLcreateTT);
        /*$runSQLdropTT=false;
      }*/
          					
        $total_registros = number_format($total_registros_t,0);
        $total_entradas = number_format($entradas,2);
        $total_salidas = number_format($salidas,2);	
				$html.='     
        <tr>
          <!--td colspan="6"><hr></td-->
        </tr>
        </tbody>
              </table>  
        <tr>
        <td align="left" width="100"><strong>TOTAL REGISTROS:</strong></td>
        <td align="left" width="30">'.$total_registros.'</td>
        <td align="center" width="100"><strong>ENTRADAS:</strong></td>
        <td align="center" width="30">'.$total_entradas.'</td>
        <td align="right" width="100"><strong>SALIDAS:</strong></td>
        <td align="right" width="30">'.$total_salidas.'</td>
        <td align="center"> </td>
        </tr>
          
      ';


        $html.='     
                 
                
            </div>
            </div>

            <!--div><br></div-->

            ';


    
        $html.='</header>';
          //echo $html;


$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
//$mpdf->setFooter('Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('kardexDeAlmacen_'.$anio_logs.'_'.$mes_logs.'_'.$dia_logs.'.pdf', 'I');



?>