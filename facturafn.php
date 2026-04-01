<?php
 header("Content-Type: text/html;utf-8");

//Realiza la conexion a la base de datos
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

//Genera la consulta para tomar los datos del registro que enviaste
$resIdCFDI = $_GET['facturaid']; //Toma el dato que enviaste desde tu sistema que le pusiste el nombre de varTimbre
$resUsrCFDI = $_GET['usuario'];
$resPrefCFDI = $_GET['prefijo'];
$resSQLcfdi = "SELECT * FROM ".$resPrefCFDI."factura WHERE id = $resIdCFDI";
$runSQLcfdi = mysql_query($resSQLcfdi, $cnx_cfdi); //Corre la consulta
$rowSQLcfdi = mysql_fetch_assoc($runSQLcfdi); //Se guarda los datos de la consulta generada en esta variable

//Genera la consulta para tomar los datos del contribuyente sin datos de WHERE ya que es el unico que esta registrado
$resSQLcontribuyente = "SELECT * FROM ".$resPrefCFDI."systemsettings";
$runSQLcontribuyente = mysql_query($resSQLcontribuyente, $cnx_cfdi); //Corre la consulta
$rowSQLcontribuyente = mysql_fetch_assoc($runSQLcontribuyente); //Se guarda los datos de la consulta generada en esta variable

//Genera la consulta para tomar los datos del cliente
$resIdCliente = $rowSQLcfdi['CargoAFactura_RID']; //Toma los datos de la consuta que generaste, el campo CargoA_RID
$resSQLcliente = "SELECT * FROM ".$resPrefCFDI."clientes WHERE id = $resIdCliente";
echo $resSQLcliente;
$runSQLcliente = mysql_query($resSQLcliente, $cnx_cfdi); //Corre la consulta
$rowSQLcliente = mysql_fetch_assoc($runSQLcliente); //Se guarda los datos de la consulta generada en esta variable

//Genera la consulta para tomar los datos de los estados
$resSQLestados = "SELECT * FROM ".$resPrefCFDI."estados WHERE id = ".$rowSQLcliente['Estado_RID'];
$runSQLestados= mysql_query($resSQLestados, $cnx_cfdi);
$rowSQLestados = mysql_fetch_assoc($runSQLestados);

//Genera la consulta para tomar los datos de la oficina
$resIdoficina = $rowSQLcfdi['Oficina_RID']; //Toma los datos de la consuta que generaste, el campo Oficina_RID
$resSQLoficina = "SELECT * FROM ".$resPrefCFDI."Oficinas WHERE id = $resIdoficina";
$runSQLoficina  = mysql_query($resSQLoficina , $cnx_cfdi); //Corre la consulta
$rowSQLoficina  = mysql_fetch_assoc($runSQLoficina ); //Se guarda los datos de la consulta generada en esta variable

//Genera la consulta para tomar los datos de la factura sub
$resIdsub = $rowSQLcfdi['ID']; //Toma los datos de la consuta que generaste, el campo FacturaSub_RID
$resSQLsub = "SELECT * FROM ".$resPrefCFDI."facturassub WHERE FolioSub_RID = $resIdsub";
$runSQLsub  = mysql_query($resSQLsub, $cnx_cfdi); //Corre la consulta
$rowSQLsub  = mysql_fetch_assoc($runSQLsub); //Se guarda los datos de la consulta generada en esta variable


//Ya vamos a empezar a generar el archivo

$resRuta ="C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\PorTimbrar\\".$rowSQLcfdi['XFolio'].".fy";



$escribir =fopen($resRuta,"w+"); //este comando "fopen" abre el archivo en la variable $escribir
//Y para escribir en el archivo solo se pone el comando fwrite($variable, y lo que quieras escribir entre "" si es un valor o si lo llamas de una variable sin las "" y al final \r\n para que te de una nueva linea de texto como que si te mandara un enter)
fwrite($escribir, "<Documento>\r\n");
fwrite($escribir, "<Comprobante>\r\n");
fwrite($escribir, "serie=".$rowSQLoficina['SerieFiscal']."\r\n"); //en $rowSQLoficina esta toda la tabla de oficina y seleccionamos el campo Serie
fwrite($escribir, "folio=".$rowSQLcfdi['Folio']."\r\n"); //en $rowSQLcfdi esta toda la tabla de factura y seleccionamos el campo Folio
//Inicio para crear la fecha con T
function right($value, $count) //Funcion si quieres las puedes poner hasta arriba o hasta abajo no hay problema no tienen que ir necesariamente aqui las dos izquierda derecha.
	{
    	return substr($value, ($count*-1));
	}
