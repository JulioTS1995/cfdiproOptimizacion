<?php
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);

// Definir un array para almacenar los resultados válidos
$resultados_validos = array();

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tabla = $_POST["tabla"];
    $palabraBuscar = $_POST["palabraBuscar"];
    $campo = $_POST["campo"];

    // Consulta para obtener los prefijos de las tablas ingresadas por el usuario
    $sql = "SELECT SUBSTRING_INDEX(table_name, '_', 1) AS prefijo
            FROM information_schema.tables
            WHERE table_schema = 'basdb'
                AND table_name LIKE '%$tabla%'";

    $result = mysqli_query($cnx_cfdi2, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // Iterar sobre los resultados e imprimir los prefijos
        while ($row = mysqli_fetch_assoc($result)) {
            $prefijo = $row["prefijo"];

            // Verificar si la tabla cumple con la condición de tener la palabra ingresada en RazonSocial
            $sql_condicion = "SELECT 1 
                              FROM basdb.$prefijo" . "_$tabla  
                              WHERE $campo LIKE '%$palabraBuscar%' 
                              LIMIT 1";

            $result_condicion = mysqli_query($cnx_cfdi2, $sql_condicion);

            if ($result_condicion && mysqli_num_rows($result_condicion) > 0) {
                // Almacenar los resultados válidos en el array
                $resultados_validos[] = $prefijo;
            }
        }
    } else {
        echo "No se encontraron resultados";
    }
}

// Cerrar la conexión
mysqli_close($cnx_cfdi2);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Prefijos</title>
</head>
<body>
    <h2>Búsqueda de Prefijos</h2>

    <form method="post" action="">
        <label for="tabla">Nombre de la tabla:</label>
        <input type="text" id="tabla" name="tabla" placeholder="Nombre de la tabla" required>

        <label for="campo">Nombre del campo:</label>
        <input type="text" id="campo" name="campo" placeholder="Nombre del campo" required>
        
        <label for="palabraBuscar">Palabra a buscar:</label>
        <input type="text" id="palabraBuscar" name="palabraBuscar" required>
        
        <button type="submit">Buscar</button>
    </form>

    <!-- Mostrar resultados en una tabla -->
    <?php if (!empty($resultados_validos)): ?>
        <h3>Resultados Válidos</h3>
        <table border="1">
            <tr>
                <th>Prefijo</th>
            </tr>
            <?php foreach (array_unique($resultados_validos) as $prefijo): ?>
                <tr>
                    <td><?php echo $prefijo; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Puedes agregar más contenido HTML según tus necesidades -->
</body>
</html>
