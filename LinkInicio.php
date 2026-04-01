<?php 

require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2, $database_cfdi);
mysqli_set_charset($cnx_cfdi2, 'utf8');

$prefijobd = $_GET["prefijodb"];

$resSQL="SELECT * FROM ".$prefijobd."systemsettings";
$runSQL=mysqli_query($cnx_cfdi2,$resSQL);
while ($rowSQL=mysqli_fetch_array($runSQL)){
	$link = $rowSQL["LinkInicio"];
 }

 //$url = "https://www.google.com";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor de URL</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <h1>TEST</h1>

<iframe src="<?php echo htmlspecialchars($url); ?>"></iframe>

</body>
</html>