function left($string, $count)
	{
    	return substr($string, 0, $count);
	}

$resFecha = $rowSQLcfdi['Creado']; //Variable que toda el datos de la fecha si es que fuera asi 00/00/0000 00:00:00
$resFecha1 = left($resFecha,10); // Toma la fecha unicamente.
$resFecha2 = right($resFecha,8); //Toma la hora unicamente
$resFechaFinal = $resFecha1."T".$resFecha2;

//Fin para crear la fecha con T

fwrite($escribir, "fecha=".$resFechaFinal."\r\n"); //en $rowSQLcfdi esta toda la tabla de factura y seleccionamos el campo Creado
fwrite($escribir, "TipoDeComprobante=ingreso\r\n");
fwrite($escribir, "TituloDeDocumento=Factura\r\n");
fwrite($escribir, "Formadepago=PAGO EN UNA SOLA EXHIBICION\r\n");
fwrite($escribir, "condicionesDePago=CONTADO\r\n");
fwrite($escribir, "MetodoDePago=".$rowSQLcfdi['feMetodoPago']."\r\n");
fwrite($escribir, "NumCtaPago=".$rowSQLcfdi['feCuentaPago']."\r\n");
fwrite($escribir, "Subtotal=".round($rowSQLcfdi['zSubtotal'],2)."\r\n"); //en $rowSQLcfdi esta toda la tabla de factura y seleccionamos el campo zSubtotal
fwrite($escribir, "Descuento=\r\n");
fwrite($escribir, "motivodescuento=\r\n");
fwrite($escribir, "TipoCambio=".$rowSQLcfdi['TipoCambio']."\r\n");
fwrite($escribir, "moneda=".$rowSQLcfdi['Moneda']."\r\n");
fwrite($escribir, "IVA=".round($rowSQLcfdi['zImpuesto'],2)."\r\n");
fwrite($escribir, "Total=".round($rowSQLcfdi['zTotal'],2)."\r\n");
fwrite($escribir, "</Comprobante>\r\n");

fwrite($escribir, "<Emisor>\r\n");
fwrite($escribir, "erfc=".$rowSQLcontribuyente['RFC']."\r\n");
fwrite($escribir, "enombre=".$rowSQLcontribuyente['RazonSocial']."\r\n");
fwrite($escribir, "RegimenFiscal=".$rowSQLcontribuyente['Regimen']."\r\n");
fwrite($escribir, "ecalle=".$rowSQLcontribuyente['Calle']."\r\n");
fwrite($escribir, "enoExterior=".$rowSQLcontribuyente['NumeroExterior']."\r\n");
fwrite($escribir, "enoInterior=".$rowSQLcontribuyente['NumeroInterior']."\r\n");
fwrite($escribir, "ecolonia=".$rowSQLcontribuyente['Colonia']."\r\n");
fwrite($escribir, "elocalidad=".$rowSQLcontribuyente['Ciudad']."\r\n");
fwrite($escribir, "ereferencia=\r\n");
fwrite($escribir, "emunicipio=".$rowSQLcontribuyente['Municipio']."\r\n");
fwrite($escribir, "EEstado=".$rowSQLcontribuyente['Estado']."\r\n");
fwrite($escribir, "epais=".$rowSQLcontribuyente['Pais']."\r\n");
fwrite($escribir, "ecodigoPostal=".$rowSQLcontribuyente['CodigoPostal']."\r\n");
fwrite($escribir, "etel=".$rowSQLcontribuyente['Telefono']."\r\n");
fwrite($escribir, "eemail=".$rowSQLcontribuyente['Correo']."\r\n");
fwrite($escribir, "</Emisor>\r\n");

fwrite($escribir, "<Receptor>\r\n");
fwrite($escribir, "RFC=".$rowSQLcliente['RFC']."\r\n");
fwrite($escribir, "nombre=".$rowSQLcliente['RazonSocial']."\r\n");
fwrite($escribir, "Calle=".$rowSQLcliente['Calle']."\r\n");
fwrite($escribir, "noExterior=".$rowSQLcliente['NumeroExterior']."\r\n");
fwrite($escribir, "noInterior=".$rowSQLcliente['NumeroInterior']."\r\n");
fwrite($escribir, "colonia=".$rowSQLcliente['Colonia']."\r\n");
fwrite($escribir, "localidad=".$rowSQLcliente['Ciudad']."\r\n");
fwrite($escribir, "Referencia=\r\n");
fwrite($escribir, "municipio=".$rowSQLcliente['Municipio']."\r\n");
fwrite($escribir, "Estado=".$rowSQLestados['Estado']."\r\n");
fwrite($escribir, "pais=".$rowSQLcliente['Pais']."\r\n");
fwrite($escribir, "codigopostal=".$rowSQLcliente['CodigoPostal']."\r\n");
fwrite($escribir, "Tel=".$rowSQLcliente['Telefono']."\r\n");
fwrite($escribir, "email=".$rowSQLcliente['CorreoFactura']."\r\n");
fwrite($escribir, "</Receptor>\r\n");

