<?php

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
  die("Falta el prefijo de la BD");
}
//$prefijobd = 'prueba_';
//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
require_once('cnx_cfdi.php');
mysql_select_db($database_cfdi, $cnx_cfdi);
$sucursal = $_GET["sucursal"];//trae sucursal

//Reviso si existe el guion bajo en el prefijo y si no se lo agrego
/*$pos = strpos($prefijobd, "_");
_cofen
if ($pos === false) {
  $prefijobd = $prefijobd . "_";
} */
?>

<html>
  <head>
    <title>Tarifas de Clientes</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' charset='UTF-8'>
  </head>

  <body>
    <div id="encabezadoform">
      <h1>Tarifas de Clientes</h1>
    </div>
    <center>
      <form method="post" action="tarifasClientes.php" enctype="multipart/form-data" id = "tarifasForm">
        <fieldset>


        <div class="form-group">
            <label>Cliente:</label>
            <select class="form-control inputdefault" name="Cliente" id="Cliente" required>
				<option value='0'>Selecciona Cliente</option>
			<?php
      
      //require_once('cnx_cfdi.php');
      //mysql_select_db($database_cfdi, $cnx_cfdi);
			$resSQL = "SELECT ID,RazonSocial as Cliente FROM ".$prefijobd."clientes WHERE Sucursal_RID = ".$sucursal." ORDER BY Cliente";
        $runSQL = mysql_query($resSQL, $cnx_cfdi);  
			while ($rowSQL = mysql_fetch_assoc($runSQL))
    		{
				?>
				<option value='<?php echo $rowSQL['ID']; ?>'><?php echo $rowSQL['Cliente']; ?></option>
            <?php
			
			}
			?>

          
	    	</select>
		</div>
		
		<div class="form-group">
            <label>Ruta:</label>
            <select class="form-control inputdefault" name="Ruta" id="Ruta">
				
				<option value='0'>Selecciona Ruta</option>
	    <?php
		
		//require_once('cnx_cfdi.php');
    		//mysql_select_db($database_cfdi, $cnx_cfdi);

		$resSQL2 = "SELECT ID,Ruta FROM ".$prefijobd."rutas WHERE Sucursal_RID = ".$sucursal." ORDER BY Ruta";
    		$runSQL2 = mysql_query($resSQL2, $cnx_cfdi);
		while ($rowSQL2 = mysql_fetch_assoc($runSQL2))
    		{
	    ?>
	    <option value='<?php echo $rowSQL2['ID']; ?>'><?php echo $rowSQL2['Ruta']; ?></option>
            <?php
		}
	    ?>
			</select>
		</div>
		  

			
			
		<div class="form-group">
            <label>Tipo de unidad:</label>
            <select class="form-control inputdefault" name="Clase" id="Clase">
				<option value='0'>Selecciona tipo de Unidad</option>
			<?php
			$resSQL3 = "SELECT ID,Clase FROM ".$prefijobd."unidadesclase ";
    		$runSQL3 = mysql_query($resSQL3, $cnx_cfdi);
			while ($rowSQL3 = mysql_fetch_assoc($runSQL3))
    		{
			?>
			<option value='<?php echo $rowSQL3['ID']; ?>'><?php echo $rowSQL3['Clase']; ?></option>
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
	  <script>
        document.getElementById('tarifasForm').addEventListener('submit', function(event) {
            const selects = document.querySelectorAll('select[required]');
            let valid = true;
            selects.forEach(function(select) {
                if (select.value === '0') {
                    alert('Por favor, selecciona una opción válida para ' + select.previousElementSibling.innerText);
                    valid = false;
                    event.preventDefault();
                }
            });
            return valid;
        });
    </script>
    </center>
  </body>
</html>