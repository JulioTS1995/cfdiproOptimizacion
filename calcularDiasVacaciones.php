<?php

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        echo '<pre>';
        print_r($error);
        echo '</pre>';
    }
});

function calcularDiasSolicitados(
    DateTime $desde,
    DateTime $hasta,
    array $diasFestivos,
    array $diasDescanso
) {
    $diasSolicitados = 0;
    $actual = clone $desde;

    $festivos = array_flip($diasFestivos);
    $descansos = array_flip($diasDescanso);

    while ($actual <= $hasta) {

        $diaSemana = (int)$actual->format('N'); // 1-7
        $fechaStr = $actual->format('Y-m-d');

        $esDescanso = isset($descansos[$diaSemana]);
        $esFestivo  = isset($festivos[$fechaStr]);

        // Se cuenta solo si NO es descanso y NO es festivo
        if (!$esDescanso && !$esFestivo) {
            $diasSolicitados++;
        }

        $actual->modify('+1 day');
    }

    return $diasSolicitados;
}

$prefijo = $_GET["prefijo"];
$id = $_GET["id"];
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);

$query = "SELECT ev.FechaInicio, ev.FechaFin, ev.TipoVacaciones, ev.Periodo_RID, ev.FechaSolicitud, e.FechaIngreso, ev.FolioSub_RID AS EmpleadoID, 
e.DiaDescansoLunes, e.DiaDescansoMartes, e.DiaDescansoMiercoles, e.DiaDescansoJueves, e.DiaDescansoViernes, e.DiaDescansoSabado, e.DiaDescansoDomingo, ev.Dias, 
(SELECT RangoDiasRestantes FROM {$prefijo}SystemSettings LIMIT 1) AS DiasGracia
FROM {$prefijo}EmpleadosVacaciones ev 
LEFT JOIN {$prefijo}Empleados e ON e.ID = ev.FolioSub_RID 
WHERE ev.ID = {$id};";
$runsql = mysqli_query($cnx_cfdi2, $query);
if (!$runsql) {//debug
    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
    $mensaje .= 'Consulta completa: ' . $query;
    die($mensaje);
}
$diasDescanso = [];
while ($rowsql = mysqli_fetch_assoc($runsql)){
    $desde = new DateTime($rowsql['FechaInicio']);
    $hasta = new DateTime($rowsql['FechaFin']);
    $tipoVac = $rowsql['TipoVacaciones'];
    $periodoId = $rowsql['Periodo_RID'];
    $empleadoId = $rowsql['EmpleadoID'];
    $fechaSolicitud = new DateTime($rowsql['FechaSolicitud']);
    $fechaIngreso   = new DateTime($rowsql['FechaIngreso']);
    $diasActuales = $rowsql['Dias'];
    $diasGracia = $rowsql['DiasGracia'];
    if (!empty($rowsql['DiaDescansoLunes']))     $diasDescanso[] = 1;
    if (!empty($rowsql['DiaDescansoMartes']))    $diasDescanso[] = 2;
    if (!empty($rowsql['DiaDescansoMiercoles'])) $diasDescanso[] = 3;
    if (!empty($rowsql['DiaDescansoJueves']))    $diasDescanso[] = 4;
    if (!empty($rowsql['DiaDescansoViernes']))   $diasDescanso[] = 5;
    if (!empty($rowsql['DiaDescansoSabado']))    $diasDescanso[] = 6;
    if (!empty($rowsql['DiaDescansoDomingo']))   $diasDescanso[] = 7;

}

$antiguedad = $fechaIngreso->diff($fechaSolicitud);
$antiguedad = $antiguedad->y;

if($tipoVac == 'Adelantadas'){
    $antiguedad++;
}

if(!$antiguedad){
    echo "<script>alert('Es necesario que el empleado tenga almenos un año de antiguedad si las vacaciones no son adelantadas');</script>";

    exit;
}

if(!$periodoId){
    echo "<script>alert('Es necesario registrar un periodo');</script>";

    exit;
}

$aniversario = clone $fechaIngreso;
$aniversario->modify("+{$antiguedad} years");

$fechaLimite = clone $aniversario;
$fechaLimite->modify("+{$diasGracia} days");

if ($tipoVac == 'Atrasadas') {
    if ($desde > $fechaLimite) {
        echo "<script>alert('El periodo para tomar vacaciones atrasadas ha vencido');</script>";
        exit;
    }
}

$diasFestivos = []; 

$query1 = "SELECT Fecha
FROM {$prefijo}DiasFestivos 
WHERE FolioSub_RID = {$periodoId};";
$runsql1 = mysqli_query($cnx_cfdi2, $query1);
if (!$runsql1) {//debug
    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
    $mensaje .= 'Consulta completa: ' . $query1;
    die($mensaje);
}
while ($rowsql1 = mysqli_fetch_assoc($runsql1)){
    $diasFestivos[] = date('Y-m-d', strtotime($rowsql1['Fecha']));
}


$query2 = "SELECT SUM(Dias) AS DiasTomados, 
(SELECT DiasAcomulados FROM {$prefijo}Vacaciones WHERE Anios = {$antiguedad}) AS DiasAcomulados, 
(SELECT DiasVacaciones FROM {$prefijo}Vacaciones WHERE Anios = {$antiguedad}) AS DiasVacaciones
FROM {$prefijo}EmpleadosVacaciones 
WHERE FolioSub_RID = {$empleadoId} AND ID <> {$id};";
$runsql2 = mysqli_query($cnx_cfdi2, $query2);
if (!$runsql2) {//debug
    $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
    $mensaje .= 'Consulta completa: ' . $query;
    die($mensaje);
}
while ($rowsql2 = mysqli_fetch_assoc($runsql2)){
    $diasAcomulados = $rowsql2['DiasAcomulados'];
    $diasCorrespondientes = $rowsql2['DiasVacaciones'];

    $diasTomados = isset($rowsql2['DiasTomados']) ? $rowsql2['DiasTomados'] : 0;
}

$diasSolicitados = calcularDiasSolicitados($desde, $hasta, $diasFestivos, $diasDescanso);

//  die($diasAcomulados. "-". $diasTomados. "-".$diasSolicitados);

$diasRestantes = $diasAcomulados - $diasTomados - $diasSolicitados;

if($diasActuales != $diasSolicitados){
    $query3 = "UPDATE {$prefijo}EmpleadosVacaciones SET Dias = {$diasSolicitados}, DiasRestantes = {$diasRestantes}, DiasCorrespondientes = {$diasCorrespondientes}
    WHERE ID = {$id};";
    //die($query3);
    $runsql3 = mysqli_query($cnx_cfdi2, $query3);
    if (!$runsql3) {//debug
        $mensaje  = 'Consulta no valida: ' . mysql_error() . "\n";
        $mensaje .= 'Consulta completa: ' . $query;
        die($mensaje);
    }else{
        echo "<script>alert('dias calculados correctamente, refresque el formulario');</script>";//Imprime exito

    }
}else{
        echo "<script>alert('los dias calculados son iguales a los actuales, se omite el proceso');</script>";
}



