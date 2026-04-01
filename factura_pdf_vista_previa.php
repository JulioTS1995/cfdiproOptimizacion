<?php
require_once('lib_mpdf/pdf/mpdf.php');


require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
mysql_query("SET NAMES 'utf8'");
$idfactura = $_GET["id"];
$prefijodb = $_GET["prefijodb"];

//Funcion convierte numero en letra

function convertirNumeroLetra($numero){
    $numf = milmillon($numero);
    return $numf." PESOS";
}

function milmillon($nummierod){
        if ($nummierod >= 1000000000 && $nummierod <2000000000){
            $num_letrammd = "MIL ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod <10000000000){
            $num_letrammd = unidad(Floor($nummierod/1000000000))." MIL ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod < 1000000000)
            $num_letrammd = cienmillon($nummierod);
        
        return $num_letrammd;
}

function cienmillon($numcmeros){
        if ($numcmeros == 100000000)
            $num_letracms = "CIEN MILLONES";
        if ($numcmeros >= 100000000 && $numcmeros <1000000000){
            $num_letracms = centena(Floor($numcmeros/1000000))." MILLONES ".(millon($numcmeros%1000000));       
        }
        if ($numcmeros < 100000000)
            $num_letracms = decmillon($numcmeros);
        return $num_letracms;
}

function decmillon($numerodm){
        if ($numerodm == 10000000)
            $num_letradmm = "DIEZ MILLONES";
        if ($numerodm > 10000000 && $numerodm <20000000){
            $num_letradmm = decena(Floor($numerodm/1000000))."MILLONES ".(cienmiles($numerodm%1000000));        
        }
        if ($numerodm >= 20000000 && $numerodm <100000000){
            $num_letradmm = decena(Floor($numerodm/1000000))." MILLONES ".(millon($numerodm%1000000));      
        }
        if ($numerodm < 10000000)
            $num_letradmm = millon($numerodm);
        
        return $num_letradmm;
}

function millon($nummiero){
        if ($nummiero >= 1000000 && $nummiero <2000000){
            $num_letramm = "UN MILLON ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero >= 2000000 && $nummiero <10000000){
            $num_letramm = unidad(Floor($nummiero/1000000))." MILLONES ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero < 1000000)
            $num_letramm = cienmiles($nummiero);
        
        return $num_letramm;
}

function cienmiles($numcmero){
        if ($numcmero == 100000)
            $num_letracm = "CIEN MIL";
        if ($numcmero >= 100000 && $numcmero <1000000){
            $num_letracm = centena(Floor($numcmero/1000))." MIL ".(centena($numcmero%1000));        
        }
        if ($numcmero < 100000)
            $num_letracm = decmiles($numcmero);
        return $num_letracm;
}

function decmiles($numdmero){
        if ($numdmero == 10000)
            $numde = "DIEZ MIL";
        if ($numdmero > 10000 && $numdmero <20000){
            $numde = decena(Floor($numdmero/1000))."MIL ".(centena($numdmero%1000));        
        }
        if ($numdmero >= 20000 && $numdmero <100000){
            $numde = decena(Floor($numdmero/1000))." MIL ".(miles($numdmero%1000));     
        }       
        if ($numdmero < 10000)
            $numde = miles($numdmero);
        
        return $numde;
}

function miles($nummero){
        if ($nummero >= 1000 && $nummero < 2000){
            $numm = "MIL ".(centena($nummero%1000));
        }
        if ($nummero >= 2000 && $nummero <10000){
            $numm = unidad(Floor($nummero/1000))." MIL ".(centena($nummero%1000));
        }
        if ($nummero < 1000)
            $numm = centena($nummero);
        
        return $numm;
}

function centena($numc){
        if ($numc >= 100)
        {
            if ($numc >= 900 && $numc <= 999)
            {
                $numce = "NOVECIENTOS ";
                if ($numc > 900)
                    $numce = $numce.(decena($numc - 900));
            }
            else if ($numc >= 800 && $numc <= 899)
            {
                $numce = "OCHOCIENTOS ";
                if ($numc > 800)
                    $numce = $numce.(decena($numc - 800));
            }
            else if ($numc >= 700 && $numc <= 799)
            {
                $numce = "SETECIENTOS ";
                if ($numc > 700)
                    $numce = $numce.(decena($numc - 700));
            }
            else if ($numc >= 600 && $numc <= 699)
            {
                $numce = "SEISCIENTOS ";
                if ($numc > 600)
                    $numce = $numce.(decena($numc - 600));
            }
            else if ($numc >= 500 && $numc <= 599)
            {
                $numce = "QUINIENTOS ";
                if ($numc > 500)
                    $numce = $numce.(decena($numc - 500));
            }
            else if ($numc >= 400 && $numc <= 499)
            {
                $numce = "CUATROCIENTOS ";
                if ($numc > 400)
                    $numce = $numce.(decena($numc - 400));
            }
            else if ($numc >= 300 && $numc <= 399)
            {
                $numce = "TRESCIENTOS ";
                if ($numc > 300)
                    $numce = $numce.(decena($numc - 300));
            }
            else if ($numc >= 200 && $numc <= 299)
            {
                $numce = "DOSCIENTOS ";
                if ($numc > 200)
                    $numce = $numce.(decena($numc - 200));
            }
            else if ($numc >= 100 && $numc <= 199)
            {
                if ($numc == 100)
                    $numce = "CIEN ";
                else
                    $numce = "CIENTO ".(decena($numc - 100));
            }
        }
        else
            $numce = decena($numc);
        
        return $numce;  
}


function decena($numdero){
    
        if ($numdero >= 90 && $numdero <= 99)
        {
            $numd = "NOVENTA ";
            if ($numdero > 90)
                $numd = $numd."Y ".(unidad($numdero - 90));
        }
        else if ($numdero >= 80 && $numdero <= 89)
        {
            $numd = "OCHENTA ";
            if ($numdero > 80)
                $numd = $numd."Y ".(unidad($numdero - 80));
        }
        else if ($numdero >= 70 && $numdero <= 79)
        {
            $numd = "SETENTA ";
            if ($numdero > 70)
                $numd = $numd."Y ".(unidad($numdero - 70));
        }
        else if ($numdero >= 60 && $numdero <= 69)
        {
            $numd = "SESENTA ";
            if ($numdero > 60)
                $numd = $numd."Y ".(unidad($numdero - 60));
        }
        else if ($numdero >= 50 && $numdero <= 59)
        {
            $numd = "CINCUENTA ";
            if ($numdero > 50)
                $numd = $numd."Y ".(unidad($numdero - 50));
        }
        else if ($numdero >= 40 && $numdero <= 49)
        {
            $numd = "CUARENTA ";
            if ($numdero > 40)
                $numd = $numd."Y ".(unidad($numdero - 40));
        }
        else if ($numdero >= 30 && $numdero <= 39)
        {
            $numd = "TREINTA ";
            if ($numdero > 30)
                $numd = $numd."Y ".(unidad($numdero - 30));
        }
        else if ($numdero >= 20 && $numdero <= 29)
        {
            if ($numdero == 20)
                $numd = "VEINTE ";
            else
                $numd = "VEINTI".(unidad($numdero - 20));
        }
        else if ($numdero >= 10 && $numdero <= 19)
        {
            switch ($numdero){
            case 10:
            {
                $numd = "DIEZ ";
                break;
            }
            case 11:
            {               
                $numd = "ONCE ";
                break;
            }
            case 12:
            {
                $numd = "DOCE ";
                break;
            }
            case 13:
            {
                $numd = "TRECE ";
                break;
            }
            case 14:
            {
                $numd = "CATORCE ";
                break;
            }
            case 15:
            {
                $numd = "QUINCE ";
                break;
            }
            case 16:
            {
                $numd = "DIECISEIS ";
                break;
            }
            case 17:
            {
                $numd = "DIECISIETE ";
                break;
            }
            case 18:
            {
                $numd = "DIECIOCHO ";
                break;
            }
            case 19:
            {
                $numd = "DIECINUEVE ";
                break;
            }
            }   
        }
        else
            $numd = unidad($numdero);
    return $numd;
}

function unidad($numuero){
    switch ($numuero)
    {
        case 9:
        {
            $numu = "NUEVE";
            break;
        }
        case 8:
        {
            $numu = "OCHO";
            break;
        }
        case 7:
        {
            $numu = "SIETE";
            break;
        }       
        case 6:
        {
            $numu = "SEIS";
            break;
        }       
        case 5:
        {
            $numu = "CINCO";
            break;
        }       
        case 4:
        {
            $numu = "CUATRO";
            break;
        }       
        case 3:
        {
            $numu = "TRES";
            break;
        }       
        case 2:
        {
            $numu = "DOS";
            break;
        }       
        case 1:
        {
            $numu = "UN";
            break;
        }       
        case 0:
        {
            $numu = "";
            break;
        }       
    }
    return $numu;   
}


//FIN Funcion convierte numero en letra

 
$anio_logs = date('Y');
$mes_logs = date('m');
$dia_logs = date('d');

$fecha_actual = $dia_logs."-".$mes_logs."-".$anio_logs;

//Consulta SystemSettings
$sql_01="SELECT * FROM ".$prefijodb."systemsettings";
$res_01=mysql_query($sql_01);
$fila_01=mysql_fetch_array($res_01);

    $s_Regimen = $fila_01['Regimen'];
	$s_RFC = $fila_01['RFC'];
	$s_RazonSocial = $fila_01['RazonSocial'];
	$s_Calle = $fila_01['Calle'];
	$s_NumeroExterior = $fila_01['NumeroExterior'];
	$s_Colonia = $fila_01['Colonia'];
	$s_Ciudad = $fila_01['Ciudad'];
	$s_Municipio = $fila_01['Municipio'];
	$s_Estado = $fila_01['Estado'];
	$s_Pais = $fila_01['Pais'];
	$s_CodigoPostal = $fila_01['CodigoPostal'];
	$s_Telefono = $fila_01['Telefono'];
	$s_Web = $fila_01['Web'];
	
    
	
//Consulta Datos Factura
$sql_02="SELECT * FROM ".$prefijodb."factura WHERE ID = ".$idfactura;
$res_02=mysql_query($sql_02);
$fila_02=mysql_fetch_array($res_02);

    $f_cfdserie = $fila_02['cfdserie'];
	$f_cfdfolio = $fila_02['cfdfolio'];
	$f_FECreado_t = $fila_02['FECreado'];
	$f_XFolio = $fila_02['XFolio'];
	$f_FECreado = date("d-m-Y", strtotime($f_FECreado_t));
	$f_CargoAFactura_RID = $fila_02['CargoAFactura_RID']; //Cliente
	$f_RemitenteLocalidad = $fila_02['RemitenteLocalidad'];
	$f_Remitente = $fila_02['Remitente'];
	$f_RemitenteDomicilio = $fila_02['RemitenteDomicilio'];
	$f_RemitenteTelefono = $fila_02['RemitenteTelefono'];
	$f_RemitenteRFC = $fila_02['RemitenteRFC'];
	$f_DestinatarioLocalidad = $fila_02['DestinatarioLocalidad'];
	$f_Destinatario = $fila_02['Destinatario'];
	$f_DestinatarioDomicilio = $fila_02['DestinatarioDomicilio'];
	$f_DestinatarioTelefono = $fila_02['DestinatarioTelefono'];
	$f_DestinatarioRFC = $fila_02['DestinatarioRFC'];
	$f_RemitenteContacto = $fila_02['RemitenteContacto'];
	$f_DestinatarioContacto = $fila_02['DestinatarioContacto'];
	$f_yFlete = $fila_02['yFlete'];
	$f_yFlete_f = '$'.number_format($f_yFlete,2);
	$f_ySeguro = $fila_02['ySeguro'];
	$f_ySeguro_f = '$'.number_format($f_ySeguro,2);
	$f_yCarga = $fila_02['yCarga'];
	$f_yCarga_f = '$'.number_format($f_yCarga,2);
	$f_yDescarga = $fila_02['yDescarga'];
	$f_yDescarga_f = '$'.number_format($f_yDescarga,2);
	$f_yRecoleccion = $fila_02['yRecoleccion'];
	$f_yRecoleccion_f = '$'.number_format($f_yRecoleccion,2);
	$f_yRepartos = $fila_02['yRepartos'];
	$f_yRepartos_f = '$'.number_format($f_yRepartos,2);
	$f_yAutopistas = $fila_02['yAutopistas'];
	$f_yAutopistas_f = '$'.number_format($f_yAutopistas,2);
	$f_yDemoras = $fila_02['yDemoras'];
	$f_yDemoras_f = '$'.number_format($f_yDemoras,2);
	$f_yOtros = $fila_02['yOtros'];
	$f_yOtros_f = '$'.number_format($f_yOtros,2);
	$f_yRtaCajaPlat = $fila_02['yRtaCajaPlat'];
	$f_yRtaCajaPlat_f = '$'.number_format($f_yRtaCajaPlat,2);
	$f_yRtaTracto = $fila_02['yRtaTracto'];
	$f_yRtaTracto_f = '$'.number_format($f_yRtaTracto,2);
	$f_yFSC = $fila_02['yFSC'];
	$f_yFSC_f = '$'.number_format($f_yFSC,2);
	$f_yStopOff = $fila_02['yStopOff'];
	$f_yStopOff_f = '$'.number_format($f_yStopOff,2);
	$f_zSubtotal = $fila_02['zSubtotal'];
	$f_zSubtotal_f = '$'.number_format($f_zSubtotal,2);
	$f_zImpuesto = $fila_02['zImpuesto'];
	$f_zImpuesto_f = '$'.number_format($f_zImpuesto,2);
	$f_zRetenido = $fila_02['zRetenido'];
	$f_zRetenido_f = '$'.number_format($f_zRetenido,2);
	$f_zTotal = $fila_02['zTotal'];
	$f_zTotal_f = '$'.number_format($f_zTotal,2);
	$f_Comentarios = $fila_02['Comentarios'];
	$f_TipoCambio = $fila_02['TipoCambio'];
	$f_feCuentaPago = $fila_02['feCuentaPago'];
	$f_Unidad_RID = $fila_02['Unidad_RID'];
	$f_Operador_RID = $fila_02['Operador_RID'];
	$f_Remolque_RID = $fila_02['Remolque_RID'];
	$f_feCuentaPago = $fila_02['feCuentaPago'];
	$f_metodopago33_RID = $fila_02['metodopago33_RID'];
	$f_usocfdi33_RID = $fila_02['usocfdi33_RID'];
	$f_formapago33_RID = $fila_02['formapago33_RID'];
	
	
	$temp_total = explode(".",$f_zTotal);
	
	
	$f_total_letra = convertirNumeroLetra($temp_total[0]);
	
	if($temp_total[1] > 0){
	}
	else {
		$temp_total[1] = 0;
	}		
	
	
	
	//Buscar usocfdi
	$sql_fp="SELECT * FROM ".$prefijodb."TablaGeneral WHERE ID = ".$f_formapago33_RID;
	$res_fp=mysql_query($sql_fp);
	$fila_fp=mysql_fetch_array($res_fp);
		$fp_siglas = $fila_fp['ID2'];
		$fp_Descripcion = $fila_fp['Descripcion'];
	
	//Buscar usocfdi
	$sql_uso="SELECT * FROM ".$prefijodb."TablaGeneral WHERE ID = ".$f_usocfdi33_RID;
	$res_uso=mysql_query($sql_uso);
	$fila_uso=mysql_fetch_array($res_uso);
		$uso_siglas = $fila_uso['ID2'];
		$uso_Descripcion = $fila_uso['Descripcion'];
	
	//Buscar Metodo de Pago
	$sql_mp="SELECT * FROM ".$prefijodb."TablaGeneral WHERE ID = ".$f_metodopago33_RID;
	$res_mp=mysql_query($sql_mp);
	$fila_mp=mysql_fetch_array($res_mp);
		$mp_siglas = $fila_mp['ID2'];
		$mp_Descripcion = $fila_mp['Descripcion'];
		
		
	/*
	//Operador
	$sql_operador="SELECT * FROM ".$prefijodb."operadores WHERE ID = ".$f_Operador_RID;
	$res_operador=mysql_query($sql_operador);
	$fila_operador=mysql_fetch_array($res_operador);
		$o_Operador = $fila_operador['Operador'];
	//Unidad
	$sql_unidad="SELECT * FROM ".$prefijodb."unidades WHERE ID = ".$f_Unidad_RID;
	$res_unidad=mysql_query($sql_unidad);
	$fila_unidad=mysql_fetch_array($res_unidad);
		$u_Unidad = $fila_unidad['Unidad'];
		$u_Placas = $fila_unidad['Placas'];
	//Remolque
	$sql_remolque="SELECT * FROM ".$prefijodb."unidades WHERE ID = ".$f_Remolque_RID;
	$res_remolque=mysql_query($sql_remolque);
	$fila_remolque=mysql_fetch_array($res_remolque);
		$r_Unidad = $fila_remolque['Unidad'];
		$r_Placas = $fila_remolque['Placas'];
	
	*/

//Consulta Datos Cliente
$sql_03="SELECT * FROM ".$prefijodb."clientes WHERE ID = ".$f_CargoAFactura_RID;
$res_03=mysql_query($sql_03);
$fila_03=mysql_fetch_array($res_03);

    $c_RFC = $fila_03['RFC'];
	$c_RazonSocial = $fila_03['RazonSocial'];
	$c_Calle = $fila_03['Calle'];
	$c_NumeroInterior = $fila_03['NumeroInterior'];
	$c_NumeroExterior = $fila_03['NumeroExterior'];
	$c_Colonia = $fila_03['Colonia'];
	$c_Ciudad = $fila_03['Ciudad'];
	$c_Municipio = $fila_03['Municipio'];
	$c_Pais = $fila_03['Pais'];
	$c_Estado_RID = $fila_03['Estado_RID'];
	$c_CodigoPostal = $fila_03['CodigoPostal'];
	$c_DiasCredito = $fila_03['DiasCredito'];
	

if (isset($c_Estado_RID)) {
	
} else {
	$c_Estado_RID = 0;
}
	
//Consulta Estados
$sql_04="SELECT * FROM ".$prefijodb."estados WHERE ID = ".$c_Estado_RID;
$res_04=mysql_query($sql_04);
$fila_04=mysql_fetch_array($res_04);

    $c_Estado = $fila_04['Estado'];
	
//Consultar Clave de Servicio
		$sql_06="SELECT * FROM ".$prefijodb."TablaGeneral WHERE Tabla = 5 LIMIT 1";
		$res_06=mysql_query($sql_06);
		$fila_06=mysql_fetch_array($res_06);
			$v_clave_servicio = $fila_06['ID2'];
		
		//Consultar Clave de Producto
		$sql_07="SELECT * FROM ".$prefijodb."TablaGeneral WHERE Tabla = 4";
		$res_07=mysql_query($sql_07);
		while($fila07 = mysql_fetch_array($res_07))
        {
		  $clv_prod = $fila07['ID2'];
		  $va_tipo = $fila07['extstr1'];

		 switch ($va_tipo) {
			case 'Flete':
				$clv_prod_flete = $clv_prod;
				break;
			case 'Seguro':
				$clv_prod_seguro = $clv_prod;
				break;
			case 'Carga':
				$clv_prod_carga = $clv_prod;
				break;
			case 'Descarga':
				$clv_prod_descarga = $clv_prod;
				break;
			case 'Recoleccion':
				$clv_prod_recoleccion = $clv_prod;
				break;
			case 'Repartos':
				$clv_prod_repartos = $clv_prod;
				break;
			case 'Autopistas':
				$clv_prod_autopistas = $clv_prod;
				break;
			case 'Demoras':
				$clv_prod_demoras = $clv_prod;
				break;
			case 'Otros':
				$clv_prod_otros = $clv_prod;
				break;
			case 'Rta Caja Plataforma':
				$clv_prod_rta_caja = $clv_prod;
				break;
			case 'Rta Tracto':
				$clv_prod_rta_tracto = $clv_prod;
				break;
			case 'FSC':
				$clv_prod_fsc = $clv_prod;
				break;
			case 'Stop Off':
				$clv_prod_stop_off = $clv_prod;
				break;
			
			
			default:
				//$clv_prod_flete = "NA";
		}
		  
			
		  
		}
	
		if($f_yFlete > 0){
			$v_clave_servicio_v1 = $v_clave_servicio;
			$clv_prod1 = $clv_prod_flete;
		}else{
			$v_clave_servicio_v1 = "";
			$clv_prod1 = "";
		}
		
		if($f_ySeguro > 0){
			$v_clave_servicio_v2 = $v_clave_servicio;
			$clv_prod2 = $clv_prod_seguro;
		}else{
			$v_clave_servicio_v2 = "";
			$clv_prod2 = "";
		}
		
		if($f_yCarga > 0){
			$v_clave_servicio_v3 = $v_clave_servicio;
			$clv_prod3 = $clv_prod_carga;
		}else{
			$v_clave_servicio_v3 = "";
			$clv_prod3 = "";
		}
		
		if($f_yDescarga > 0){
			$v_clave_servicio_v4 = $v_clave_servicio;
			$clv_prod4 = $clv_prod_descarga;
		}else{
			$v_clave_servicio_v4 = "";
			$clv_prod4 = "";
		}
		
		if($f_yRecoleccion > 0){
			$v_clave_servicio_v5 = $v_clave_servicio;
			$clv_prod5 = $clv_prod_recoleccion;
		}else{
			$v_clave_servicio_v5 = "";
			$clv_prod5 = "";
		}
		
		if($f_yRepartos > 0){
			$v_clave_servicio_v6 = $v_clave_servicio;
			$clv_prod6 = $clv_prod_repartos;
		}else{
			$v_clave_servicio_v6 = "";
			$clv_prod6 = "";
		}
		
		if($f_yAutopistas > 0){
			$v_clave_servicio_v7 = $v_clave_servicio;
			$clv_prod7 = $clv_prod_autopistas;
		}else{
			$v_clave_servicio_v7 = "";
			$clv_prod7 = "";
		}
		
		if($f_yDemoras > 0){
			$v_clave_servicio_v8 = $v_clave_servicio;
			$clv_prod8 = $clv_prod_demoras;
		}else{
			$v_clave_servicio_v8 = "";
			$clv_prod8 = "";
		}
		
		if($f_yOtros > 0){
			$v_clave_servicio_v9 = $v_clave_servicio;
			$clv_prod9 = $clv_prod_otros;
		}else{
			$v_clave_servicio_v9 = "";
			$clv_prod9 = "";
		}
		
		
		



$html = '
<header class="clearfix">
	<link rel="stylesheet" type="text/css" href="style2.css">
	<style type="text/css">
		.table_s { border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px}
	</style>
    <meta charset="utf-8">
	
	<div class="container">
		<table border="0">
			<tr>
				<td style="text-align:left;" width="20%"><img src="img_logos/'.$prefijodb.'logo.png" width="120px"></td>
				<td style="text-align:left;" width="80%">
					<table border="0">
						<tr>
							<td style="text-align:left;font-size:12px;"><strong>'.$s_RazonSocial.'</strong>
								<br>RFC: '.$s_RFC.'
								<br>RÉGIMEN FISACAL: '.$s_Regimen.'
								<br>'.$s_Calle.', '.$s_NumeroExterior.', '.$s_Colonia.', '.$s_CodigoPostal.',
								<br>'.$s_Ciudad.', '.$s_Municipio.', '.$s_Estado.',
								<br>'.$s_Pais.'
								<br>Tel: '.$s_Telefono.'
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<table border="0" cellpadding="0" cellspacing="0" style="margin:0px;padding:0px;border: 1px solid;" >	
			<tr bgcolor="#D0D0D0" style="border: 1px solid;">
				<td style="text-align:left;font-size:14px;" colspan="2"><strong>CLIENTE </strong></td>
			</tr>
			<tr>
				<td style="text-align:left;font-size:11px;" ><strong>RAZON SOCIAL O DENOMINACIÓN</strong></td>
				<td style="text-align:left;font-size:11px;" ><strong>DOMICILIO FISCAL</strong></td>
			</tr>
			<tr height="15px">
				
			</tr>
			<tr>
				<td style="text-align:left;font-size:11px;" >'.$c_RazonSocial.'</td>
				<td style="text-align:left;font-size:11px;" >'.$c_Calle.', '.$c_NumeroExterior.' '.$c_NumeroInterior.'</td>
			</tr>
			<tr>
				<td style="text-align:left;font-size:11px;" >R.F.C. '.$c_RFC.'</td>
				<td style="text-align:left;font-size:11px;" >COL. '.$c_Colonia.'</td>
			</tr>
			<tr>
				<td style="text-align:left;font-size:11px;" ></td>
				<td style="text-align:left;font-size:11px;" >C.P. '.$c_CodigoPostal.', '.$c_Municipio.', '.$c_Estado.', '.$c_Pais.' </td>
			</tr>			
		</table>
		
		<!-- Busca Factura Partidas -->
		
		<table border="1" id="tabla" name="tabla" border="1" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;padding:0px;">
			<tr>
				<td style="text-align:center;font-size:8px;" width="10%"><strong>CANTIDAD</strong></td>
				<td style="text-align:center;font-size:8px;" width="10%"><strong>CLAVE UNIDAD</strong></td>
				<td style="text-align:center;font-size:8px;" width="60%"><strong>DESCRIPCION</strong></td>
				<td style="text-align:center;font-size:8px;" width="10%"><strong>VALOR UNITARIO</strong></td>
				<td style="text-align:center;font-size:8px;" width="10%"><strong>IMPORTE</strong></td>
			</tr>';
					
					$sql05 = "SELECT * FROM ".$prefijodb."facturapartidas WHERE FolioSub_RID = ".$idfactura;
					$res05 = mysql_query($sql05)or die(mysql_error());
					while($fila05 = mysql_fetch_array($res05))
					{
					  $v_cantidad = $fila05['Cantidad'];
					  $v_claveunidad33 = $fila05['claveunidad33'];
					  $v_ConceptoPartida = $fila05['ConceptoPartida'];
					  $v_PrecioUnitario_t = $fila05['PrecioUnitario'];
					  $v_PrecioUnitario = '$'.number_format($v_PrecioUnitario_t,2);
					  $v_Importe_t = $fila05['Importe'];
					  $v_Importe = '$'.number_format($v_Importe_t,2);
					  $v_prodserv33 = $fila05['prodserv33'];
					  $v_prodserv33dsc = $fila05['prodserv33dsc'];
					  $v_IVA = $fila05['IVA'];
					  $v_IVAImporte_t = $fila05['IVAImporte'];
					  $v_IVAImporte = '$'.number_format($v_IVAImporte_t,2);
					  $v_Retencion = $fila05['Retencion'];
					  $v_RetencionImporte_t = $fila05['RetencionImporte'];
					  $v_RetencionImporte = '$'.number_format($v_RetencionImporte_t,2);
					  $html.='
						<tr>
							<td style="vertical-align:text-top;text-align:center;font-size:8px;border: 1px solid;">'.$v_cantidad.'</td>
							<td style="vertical-align:text-top;text-align:center;font-size:8px;border: 1px solid;">'.$v_claveunidad33.'</td>
							<td style="text-align:left;font-size:8px;border: 1px solid;">
								<table border="0" id="subtabla" name="subtabla" width="100%" cellpadding="0" cellspacing="0" >
									<tr>
										<td style="text-align:left;font-size:8px;" colspan="3">'.$v_ConceptoPartida.'</td>
									</tr>
									<tr>
										<td style="text-align:left;font-size:8px;" colspan="3">'.$v_prodserv33.' '.$v_prodserv33dsc.'</td>
									</tr>
									<tr>
										<td style="text-align:left;font-size:8px;">002 IVA Base:'.$v_PrecioUnitario.'</td>
										<td style="text-align:left;font-size:8px;">Tasa '.$v_IVA.'%</td>
										<td style="text-align:left;font-size:8px;">Importe: '.$v_IVAImporte.'</td>
									</tr>
									<tr>
										<td style="text-align:left;font-size:8px;"></td>
										<td style="text-align:left;font-size:8px;">Tasa '.$v_Retencion.'%</td>
										<td style="text-align:left;font-size:8px;">Importe: '.$v_RetencionImporte.'</td>
									</tr>
									<tr height="50px">
										<td style="text-align:left;font-size:8px;" colspan="3"></td>
									</tr>
									<!--- Busca FacturaSub-->';
									$sql06 = "SELECT * FROM ".$prefijodb."facturassub WHERE FolioSub_RID = ".$idfactura;
									$res06 = mysql_query($sql06)or die(mysql_error());
									while($fila06 = mysql_fetch_array($res06))
									{
									  $v_cantidad = $fila06['Cantidad'];
									  $v_servicio = 'SERVICIO';
									  $v_descripcion = $fila06['Descripcion'];
									$html.='
									<tr>
										<td style="text-align:left;font-size:8px;" colspan="3">'.$v_descripcion.'</td>
									</tr>';
									}
								$html.='	
								</table>
							</td>
							<td style="vertical-align:text-top;text-align:center;font-size:8px;border: 1px solid;">'.$v_PrecioUnitario.'</td>
							<td style="vertical-align:text-top;text-align:center;font-size:8px;border: 1px solid;">'.$v_Importe.'</td>
						</tr>';
					}
					
	$html.='
			<tr>
				<td style="text-align:center;font-size:12px;" colspan="5">***('.$f_total_letra.' '.$temp_total[1].'/100 M.N.)***</td>
			</tr>
		</table>

		
		
		<table  border="0" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;padding:0px;">
			<tr>
				<td valign="top" width="15%" style="text-align:left;font-size:8px;">
					<strong>FORMA DE PAGO:
					<br>METODO DE PAGO:
					<br>USO CFDI:
					<br>TIPO DE DOCUMENTO:</strong>
				</td>
				<td valign="top" width="45%" style="text-align:left;font-size:8px;">
					'.$fp_siglas.' / '.$fp_Descripcion.'
					<br>'.$mp_siglas.' / '.$mp_Descripcion.'
					<br>'.$uso_siglas.' / '.$uso_Descripcion.'
					<br>
				</td>
				<td valign="top" width="15%" style="text-align:left;font-size:8px;"></td>
				<td valign="top" width="10%" style="text-align:left;font-size:8px;">
					<strong>SUBTOTAL:
					<br>I.V.A.:
					<br>RETENCION:
					<br>TOTAL:</strong>
				</td>
				<td valign="top" width="10%" style="text-align:left;font-size:8px;">
					'.$f_zSubtotal_f.'
					<br>'.$f_zImpuesto_f.'
					<br>'.$f_zRetenido_f.'
					<br>'.$f_zTotal_f.'
				</td>	
			</tr>
		</table>';
		

		
					
	
     
$html.='
		
    </div> <!-- FIN DIV PROJECT -->
</header>';

$html_foot = '
	<div style="vertical-align:middle;text-align:center;"><p style="text-align:center;font-size:7px;">Este documento es solo una vista previa, por lo cual no tiene validez oficial</p></div>
';



$mpdf = new mPDF('c', 'A4');
$css = file_get_contents('css/style_pdf.css');
//$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->SetHTMLFooter($html_foot); 
//$mpdf->setFooter('Este documento es solo una vista previa, por lo cual no tiene validez oficial / {DATE j-m-Y} / Tractosoft  / Página {PAGENO}');
$mpdf->Output('factura_preview.pdf', 'I');


//http://localhost/cfdipro/factura_pdf_vista_previa.php?prefijodb=prueba_&id=1737963


?>
