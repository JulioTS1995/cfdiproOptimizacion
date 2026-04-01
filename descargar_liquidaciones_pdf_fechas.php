<?php
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}

$prefijodb = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Descargar Liquidaciones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Apple-Inspired Design -->
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "San Francisco", "Helvetica Neue", Helvetica, Arial, sans-serif;
      background-color: #f5f5f7;
      margin: 0;
      padding: 0;
      color: #1d1d1f;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      padding: 40px;
    }
    h2 {
      text-align: center;
      font-weight: 600;
      font-size: 24px;
      margin-bottom: 30px;
      color: #000;
    }
    label {
      font-size: 14px;
      font-weight: 500;
      color: #333;
      margin-bottom: 6px;
      display: block;
    }
    input, select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #d2d2d7;
      font-size: 15px;
      margin-bottom: 20px;
      transition: border 0.2s ease-in-out;
    }
    input:focus, select:focus {
      border-color: #007aff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0,122,255,0.2);
    }
    button {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      background: linear-gradient(180deg, #007aff, #0051a8);
      color: #fff;
      transition: background 0.3s ease;
    }
    button:hover {
      background: linear-gradient(180deg, #005ecb, #004494);
    }
    .footer-note {
      text-align: center;
      font-size: 13px;
      color: #6e6e73;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <form action="descargar_liquidaciones_pdf.php" method="post">
      <h2> Descargar Liquidaciones PDF <svg xmlns="http://www.w3.org/2000/svg" 
       width="45" height="45" viewBox="0 0 24 24" 
       fill="none" style="vertical-align: middle; margin-right: 10px;">
    <path fill="rgba(219, 40, 30, 0.88)" d="M6 2a2 2 0 0 0-2 2v16c0 
        1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6H6z"/>
    <text x="6" y="17" fill="white" font-size="5" 
          font-family="Arial, sans-serif" >PDF</text>
  </svg></h2>

      <label for="fechai">Fecha Inicio:</label>
      <input style= "width:95%;"type="date" name="fechai" id="fechai" required>

      <label for="fechaf">Fecha Fin:</label>
      <input  style= "width:95%;" type="date" name="fechaf" id="fechaf" required>
      
      <input type="hidden" name="prefijodb" value="<?php echo $prefijodb; ?>">

      <label for="operador">Operador:</label>
      <select name="operador" id="operador">
        <option value="0">- Seleccione -</option>
        <?php 
          $sql2 = "SELECT ID, Operador FROM ".$prefijodb."operadores ";
          $res2 = mysqli_query($cnx_cfdi2, $sql2);
          while($row2 = mysqli_fetch_array($res2)){
              $id_operador = $row2['ID'];
              $nom_operador = $row2['Operador'];
              echo "<option value='$id_operador'>$nom_operador</option>";
          }
        ?>
      </select>
      <label for="unidad">Unidad:</label>
      <select name="unidad" id="unidad">
        <option value="0">- Seleccione -</option>
        <?php 
          $sql2 = "SELECT ID, Placas, Unidad FROM ".$prefijodb."unidades ";
          $res2 = mysqli_query($cnx_cfdi2, $sql2);
          while($row2 = mysqli_fetch_array($res2)){
              $id_unidad = $row2['ID'];
              $Placa = $row2['Placas'];
              $Unidad = $row2['Unidad'];
              echo "<option value='$id_unidad'>$Unidad / $Placa</option>";
          }
        ?>
      </select>

      <button onClick="hideMe()" type="submit" name="btnGenerar" id="btnGenerar">Generar ZIP</button>
    </form>
   
  </div>

  <script>
    function hideMe(){
      document.getElementById("btnGenerar").style.display = "none";
    }
  </script>
</body>
</html>