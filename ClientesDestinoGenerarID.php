<?php

$id_clientedestino = $_GET['id'];
$prefijodb = $_GET['prefijodb'];

$time = time();
$fecha = date("Y-m-d H:i:s", $time);

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
//$begintrans = mysql_query("BEGIN", $cnx_cfdi);


/*
 * zero_fill
 *
 * Rellena con ceros a la izquierda
 *
 * @param $valor valor a rellenar
 * @param $long longitud total del valor
 * @return valor rellenado
 */

function zero_fill ($valor, $long = 0)
{
    return str_pad($valor, $long, '0', STR_PAD_LEFT);
}


//Generar ID Cliente Destino
$resSQL1 = "SELECT MAX(Folio) as folio FROM " . $prefijodb . "clientesdestinos";
$runSQL1 = mysql_query($resSQL1, $cnx_cfdi);
while($rowSQL1 = mysql_fetch_array($runSQL1)){
	$v_folio = $rowSQL1['folio'];
}

$v_folio = $v_folio + 1;

$new_id = zero_fill($v_folio, 6);

				
$sql = "UPDATE " . $prefijodb . "clientesdestinos SET folio=".$v_folio.", IdClienteDestino='".$new_id."' WHERE ID=".$id_clientedestino;


/*echo "<br>";
echo $sql;	
echo "<br>";
echo "<br>";*/
				
mysql_query($sql,$cnx_cfdi);

echo "<br>";		
echo "<h2>Se asigno IdClienteDestino = ".$new_id." con &Eacute;xito</h2>";				


?>