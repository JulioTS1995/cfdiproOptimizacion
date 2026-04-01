<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
set_time_limit(60000);

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener el valor del campo de entrada
        $prefijos = isset($_POST["prefijodb"]) ? $_POST["prefijodb"] : '';
        
        // Dividir los prefijos en un array
        $prefijosArray = explode(',', $prefijos);

        // Iterar sobre los prefijos
        $msg = "";
    foreach ($prefijosArray as $prefijodb) {
        $prefijodb = mysqli_real_escape_string($cnx_cfdi2, $prefijodb);

        $query = "SELECT AbonoFactura_RID, 
        (SELECT CobranzaAbonado FROM ".$prefijodb."_Factura WHERE ID = AbSub.AbonoFactura_RID) AS CobranzaAbonado
        FROM ".$prefijodb."_AbonosSub AS AbSub WHERE AbonoFactura_RID IS NOT NULL;"; 
        $runsql = mysqli_query($cnx_cfdi2, $query);
        if (!$runsql) {//debug
            $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
            $mensaje .= 'Consulta completa: ' . $query;
            die($mensaje);
        }    

        $cont = 0;
        $cont2 = 0;
        while ($rowsql = mysqli_fetch_assoc($runsql)){
            $facturaID = $rowsql['AbonoFactura_RID'];
            $cobranzaAbonado = $rowsql['CobranzaAbonado'];

            $queryFact = "SELECT DISTINCT ID, CobranzaSaldo FROM ".$prefijodb."_FacturaPartidas WHERE FolioSub_RID = $facturaID;";
            $runsqlFact = mysqli_query($cnx_cfdi2, $queryFact);
            if (!$runsqlFact) {//debug
                $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                $mensaje .= 'Consulta completa: ' . $queryFact;
                die($mensaje);
            }

            $cobranzaAbonadoTemp = $cobranzaAbonado;


            while ($rowsqlFact = mysqli_fetch_assoc($runsqlFact)){
                $cont2++;
                $partidaID = $rowsqlFact['ID'];
                $cobranzaSaldoPartida = $rowsqlFact['CobranzaSaldo'];
                if ($cobranzaAbonadoTemp > 0 AND $cobranzaSaldoPartida > $cobranzaAbonadoTemp) {
                    $updateOpc2 = "UPDATE ".$prefijodb."_FacturaPartidas SET CobranzaAbonado=$cobranzaAbonadoTemp, CobranzaSaldo=(Importe-$cobranzaAbonadoTemp) WHERE ID = $partidaID;";
                    $result_updateOpc2 = mysqli_query($cnx_cfdi2,$updateOpc2);
                    if (!$result_updateOpc2) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $updateOpc2;
                        die($mensaje);
                    }


                    $cobranzaAbonadoTemp=0;
                    $cont++;

                }elseif($cobranzaAbonadoTemp > 0 AND $cobranzaSaldoPartida <= $cobranzaAbonadoTemp){
                    
                    $updateOpc1 = "UPDATE ".$prefijodb."_FacturaPartidas SET CobranzaAbonado=Importe, CobranzaSaldo=0 WHERE ID = $partidaID;";
                    $result_updateOpc1 = mysqli_query($cnx_cfdi2,$updateOpc1);
                    if (!$result_updateOpc1) {//debug
                        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
                        $mensaje .= 'Consulta completa: ' . $updateOpc1;
                        die($mensaje);
                    }

                    $cobranzaAbonadoTemp=$cobranzaAbonadoTemp-$cobranzaSaldoPartida;
                    $cont++;
                }
                
            }
            
        }
        $msg .= "Se actualizaron $cont partidas en $prefijodb.\\n";
        echo $cont2;

    }
    if (!empty($msg)) {
        echo "<script>alert(' $msg ')</script>";
    }else{
        echo "<script>alert(' no hubo registros ')</script>";

    }
}

?>

<!-- Formulario HTML -->
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="prefijodb">Prefijos de base de datos (separados por coma):</label>
    <input type="text" name="prefijodb" id="prefijodb" required>
    <input type="submit" value="Ejecutar">
</form>