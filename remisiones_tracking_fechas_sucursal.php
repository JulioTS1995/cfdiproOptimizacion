<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
$sucursal = $_GET["sucursal"];//trae sucursal

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


?>
<html>
  <head>
    <title>Viajes Por Periodo</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
  </head>

  <body>
    <div id="encabezadoform">
      <h1>Viajes por Periodo - Tracking</h1>
    </div>
    <center>
      <form method="post" action="remisiones_tracking_sucursal.php" enctype="multipart/form-data">
        <fieldset>
          <div  class="form-group">
            <label>Fecha Inicial:</label>
            <input type="date" class="form-control inputdefault" name="fechai" id="fecha" required="required" autofocus>
	    <label>    Fecha Final:</label>
            <input type="date" class="form-control inputdefault" name="fechaf" id="fecha" required="required" autofocus>
          </div>

          
		  
		  
		
		

          <div class="form-group">
            <input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
            <input type="hidden" name="sucursal" id="sucursal" value='<?php echo $sucursal; ?>'>
            <input type="submit" value="Consultar" name="consultar" class="btn btn-info">
            <input type="reset" value="Cancelar" name="cancelar" class="btn btn-info">
          </div>
        </fieldset>
      </form>
    </center>
  </body>
</html>