<?php

require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

//$prefijobd="prueba_";

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
$pos = strpos($prefijobd, "_");

if ($pos === false) {
    $prefijobd = $prefijobd . "_";
} 


?>
<html>
  <head>
    <title>Remisiones Por Periodo</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
  </head>

  <body>
    <div id="encabezadoform">
      <h1>Abonos detallados por periodo</h1>
    </div>
    <center>
      <form method="post" action="abonos_detalle2.php" enctype="multipart/form-data">
        <fieldset>
          <div  class="form-group">
            <label>Fecha Inicial:</label>
            <input type="date" class="form-control inputdefault" name="fechai" id="fecha" required="required" autofocus>
			<label>    Fecha Final:</label>
            <input type="date" class="form-control inputdefault" name="fechaf" id="fecha" required="required" autofocus>
          </div>
		  <div  class="form-group">
			<label>Cliente:</label>
			<select class="form-control" name="cliente" id="cliente">
				<option value="0">- Seleccione -</option>
				<?php 
					//Buscar Clientes 
					$sql1 = "SELECT * FROM ".$prefijobd."clientes ORDER BY RazonSocial";
					$res1 = mysql_query($sql1, $cnx_cfdi);
					while($row1 = mysql_fetch_array($res1)){
						$id_cliente = $row1['ID'];
						$nom_cliente = $row1['RazonSocial'];
				?>
					<option value="<?php echo $id_cliente; ?>"><?php echo $nom_cliente; ?></option>
				<?php
					}
				?>
			</select>
		  </div>
		  <div  class="form-group">
			<label>Atiende:</label>
			<select class="form-control" name="atiende" id="atiende">
				<option value="0">- Seleccione -</option>
				<?php 
					//Buscar - Atiende
					$sql2 = "SELECT * FROM ".$prefijobd."usuarios ORDER BY Nombre";
					$res2 = mysql_query($sql2, $cnx_cfdi);
					while($row2 = mysql_fetch_array($res2)){
						$atiende_id = $row2['ID'];
						$atiende_nombre = $row2['Nombre'];
				?>
					<option value="<?php echo $atiende_id; ?>"><?php echo $atiende_nombre; ?></option>
				<?php
					}
				?>
			</select>
		  </div>

          
		  
		  
		
		

          <div class="form-group">
            <input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
            <input type="submit" value="Consultar" name="consultar" class="btn btn-info">
            <input type="reset" value="Cancelar" name="cancelar" class="btn btn-info">
          </div>
        </fieldset>
      </form>
    </center>
  </body>
</html>