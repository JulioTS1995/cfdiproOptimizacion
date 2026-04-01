<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Kardex de Almacen</title>
<style type="text/css">
.style1 {
	font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
}
.style2 {
	text-align: center;
}
.style3 {
	font-weight: bold;
}
</style>

	<meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


    <meta http-equiv="X-UA-Compatible" content="ie=edge">


</head>


<?php
require_once('cnx_cfdi.php');
require_once('lib_mpdf/pdf/mpdf.php');
mysql_select_db($database_cfdi, $cnx_cfdi);

mysql_query("SET NAMES 'utf8'");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

$prefijodb = @mysql_escape_string($_GET["prefijodb"]);

$prefijobd = @mysql_escape_string($_GET["prefijodb"]);

$prefijodb = $_GET["prefijodb"];

$prefijobd = $_GET["prefijodb"];

$resSQL0 = "SELECT * FROM {$prefijobd}systemsettings";
$runSQL0 = mysql_query($resSQL0, $cnx_cfdi);
while($rowSQL0 = mysql_fetch_array($runSQL0)){

  $kitRefacciones = (int)$rowSQL0['KitRefacciones'];
}
?>

  

  <body>
    <div id="encabezadoform">
      <h1>Kardex de Almacen por Periodo</h1>
    </div>
    <center>
      <form method="post" action="kardexAlmacen_o.php" enctype="multipart/form-data">
        <fieldset>
          <div class="form-group">
            <label>Fecha Inicial:
            <input type="date" class="form-control inputdefault" name="fechai" id="fecha" required="required" autofocus>
	    Fecha Final:
            <input type="date" class="form-control inputdefault" name="fechaf" id="fecha" required="required" autofocus></label>
          </div>
<!----->
<div class="form-group">
            <label>Producto:
            <select class="form-control inputdefault" name="prod" id="prod">
				<option value='0'>Selecciona el Producto</option></label>
			<?php
      
      //require_once('cnx_cfdi.php');
      //mysql_select_db($database_cfdi, $cnx_cfdi);
			$resSQL = "SELECT ID,Codigo,Nombre as prod FROM ".$prefijobd."Productos ORDER BY Codigo";
        $runSQL = mysql_query($resSQL, $cnx_cfdi);  
			while ($rowSQL = mysql_fetch_assoc($runSQL))
    		{
				?>
				<option value='<?php echo $rowSQL['ID']; ?>'><?php echo"".$rowSQL['Codigo']." / " .$rowSQL['prod'].""; ?></option>
            <?php
			
			}
			?>

          
	    	</select>
		</div>         
<div class="form-group">
            <label>Documento:
            <select class="form-control inputdefault" name="docu" id="docu">
				<option value='0'>Selecciona el tipo de Documento</option>
			
        <option value='1'>Compras</option>
        <option value='2'>Vales de Entrada</option>
        <option value='3'>Vales de Salida</option>
        <?php if ($kitRefacciones === 1) {?>        
        <option value='4'>Mantenimiento</option>
        <?php } ?>
          
	    	</select></label>
		</div> 

          <div class="form-group">
            <input type="hidden" name="base" id="base" value='<?php echo $prefijobd; ?>'>
            <label><button type="submit" name="btnGenerar" id="btnGenerar" value="Enviar" class="btn btn-success btn-block">Generar Reporte</button></label>
            <input type="reset" value="Cancelar" name="cancelar" class="btn btn-info">
          </div>
        </fieldset>
      </form>
    </center>
  </body>
</html>

<!-- https://tractosoft-espejo71.com/cfdipro/kardexAlmacenMain_o.php?prefijodb=optimizacion_ -->