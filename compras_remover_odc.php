<?php
   
	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}
	
	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Compra");
	}
	
	$id_compra = $_GET["id"];
	
	 
	
	//$id_compra = 1673644;
	
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
	
	//$prefijobd = "solosa_";
	
	//Buscar Compra
	$sql03="SELECT * FROM " . $prefijobd . "compras WHERE ID = ".$id_compra;
	$res_sql03=mysql_query($sql03);
								
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
        $compra_xfolio = $fila_sql03['XFolio'];
	}
	
	
	


?>
<!DOCTYPE html>
<html lang="en">
<head>

<!-- Latest compiled and minified CSS Estilos MENU Header -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>

  <link rel="stylesheet" href="css/style_menu.css" type="text/css"/>
  <link rel="stylesheet" href="css/estilo_forms.css" type="text/css"/>

  <link rel="stylesheet" href="css/table_search.css" type="text/css"/>
  <script src="js/table_search.js"></script>
 

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Agregar Ordenes de Compra</title>

    <link rel="shortcut icon" href="imagenes/logo_ts.ico">


    <script type="text/javascript">
			
        ///////////////////// Solo numero en un campo
            function justNumbers(e)
            {
            var keynum = window.event ? window.event.keyCode : e.which;
            if ((keynum == 8) || (keynum == 46))
                        return true;
                return /\d/.test(String.fromCharCode(keynum));
            }

            //Para llamar en el input
            //onkeypress="return justNumbers(event);"
            //Para limitar numero de caracteres
            //maxlength="5"
        /////////////////////FIN  Solo numero en un campo

		</script>




    <!--<script src="js/arriba.js"></script>-->


</head>
<body >

<!-- <span class="ir-arriba icon-arrow-up"></span> -->


<div class="container" style="margin-top: 0;">
	<div style="margin-top: 20px;left: 30%; position:fixed;">
		<h1 class="titulo_1 col-12">Listado de Ordenes de Compra<small class="text-muted"> adjuntas en Compra: <?php echo $compra_xfolio;  ?></small></h1>
	</div>
	<div style="margin: 0;left: 2%;">
        <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
    </div>
	<br>


        <div class="container">

            <div class="row">
                <!--<div class="form-group col-lg-10 " align="center">
                    <input type="search" id="search" value="" class="form-control " placeholder="Buscar Registro">
                </div> -->
                <form name="form_buscar" method="post" action="#" id="form_buscar">
                    <div class="form-group col-lg-4 " align="left">
                        <label>XFolio</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="glyphicon glyphicon-search"></i>
                            </span>
                            <input style="text-align: left;" id="txt_xfolio" type="text" name="txt_xfolio" placeholder="Buscar XFolio" class="form-control "/>
                        </div>   
                    </div>
                    <div class="form-group col-lg-3 " align="left">
                        <label>Fecha:</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="glyphicon glyphicon-search"></i>
                            </span>
							<input type="date" id="txt_fecha" name="txt_fecha" class="form-control ">
                        </div>
                        
                    </div>
                    <div class="form-group col-lg-1 " align="center">
                        <label> </label>
                        <input type="submit" name="btn_buscar" class="btn btn-default" id="btn_buscar" value="Buscar">
                    </div>
                    
                </form>
                <?php  

                    function quitar_tildes($cadena) {

                    $cade = utf8_decode($cadena);
$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
$texto = str_replace($no_permitidas, $permitidas ,$cade);
return $texto;
}
					
					if (!empty($_POST['txt_xfolio'])) {
                    //if (isset($_POST['txt_xfolio'])) {
                        $text_xfolio = $_POST['txt_xfolio'];
						$cond_xfolio = "AND XFolio = '".$text_xfolio."'";
                    } else {
                         $text_xfolio = "";
						 $cond_xfolio = "";
                    }

                    if (!empty($_POST['txt_fecha'])) {
                        $text_fecha = $_POST['txt_fecha'];
						$cond_fecha = "AND Fecha = '".$text_fecha."'";
                    } else {
                         $text_fecha = "";
						 $cond_fecha = "";
                    }
				
                    $condicion1 = "Compra = '".$compra_xfolio."'";
					
                   

                    $text_xfolio = quitar_tildes($text_xfolio);
                    



                ?>
            </div>
            <div class="row">
                <div class="col-lg-12" style="height:500px; overflow:scroll;">
                    <table class="table table-hover table-responsive table-condensed" id="table">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle;text-align:center;">XFolio</th>
                                <th style="vertical-align: middle;text-align:center;">Fecha</th>
                                <th style="vertical-align: middle;text-align:center;">Proveedor</th>
                                <th style="vertical-align: middle;text-align:center;">Total</th>
                                <th style="vertical-align: middle;text-align:center;">Moneda</th>
								<th style="vertical-align: middle;text-align:center;">Remover ODC</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
								$sql01="SELECT * FROM " . $prefijobd . "ordencompra WHERE ".$condicion1." ".$cond_xfolio." ".$cond_fecha;
								$res_sql01=mysql_query($sql01);
								
								//echo $sql01;
								
								while ($fila_sql01 = mysql_fetch_array($res_sql01)){
                                 $id_odc = $fila_sql01['ID'];
								 $moneda_odc = $fila_sql01['Moneda'];
								 $xfolio_odc = $fila_sql01['XFolio'];
								 $fecha_odc_t = $fila_sql01['Fecha'];
								 $proveedor_id_odc = $fila_sql01['ProveedorOC_RID'];
								 $total_odc_t = $fila_sql01['Total'];
                                 
								 $fecha_odc = date("d-m-Y", strtotime($fecha_odc_t));
								 $total_odc = "$".number_format($total_odc_t,2);
                                
								 
								 //Buscar Proveedor
								 $sql02="SELECT * FROM " . $prefijobd . "proveedores WHERE ID = ".$proveedor_id_odc;
								 $res_sql02=mysql_query($sql02);
								
								 while ($fila_sql02 = mysql_fetch_array($res_sql02)){
                                  $nom_proveedor = $fila_sql02['RazonSocial'];
								}
								 						 

                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $xfolio_odc; ?></td>
                                <td style="text-align:center;"><?php echo $fecha_odc; ?></td>
                                <td><?php echo $nom_proveedor; ?></td>
                                <td><?php echo $total_odc; ?></td>
                                <td style="text-align:center;"><?php echo $moneda_odc; ?></td>
								<td style="text-align:center;">
                                    <a href="compras_remueve_odc_proceso.php?id_odc=<?php echo $id_odc;?>&prefijobd=<?php echo $prefijobd;?>&id_compra=<?php echo $id_compra;?>">
                                        <button type="button" class="btn btn-default">
                                            <span class="glyphicon glyphicon-trash"></span> Remover
                                        </button>
                                    </a>
                                </td>
                               
                            </tr>
                            <?php
                                }
								
								$num_odc_t  = mysql_num_rows(mysql_query("$sql01"));	
								$num_odc = number_format($num_odc_t);
                            ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6" align="right">
                    <p>
                        <h4>Total de Registros: <strong><?php echo $num_odc; ?></strong></h4>
                    </p>
                </div>
            </div>
            

        </div>



<script src="//rawgithub.com/stidges/jquery-searchable/master/dist/jquery.searchable-1.0.0.min.js"></script>



</div>

   
</body>
</html>