fwrite($escribir, "<expedidoEn>\r\n");
fwrite($escribir, "r_calle=".$rowSQLcfdi['RemitenteDomicilio']."\r\n");
fwrite($escribir, "r_noExterior=\r\n");
fwrite($escribir, "r_noInterior=\r\n");
fwrite($escribir, "r_colonia=\r\n");
fwrite($escribir, "r_localidad=".$rowSQLcfdi['RemitenteLocalidad']."\r\n");
fwrite($escribir, "r_referencia=\r\n");
fwrite($escribir, "r_municipio=\r\n");
fwrite($escribir, "r_estado=\r\n");
fwrite($escribir, "r_pais=MEXICO\r\n");
fwrite($escribir, "r_CODIGOPOSTAL=".$rowSQLcfdi['RemitenteCodigoPostal']."\r\n");
fwrite($escribir, "</expedidoEn>\r\n");


fwrite($escribir, "<Conceptos>\r\n");
if ($rowSQLcfdi['yFlete']<>0)
{
fwrite($escribir, "p01_cantidad=1\r\n");
fwrite($escribir, "p01_unidad=Servicio\r\n");
fwrite($escribir, "p01_descripcion=FLETE\r\n");
fwrite($escribir, "p01_valorUnitario=".round($rowSQLcfdi['yFlete'],2)."\r\n");
fwrite($escribir, "p01_importe=".round($rowSQLcfdi['yFlete'],2)."\r\n");
fwrite($escribir, "p01_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['ySeguro']<>0)
{
fwrite($escribir, "p02_cantidad=1\r\n");
fwrite($escribir, "p02_unidad=Servicio\r\n");
fwrite($escribir, "p02_descripcion=SEGURO\r\n");
fwrite($escribir, "p02_valorUnitario=".round($rowSQLcfdi['ySeguro'],2)."\r\n");
fwrite($escribir, "p02_importe=".round($rowSQLcfdi['ySeguro'],2)."\r\n");
fwrite($escribir, "p02_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yCarga']<>0)
{
fwrite($escribir, "p03_cantidad=1\r\n");
fwrite($escribir, "p03_unidad=Servicio\r\n");
fwrite($escribir, "p03_descripcion=CARGA\r\n");
fwrite($escribir, "p03_valorUnitario=".round($rowSQLcfdi['yCarga'],2)."\r\n");
fwrite($escribir, "p03_importe=".round($rowSQLcfdi['yCarga'],2)."\r\n");
fwrite($escribir, "p03_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yDescarga']<>0)
{
fwrite($escribir, "p04_cantidad=1\r\n");
fwrite($escribir, "p04_unidad=Servicio\r\n");
fwrite($escribir, "p04_descripcion=DESCARGA\r\n");
fwrite($escribir, "p04_valorUnitario=".round($rowSQLcfdi['yDescarga'],2)."\r\n");
fwrite($escribir, "p04_importe=".round($rowSQLcfdi['yDescarga'],2)."\r\n");
fwrite($escribir, "p04_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yRecoleccion']<>0)
{
fwrite($escribir, "p05_cantidad=1\r\n");
fwrite($escribir, "p05_unidad=Servicio\r\n");
fwrite($escribir, "p05_descripcion=RECOLECCION\r\n");
fwrite($escribir, "p05_valorUnitario=".round($rowSQLcfdi['yRecoleccion'],2)."\r\n");
fwrite($escribir, "p05_importe=".round($rowSQLcfdi['yRecoleccion'],2)."\r\n");
fwrite($escribir, "p05_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yRepartos']<>0)
{
fwrite($escribir, "p06_cantidad=1\r\n");
fwrite($escribir, "p06_unidad=Servicio\r\n");
fwrite($escribir, "p06_descripcion=REPARTOS\r\n");
fwrite($escribir, "p06_valorUnitario=".round($rowSQLcfdi['yRepartos'],2)."\r\n");
fwrite($escribir, "p06_importe=".round($rowSQLcfdi['yRepartos'],2)."\r\n");
fwrite($escribir, "p06_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yAutopistas']<>0)
{
fwrite($escribir, "p07_cantidad=1\r\n");
fwrite($escribir, "p07_unidad=Servicio\r\n");
fwrite($escribir, "p07_descripcion=AUTOPISTAS\r\n");
fwrite($escribir, "p07_valorUnitario=".round($rowSQLcfdi['yAutopistas'],2)."\r\n");
fwrite($escribir, "p07_importe=".round($rowSQLcfdi['yAutopistas'],2)."\r\n");
fwrite($escribir, "p07_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yDemoras']<>0)
{
fwrite($escribir, "p08_cantidad=1\r\n");
fwrite($escribir, "p08_unidad=Servicio\r\n");
fwrite($escribir, "p08_descripcion=DEMORAS\r\n");
fwrite($escribir, "p08_valorUnitario=".round($rowSQLcfdi['yDemoras'],2)."\r\n");
fwrite($escribir, "p08_importe=".round($rowSQLcfdi['yDemoras'],2)."\r\n");
fwrite($escribir, "p08_factorIVA=16%\r\n");
}
if ($rowSQLcfdi['yOtros']<>0)
{
fwrite($escribir, "p09_cantidad=1\r\n");
fwrite($escribir, "p09_unidad=Servicio\r\n");
fwrite($escribir, "p09_descripcion=OTROS\r\n");
fwrite($escribir, "p09_valorUnitario=".round($rowSQLcfdi['yOtros'],2)."\r\n");
fwrite($escribir, "p09_importe=".round($rowSQLcfdi['yOtros'],2)."\r\n");
fwrite($escribir, "p09_factorIVA=16%\r\n");
}
fwrite($escribir, "</Conceptos>\r\n");

fwrite($escribir, "<Otros>\r\n");
fwrite($escribir, "Cant_Letra=".$rowSQLcfdi['TotalLetra']."\r\n");
fwrite($escribir, "FactorIVA=".round($rowSQLcfdi['xIVA'],2)."\r\n");
fwrite($escribir, "factorRet=".round($rowSQLcfdi['xRetencion'],2)."\r\n");
fwrite($escribir, "Observaciones=".$rowSQLcfdi['Comentarios']."\r\n");
fwrite($escribir, "TipoImpresion=3\r\n");
fwrite($escribir, "</Otros>\r\n");
fwrite($escribir, "\r\n");
fwrite($escribir, "</Documento>\r\n");
fclose($escribir);


renombrar_archivo("C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\PorTimbrar\\".$rowSQLcfdi['XFolio'].".fy", "C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\PorTimbrar\\".$rowSQLcfdi['XFolio'].".fx");





//Verifica si existe el archivo y pone true o false en la variable $error
$error = existe("C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\ConError\\".$rowSQLcfdi['XFolio'].".err");


//Si la variable $error es igual a true entonces imprime el error en pantalla me imagino
echo "Procesando...";
if ($error == true)
	{
	
		$ar=fopen("C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\ConError\\".$rowSQLcfdi['XFolio'].".err","r");
		while (!feof($ar))
			{
				$linea=fgets($ar);
			   $lineasalto=nl2br($linea);
			   echo $lineasalto;
			}
		fclose($ar);
		unlink("C:\\FELECTRONICA CFDI\\Conector CFDI\\envio\\ConError\\".$rowSQLcfdi['XFolio'].".err");
	}
	
else{
		//Si la variable $error es igual a false entonces imprime en pantalla esto
		$UpdateFac = "UPDATE ".$resPrefCFDI."factura SET FEDocumentador='".$resUsrCFDI."',FECreado=NOW() WHERE ID=".$resIdCFDI;
		$runUpdateFac = mysql_query($UpdateFac, $cnx_cfdi); //Corre la consulta
		echo "Generado con Exito";
  }



//Funcion para ver si el archivo existe o no

function existe($archivo) 
	{ 
		sleep(5);
		$f=@fopen($archivo,"r"); 
		if($f) 
			{ 
				fclose($f); 
				return true; 
			} 
		return false; 
	} 
  
  
function renombrar_archivo($archivoAnterior,$archivoNuevo) {
  if (!rename($archivoAnterior,$archivoNuevo)) {
    if (copy ($archivoAnterior,$archivoNuevo)) {
      unlink($archivoAnterior);
			echo "<script languaje='javascript' type='text/javascript'>window.open('', '_self', '');window.close();</script>";
      return true;
    }
    return false;
  }
  return true;
}  
?>