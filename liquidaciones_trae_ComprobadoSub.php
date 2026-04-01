<?php
$prefijobd = $_GET["base"];
$xfolioLiq = $_GET["xfolio"];

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

$resSQLDel="DELETE FROM ".$prefijobd."LiquidacionesComprobadoSub WHERE FolioSub_RID = (SELECT ID FROM ".$prefijobd."Liquidaciones WHERE XFolio = '".$xfolioLiq."');";
$runSQLDel=mysqli_query($cnx_cfdi2,$resSQLDel);

$resSQL="SELECT 
GS.Cantidad,
GS.ConceptoLiq_RID,
R.XFolio AS FolioRemision,
LS.ID AS LiqSubID
FROM
".$prefijobd."GastosViajes AS G,
".$prefijobd."Remisiones AS R,
".$prefijobd."GastosViajesSub AS GS,
".$prefijobd."GastosViajes_REF AS GVR,
".$prefijobd."LiquidacionesSub AS LS
WHERE
G.Liquidacion = '".$xfolioLiq."'
    AND G.TipoVale='Deposito'
    AND GVR.ID = G.ID
    AND GVR.RID = GS.ID
    AND R.ID = G.Remision_RID
    AND LS.RemisionLiq_RID = R.ID;";
	//die($resSQL);
//echo $resSQL;
$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
$rows = mysqli_num_rows($runSQL);
if($rows==0){
    die('No existen Viajes anexados o Gastos anexados a esos Viajes');
}

$cont=0;

while ($rowSQL=mysqli_fetch_array($runSQL)){
    $cont++;
    $cantidad = $rowSQL['Cantidad'];
    $conceptoID = $rowSQL['ConceptoLiq_RID'];
    $xfolioRemision = $rowSQL['FolioRemision'];
    $liqSubID = $rowSQL['LiqSubID'];

    //Crear Nuevo ID
    $begintrans = mysqli_query( $cnx_cfdi2,"BEGIN");
    //Obtengo el siguiente BASIDGEN
    $qry_basidgen = "SELECT MAX_ID from bas_idgen";
    $result_qry_basidgen = mysqli_query( $cnx_cfdi2,$qry_basidgen);
    if (!$result_qry_basidgen){
        //No pude obtener el siguiente basidgen
        $endtrans = mysqli_query( $cnx_cfdi2,"ROLLBACK");
        echo "Error4";
    }
    else {			
        //Le sumo uno y hago el update
        $rowbasidgen = mysqli_fetch_row($result_qry_basidgen);          
        $basidgen = $rowbasidgen[0]+1;      
        //echo "<br>Basidgen" . $basidgen . "<br>"          
        $upd_basidgen = "UPDATE bas_idgen SET MAX_ID=" . $basidgen;
        $result_upd_basidgen = mysqli_query( $cnx_cfdi2,$upd_basidgen);
                        
        if ($result_upd_basidgen) {
            //Se hizo el update sin problemas
            $endtrans = mysqli_query( $cnx_cfdi2,"COMMIT");
        }
    }
    $newID = $basidgen;

    $resSQLInsert="INSERT INTO ".$prefijobd."LiquidacionesComprobadoSub
    (ID, FolioSub_REN, FolioSub_RID, Deposito, Concepto_REN,
     Concepto_RID, Autorizado) VALUES 
     ('".$newID."','Liquidaciones',(SELECT ID FROM ".$prefijobd."Liquidaciones WHERE XFolio = '".$xfolioLiq."'),
     '".$cantidad."','ConceptosLiquidaciones','".$conceptoID."','0')";
	 //die($resSQLInsert);
     //echo $resSQLInsert."<br>";

    $resSQLInsert=utf8_encode($resSQLInsert);
    $runSQLInsert=mysqli_query($cnx_cfdi2,$resSQLInsert);


     if($cont==$rows){
        echo('Gastos anexados correctamente');
     }
}

?>