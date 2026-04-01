<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kardex de Productos</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
    #resultadoBusqueda {
        border: 1px solid #ccc;
        max-height: 150px;
        overflow-y: auto;
        position: absolute;
        z-index: 1000;
        width: 100%;
        background-color: white;
    }
    #resultadoBusqueda div {
        padding: 8px;
        cursor: pointer;
    }
    #resultadoBusqueda div:hover {
        background-color: #f0f0f0;
    }
    .autocomplete-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px; 
    }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

</head>
<body>
<?php
require_once('cnx_cfdi.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query("SET NAMES 'utf8'");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}
$prefijodb = $_GET["prefijodb"];
?>

<div class="container">
    <h2><b>Kardex de Productos</b></h2>

    <form action="reporte_productos_o.php" method="get" autocomplete="off">
        <div class="form-group autocomplete-wrapper">
            <label>Buscar producto:</label>
            <input type="text" id="buscar_1" class="form-control" placeholder="Escribe código o nombre y scrollea para buscar">
            <div id="resultadoBusqueda"></div>
            <input type="hidden" id="producto" name="producto">
            <input type="hidden" name="prefijodb" value="<?php echo $prefijodb; ?>">
        </div>

       <button type="submit" name="btnGenerar" id="btnGenerar" value="Enviar" class="btn btn-success btn-block">Generar Reporte</button>
	<button type="submit" name="btnGenerar" id="btnGenerar" value="Calcular" class="btn btn-primary btn-block">Regenerar Existencias</button>
    </form>
</div>

<script>
$(document).ready(function () {
    $('#buscar_1').on('keyup', function () {
        var query = $(this).val();
        var prefijodb = "<?php echo $prefijodb; ?>";

        if (query.length > 1) {
            $.ajax({
                url: "buscar_productos.php",
                method: "GET",
                data: { q: query, prefijodb: prefijodb },
                success: function (data) {
                    $('#resultadoBusqueda').fadeIn().html(data);
                }
            });
        } else {
            $('#resultadoBusqueda').fadeOut();
        }
    });

    
    $(document).on('click', '.sugerencia', function () {
        var id = $(this).data('id');
        var texto = $(this).text();

        $('#buscar_1').val(texto);
        $('#producto').val(id);
        $('#resultadoBusqueda').fadeOut();
    });

   
    $(document).click(function(e) {
        if (!$(e.target).closest('#buscar_1, #resultadoBusqueda').length) {
            $('#resultadoBusqueda').fadeOut();
        }
    });
});
</script>

</body>
</html>