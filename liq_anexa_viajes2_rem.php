<?php
    set_time_limit(350);

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}
	
	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Liquidacion");
	}
	
	$id_liquidacion = $_GET["id"];
	$id_oper = $_GET["operador"];
	
	 
	
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
	
	
	//Buscar Liquidacion
	$sql03="SELECT * FROM " . $prefijobd . "liquidaciones WHERE ID = ".$id_liquidacion;
	$res_sql03=mysql_query($sql03);
								
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
        $liq_xfolio = $fila_sql03['XFolio'];
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

    <title>Agregar Viajes a Liquidaciones</title>

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
		<h1 class="titulo_1 col-12">Listado de Viajes<small class="text-muted"> para agregar en Liquidación: <?php echo $liq_xfolio;  ?></small></h1>
	</div>
	<div style="margin: 0;left: 2%;">
        <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
    </div>
	<br>


        <div class="container">

           
 <?php  

function quitar_tildes($cadena) {

$cade = utf8_decode($cadena);
$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
$texto = str_replace($no_permitidas, $permitidas ,$cade);
return $texto;
}
//$text_xfolio = quitar_tildes($text_xfolio);
					
?>
            
            <div class="row">
                <div class="col-lg-12" style="height:500px; overflow:scroll;">
                    <table class="table table-hover table-responsive table-condensed" id="table">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle;text-align:center;">XFolio</th>
                                <th style="vertical-align: middle;text-align:center;">Fecha</th>
                                <th style="vertical-align: middle;text-align:center;">Operador</th>
								<th style="vertical-align: middle;text-align:center;">Unidad</th>
                                <th style="vertical-align: middle;text-align:center;">Total</th>
								<th style="vertical-align: middle;text-align:center;">Agregar a Liquidación</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
								$sql01="SELECT * FROM " . $prefijobd . "viajes2 WHERE (Liquidacion IS NULL OR Liquidacion='') AND Finalizado>'1995-01-01' AND Operador_RID =".$id_oper." ORDER BY XFolio";
								//echo $sql01;
								$res_sql01=mysql_query($sql01);
								
								//echo $sql01;
								
								while ($fila_sql01 = mysql_fetch_array($res_sql01)){
                                 $id_b = $fila_sql01['ID'];
								 $xfolio_b = $fila_sql01['XFolio'];
								 $fecha_b_t = $fila_sql01['Creado'];
								 $id_operador_b = $fila_sql01['Operador_RID'];
								 $id_unidad_b = $fila_sql01['Unidad_RID'];
								 $total_b_t = $fila_sql01['Total'];
								 
                                 
								 $fecha_b = date("d-m-Y", strtotime($fecha_b_t));
								 $total_b = "$".number_format($total_b_t,2);
                                
								 
								 //Buscar Operador
								 $sql02="SELECT * FROM " . $prefijobd . "operadores WHERE ID = ".$id_operador_b;
								 $res_sql02=mysql_query($sql02);
								 while ($fila_sql02 = mysql_fetch_array($res_sql02)){
                                  $nom_operador = $fila_sql02['Operador'];
								}
								
								//Buscar Unidad
								 $sql03="SELECT * FROM " . $prefijobd . "unidades WHERE ID = ".$id_unidad_b;
								 $res_sql03=mysql_query($sql03);
								 while ($fila_sql03 = mysql_fetch_array($res_sql03)){
                                  $nom_unidad = $fila_sql03['Unidad'];
								}
								 						 

                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo $xfolio_b; ?></td>
                                <td style="text-align:center;"><?php echo $fecha_b; ?></td>
                                <td><?php echo $nom_operador; ?></td>
								<td><?php echo $nom_unidad; ?></td>
                                <td><?php echo $total_b; ?></td>
								<td style="text-align:center;">
                                    <a href="liq_anexa_viajes2_rem_proceso.php?id_viaje=<?php echo $id_b;?>&prefijobd=<?php echo $prefijobd;?>&id_liq=<?php echo $id_liquidacion;?>">
                                        <button type="button" class="btn btn-default">
                                            <span class="glyphicon glyphicon-plus"></span> Anexar
                                        </button>
                                    </a>
                                </td>
                               
                            </tr>
                            <?php
                                }
								
								$num_c_t  = mysql_num_rows(mysql_query("$sql01"));	
								$num_c = number_format($num_c_t);
                            ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6" align="right">
                    <p>
                        <h4>Total de Registros: <strong><?php echo $num_c; ?></strong></h4>
                    </p>
                </div>
            </div>
            

        </div>



<script src="//rawgithub.com/stidges/jquery-searchable/master/dist/jquery.searchable-1.0.0.min.js"></script>



</div>

   
</body>
</html>

<!-- http://107.161.78.100/cfdipro/liq_anexa_viajes2_rem.php?id=234947&prefijodb=prbtpsmarti_&operador=2816699 -->
