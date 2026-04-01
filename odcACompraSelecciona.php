<?php

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
$prefijo = $_GET["prefijo"];
$idCompra = $_GET['id'];
$idProveedor = $_GET['idProveedor'];

// Obtener los correos
$sql = "SELECT odcs.ID AS IDODCSub,
 odc.XFolio,
 p.Codigo,
 p.Nombre,
 odcs.Cantidad,
 odcs.CantidadPendiente,
 odcs.CantidadSurtida,
 odcs.Importe,
 odcs.ImporteIVA,
 odcs.ImporteRetencion,
 odcs.ImporteTotal FROM ".$prefijo."OrdenComprasSub odcs 
 LEFT JOIN ".$prefijo."OrdenCompra odc ON odcs.FolioSub_RID = odc.ID
 LEFT JOIN ".$prefijo."Productos p ON odcs.ProductoA_RID = p.ID 
 WHERE odcs.FolioSub_RID IN (SELECT ID FROM ".$prefijo."ordencompra WHERE ProveedorOC_RID = ".$idProveedor.") AND odcs.CantidadPendiente > 0";
//SELECT * FROM prbtpsmalfavon_ordencomprassub WHERE FolioSub_RID IN (SELECT ID FROM prbtpsmalfavon_ordencompra WHERE ProveedorOC_RID = 2808822) AND Compra IS NULL
$result = mysqli_query($cnx_cfdi2, $sql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="Refresh" CONTENT="300">
<title>Pasa ODCs a Compra</title>

 <!-- Bootstrap links -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
 <!-- FIN Bootstrap links -->
 <!-- datatable -->
	<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
 <!-- datatable 
  
 XFolio, Producto Codigo, Producto Nombre, Cantidad, Importe, Importe IVA, Importe Retencion, Importe Total
 
 -->

</head>

<body>
<form method="post" action="odcACompraInserta.php">
 
        <div id = "container1" style = "width: 80%; height: 60%; margin: 0 auto; text-align:center;" >
            <div id="contenedor2">
                <div id="2" style="float: left; width: 100%; text-align:left;">
                    <h1 class="font-weight-bold" style="text-align: left;color:#0059b3; line-height: 100px;"><strong>Selecciona los Productos</h1>
                </div>
            </div>

        <hr>
        

    <table class="table table-hover table-responsive table-condensed" id="table">
        <thead>
            <tr>
                <th></th>
                <th>XFolio</th>
                <th>Producto Codigo</th>
                <th>Producto Nombre</th>
                <th>Cantidad</th>
                <th>Cantidad Pendiente</th>
                <th>Cantidad Surtida</th>
                <th>Importe</th>
                <th>Importe IVA</th>
                <th>Importe Retencion</th>
                <th>Importe Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="odcsSeleccionados[]" value="<?php echo $row['IDODCSub']; ?>">
                    </td>
                    <td align = "left"><?php echo $row['XFolio']; ?></td>
                    <td align = "left"><?php echo $row['Codigo']; ?></td>
                    <td align = "left"><?php echo $row['Nombre']; ?></td>
                    <td align = "left"><?php echo $row['Cantidad']; ?></td>
                    <td align = "left"><?php echo $row['CantidadPendiente']; ?></td>
                    <td align = "left"><?php echo $row['CantidadSurtida']; ?></td>
                    <td align = "left"><?php echo ("$".number_format($row['Importe'],2)); ?></td>
                    <td align = "left"><?php echo ("$".number_format($row['ImporteIVA'],2)); ?></td>
                    <td align = "left"><?php echo ("$".number_format($row['ImporteRetencion'],2)); ?></td>
                    <td align = "left"><?php echo ("$".number_format($row['ImporteTotal'],2)); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <button type="submit">Agregar</button>
    <input type="hidden" name="prefijo" id="prefijo" value='<?php echo $prefijo; ?>'>
    <input type="hidden" name="id" id="id" value='<?php echo $idCompra; ?>'>


<script>
	$(document).ready(function() {
	$('#table').DataTable();
	});
</script>
</div>
</form>
</body>