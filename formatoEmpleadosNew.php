<?php
ini_set('memory_limit', '2048M');
ini_set('default_charset', 'utf-8');
set_time_limit(2000);
ini_set('max_execution_time', 2000); // Aumentar tiempo de ejecución
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

require 'phpqrcode/qrlib.php';
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
}



require_once('cnx_cfdi.php');
require_once('cnx_cfdi2.php');

//Internalizo los parametros previo escape de caracteres especiales
$prefijobd = @mysqli_escape_string($cnx_cfdi2, $_GET["prefijodb"]);

$tipoImpresion = $_GET["tipo"];

$idFolio = $_GET["idfolio"];

mysqli_select_db($cnx_cfdi2, $database_cfdi);

mysqli_query($cnx_cfdi2, "SET NAMES 'utf8'");

$prefijo = rtrim($prefijobd, "_");

$rutalogo= '../cfdipro/imagenes/'.$prefijo.'.jpg';


if ($tipoImpresion == 'vac') {

    $resSQLVac= "SELECT 
                    a.Nombre, 
                    a.NumNomina, 
                    a.FechaIngreso, 
                    a.Cargo,  
                    a.Departamento, 
                    a.TipoContrato,  
                    a.DiaDescansoLunes,
                    a.DiaDescansoMartes,
                    a.DiaDescansoMiercoles,
                    a.DiaDescansoJueves,
                    a.DiaDescansoViernes,
                    a.DiaDescansoSabado,
                    a.DiaDescansoDomingo,  
                    b.TipoVacaciones, 
                    b.FechaInicio, 
                    b.FechaFin, 
                    b.Reintegro, 
                    b.Comentarios,
                    b.Dias,
                    b.DiasCorrespondientes,
                    b.DiasSolicitados, 
                    b.DiasRestantes,
                    c.Inicio,
                    c.Fin,
                    d.Jornada as JornadaLaboral
                        FROM {$prefijobd}empleados AS a
                        INNER JOIN {$prefijobd}empleadosvacaciones AS b ON a.ID = b.FolioSub_RID 
                        INNER JOIN {$prefijobd}periodos AS c ON b.Periodo_RID = c.ID
                        INNER JOIN {$prefijobd}jornadaslaborales AS d ON d.ID = a.JornadaLaboral_RID
                WHERE b.ID = '{$idFolio}'";

  $runSQLVac = mysqli_query($cnx_cfdi2, $resSQLVac);
if ($runSQLVac === false) {
    die("Error en la consulta: " . mysqli_error($cnx_cfdi2));
}
if (mysqli_num_rows($runSQLVac) == 0) {
    die("La consulta no devolvió filas. Revisa idFolio='{$idFolio}' y el prefijo '{$prefijobd}'
    '{$resSQLVac}'");
}


$rowSQLVac = mysqli_fetch_assoc($runSQLVac);
if (!$rowSQLVac) {
    die("No se pudo leer la fila. fetch_assoc devolvió false.");
}



$nombreEmpleado    = isset($rowSQLVac['Nombre']) ? $rowSQLVac['Nombre'] : '';
$numNomina         = isset($rowSQLVac['NumNomina']) ? $rowSQLVac['NumNomina'] : '';
$fechaIngresoRaw   = isset($rowSQLVac['FechaIngreso']) ? $rowSQLVac['FechaIngreso'] : null;
$cargoLab          = isset($rowSQLVac['Cargo']) ? $rowSQLVac['Cargo'] : '';
$jornadaLab        = isset($rowSQLVac['JornadaLaboral']) ? $rowSQLVac['JornadaLaboral'] : '';
$departamento      = isset($rowSQLVac['Departamento']) ? $rowSQLVac['Departamento'] : '';
$tipoContrato      = isset($rowSQLVac['TipoContrato']) ? $rowSQLVac['TipoContrato'] : '';
$diaDescansoLunes  = isset($rowSQLVac['DiaDescansoLunes']) ? $rowSQLVac['DiaDescansoLunes'] : null;
$diaDescansoMartes = isset($rowSQLVac['DiaDescansoMartes']) ? $rowSQLVac['DiaDescansoMartes'] : null;
$diaDescansoMiercoles = isset($rowSQLVac['DiaDescansoMiercoles']) ? $rowSQLVac['DiaDescansoMiercoles'] : null;
$diaDescansoJueves  = isset($rowSQLVac['DiaDescansoJueves']) ? $rowSQLVac['DiaDescansoJueves'] : null;
$diaDescansoViernes  = isset($rowSQLVac['DiaDescansoViernes']) ? $rowSQLVac['DiaDescansoViernes'] : null;
$diaDescansoSabado  = isset($rowSQLVac['DiaDescansoSabado']) ? $rowSQLVac['DiaDescansoSabado'] : null;
$diaDescansoDomingo = isset($rowSQLVac['DiaDescansoDomingo']) ? $rowSQLVac['DiaDescansoDomingo'] : null;
$tipoVacaciones    = isset($rowSQLVac['TipoVacaciones']) ? $rowSQLVac['TipoVacaciones'] : '';
$fechaInicioVacRaw = isset($rowSQLVac['FechaInicio']) ? $rowSQLVac['FechaInicio'] : null;
$fechaFinVacRaw    = isset($rowSQLVac['FechaFin']) ? $rowSQLVac['FechaFin'] : null;
$fechaReintegroRaw = isset($rowSQLVac['Reintegro']) ? $rowSQLVac['Reintegro'] : null;
$comentarios       = isset($rowSQLVac['Comentarios']) ? $rowSQLVac['Comentarios'] : '';
$periodoInicioRaw  = isset($rowSQLVac['Inicio']) ? $rowSQLVac['Inicio'] : null;
$periodoFinRaw     = isset($rowSQLVac['Fin']) ? $rowSQLVac['Fin'] : null;
$diasAdelantados   = isset($rowSQLVac['Dias']) ? (int)$rowSQLVac['Dias'] : 0;
$diasSolicitados   = isset($rowSQLVac['DiasSolicitados']) ? (int)$rowSQLVac['DiasSolicitados'] : 0;
$diasCorrespondientes = isset($rowSQLVac['DiasCorrespondientes']) ? (int)$rowSQLVac['DiasCorrespondientes'] : 0;
$diasRestantes     = isset($rowSQLVac['DiasRestantes']) ? (int)$rowSQLVac['DiasRestantes'] : 0;

$diaDescansoMap  = array(
    'lunes'      => $diaDescansoLunes,
    'martes'     => $diaDescansoMartes,
    'miercoles'  => $diaDescansoMiercoles,
    'jueves'     => $diaDescansoJueves,
    'viernes'    => $diaDescansoViernes,
    'sabado'     => $diaDescansoSabado,
    'domingo'    => $diaDescansoDomingo
);
// Validar fechas 
$invalids = array('', null, '0000-00-00', '0000-00-00 00:00:00');
if (in_array($fechaIngresoRaw, $invalids, true)) {
    die("FechaIngreso inválida: " . var_export($fechaIngresoRaw, true));
}

// Crea DateTime 
try {
    $fechaIngresoDT   = new DateTime($fechaIngresoRaw);
    $fechaInicioVacDT = new DateTime($fechaInicioVacRaw);
    $fechaFinVacDT    = new DateTime($fechaFinVacRaw);
    $fechaReintegroDT = (in_array($fechaReintegroRaw, $invalids, true)) ? null : new DateTime($fechaReintegroRaw);
} catch (Exception $e) {
    die("Error creando DateTime: " . $e->getMessage());
}

// Formatos
$fechaIngresoT   = $fechaIngresoDT->format('Y-m-d');
$fechaInicioVacT = $fechaInicioVacDT->format('Y-m-d');
$fechaFinVacT    = $fechaFinVacDT->format('Y-m-d');
$fechaReintegroT = $fechaReintegroDT ? $fechaReintegroDT->format('Y-m-d') : '';

// Calcular antigüedad
$fechaHoy  = new DateTime("now");
$intervalo = $fechaIngresoDT->diff($fechaHoy);
$anios     = (int)$intervalo->y;
$meses     = (int)$intervalo->m;

// Funcion vacaciones 
function calcularVacaciones($aniosTrabajados) {
    if ($aniosTrabajados < 1) return 0;
    if ($aniosTrabajados == 1) return 12;
    if ($aniosTrabajados == 2) return 14;
    if ($aniosTrabajados == 3) return 16;
    if ($aniosTrabajados == 4) return 18;
    if ($aniosTrabajados == 5) return 20;
    $diasBase = 22;
    $bloques = floor(($aniosTrabajados - 6) / 5);
    return $diasBase + ($bloques * 2);
}


/* function diasSolicitados($inicioDT, $finDT, $diaDescansoMap, $cnx_cfdi2, $prefijobd) {

    if (!$inicioDT instanceof DateTime) $inicioDT = new DateTime($inicioDT);
    if (!$finDT instanceof DateTime)   $finDT   = new DateTime($finDT);

    
    $mapDiaAN = array(
        'lunes'     => 1,
        'martes'    => 2,
        'miercoles' => 3,
        'miércoles' => 3,
        'jueves'    => 4,
        'viernes'   => 5,
        'sabado'    => 6,
        'sábado'    => 6,
        'domingo'   => 7,
    );


    $descansoDias = array();

    if (is_array($diaDescansoMap)) {
        foreach ($diaDescansoMap as $diaTxt => $flag) {
            $diaTxt = strtolower(trim((string)$diaTxt));
            if ($diaTxt === '') continue;

           
            $esDescanso = false;

            if (is_bool($flag)) {
                $esDescanso = $flag;
            } else {
                $flagStr = trim((string)$flag);
                $esDescanso = ($flagStr === '1' || strtolower($flagStr) === 'true' || strtolower($flagStr) === 'si' || strtolower($flagStr) === 'sí');
            }

            if ($esDescanso && isset($mapDiaAN[$diaTxt])) {
                $descansoDias[ $mapDiaAN[$diaTxt] ] = true;
            }
        }
    }

    
    $descansoDias = array_keys($descansoDias);

    $inicioStr = $inicioDT->format('Y-m-d');
    $finStr    = $finDT->format('Y-m-d');

    $inicioStrEsc = $cnx_cfdi2->real_escape_string($inicioStr);
    $finStrEsc    = $cnx_cfdi2->real_escape_string($finStr);

    $prefijoLimpio = preg_replace('/[^A-Za-z0-9_]/', '', $prefijobd);
    $tablaFestivos = $prefijoLimpio . 'diasfestivos';

    $festivos = array();
    $sqlFestivos = "
        SELECT Fecha
        FROM `$tablaFestivos`
        WHERE Fecha BETWEEN '$inicioStrEsc' AND '$finStrEsc'
    ";

    $resFest = $cnx_cfdi2->query($sqlFestivos);
    if ($resFest) {
        while ($row = $resFest->fetch_assoc()) {
            $festivos[$row['Fecha']] = true;
        }
        $resFest->free();
    }

    $endInclusive = clone $finDT;
    $endInclusive->modify('+1 day');
    $period = new DatePeriod($inicioDT, new DateInterval('P1D'), $endInclusive);

    $contador = 0;
    foreach ($period as $fecha) {
        $numDiaSemana = (int)$fecha->format('N');   
        $fechaStrLoop = $fecha->format('Y-m-d');   

       
        if (!empty($descansoDias) && in_array($numDiaSemana, $descansoDias, true)) {
            continue;
        }
        if (isset($festivos[$fechaStrLoop])) {
            continue;
        }

        $contador++;
    }

    return $contador;
}




$diasSolicitados   = diasSolicitados($fechaInicioVacDT, $fechaFinVacDT, $diaDescansoMap, $cnx_cfdi2, $prefijobd); */
$diasVacacionesLFT = $diasCorrespondientes;

$vac = trim($tipoVacaciones);

if (($vac=='Reglamentarias') || ($vac=='Adelantadas')) {
    $diasCorrespondientes = $diasVacacionesLFT;
} else if ($vac=='Atrasadas') {
    $diasCorrespondientes = $diasAdelantados;
}
// Helper para dibujar la casilla
function cb($checked, $label){
    $mark = $checked ? 'X' : '   &nbsp;   '; // ASCII puro
    // cuadrito con borde y la X centrada
    return '<span style="
        display:inline-block;
        width:15px;height:15px;
        border:1px solid #000;
        text-align:center;line-height:15px;
        font-size:15px;font-weight:bold;
    ">'.$mark.'</span> '.$label;
}

ob_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Vacaciones</title>
</head>
		<style>
			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
    		}
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: helvetica;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 0px; 
				font-family: helvetica;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: helvetica;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
		
		
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
<body>
<htmlpageheader name="myHeader">
    <table width="100%">
        <tr>
            <td style="text-align:left;  vertical-align:top;">  
				<img src="<?php echo $rutalogo; ?>" style="width: 160px; height: auto;" alt="Logo" /> 
			</td>
            <td style=" font-size:20px; text-align:center; background-color: #a1a1a3;"><h4>Solicitud de Vacaciones</h4></td>
        </tr>
    </table>
</htmlpageheader>
<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
           

<htmlpagefooter name="myFooter">
    <div style="margin-top: 10px;">
                <table width="100%" cellpadding="6" cellspacing="0"
                        style="border-collapse:collapse; font-size:12px; table-layout:fixed;">

                    <tr>
                        <td>
                            <?php echo $comentarios; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-size:13px; text-align:center;">
                            <b>POR EL PRESENTE EXPRESO MI CONFORMIDAD DE SOLICITAR Y GOZAR MIS VACACIONES DE ACUERDO A LO QUE ESTABLECE EL ATICULO 76 Y 78 DE LA LEY FEDERAL DEL TRABAJO.</b> 
                        </td>
                    </tr>

                    <tr>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    </tr>
                    

                    <tr>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Conformidad del Empleado</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Autorización del Gerente del Area y/o Director</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>VO. BO. Recursos humanos</b>
                        </td>
                    </tr>
                     <tr>
                        <td colspan="3" style="font-size:11px; text-align:left;">
                            <b>CONSIDERAR LAS SIGUIENTES POLITICAS INTERNAS</b> <br>
                            <b>Las Vacaciones no son renunciables ni acumulables</b> <br>
                            <b>La solicitud de vacaciones debe ser aprobada por el empleador o la persona designada</b><br>
                            <b>Es fundamental acordar las fechas de vacaciones con el empleador, considerando las necesidades del servicio y el derecho del trabajador a descansar dentro de un periodo de anticipacion de 1 mes </b>
                        </td>
                    </tr>
                </table>
            </div>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" />


    <main>

        <table border= "1" style= "border-collapse:collapse;" width= "100%">
            <thead>
                <tr >
                    <td colspan='4' style="background-color: #818181ff; font-size:20px; "><b>DATOS DEL SOLICITANTE</b></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan='3' style="background-color: #818181ff; font-size:16px;"><b>Nombre de Empleado</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Numero de Nomina</b></td>
                </tr>
                <tr>
                    <td colspan='3'><?php echo $nombreEmpleado; ?></td>
                    <td colspan='2'><?php echo $numNomina;?></td>
                </tr>
                <tr>
                    <td colspan='3' style="background-color: #818181ff; font-size:16px;"><b>Cargo</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Departamento</b></td>
                </tr>
                <tr>
                    <td colspan='3'><?php echo $cargoLab;?></td>
                    <td colspan='2'><?php echo $departamento;?></td>
                </tr>
                <tr>
                    <td colspan='1' style="background-color: #818181ff; font-size:16px;"><b>Antigüedad</b></td>
                    <td colspan='1' style="background-color: #818181ff; font-size:16px;"><b>Periodo</b></td>
                    <td colspan='1' style="background-color: #818181ff; font-size:16px;"><b>Dias Correspondientes</b></td>
                    <?php if (($vac == 'Reglamentarias') || ($vac == 'Atrasadas')) {
                        echo "
                                <td colspan='2' style='background-color: #818181ff; font-size:16px;'><b>Fecha ingreso</b></td>
                            </tr>
                                <tr>
                                    <td colspan='1'>".$anios." año(s) y ".$meses." mes(es)</td>
                                    <td colspan='1'>".$periodoInicioRaw." - ".$periodoFinRaw."</td>
                                    <td colspan='1'>".$diasCorrespondientes."</td>
                                    <td colspan='2'>".$fechaIngresoT."</td>
                                </tr>";
                    } else if ($vac == 'Adelantadas') {
                        echo "<td colspan='1' style='background-color: #818181ff; font-size:16px;'><b>Dias</b></td>
                              <td colspan='1' style='background-color: #818181ff; font-size:16px;'><b>Fecha ingreso</b></td>
                              </tr>
                                <tr>
                                    <td colspan='1'>".$anios." año(s) y ".$meses." mes(es)</td>
                                    <td colspan='1'>".$periodoInicioRaw." - ".$periodoFinRaw."</td>
                                    <td colspan='1'>".$diasCorrespondientes."</td>
                                    <td colspan='1'>".$diasAdelantados."</td>
                                    <td colspan='1'>".$fechaIngresoT."</td>
                                </tr>";
                    }
                   
                    ?>
                    
              
                <tr>
                    <td colspan='3' style="background-color: #818181ff; font-size:16px;"><b>Tipo de Contrato</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Jornada Laboral</b></td>
                </tr>
                <tr>
                    <td colspan='3'><?php echo $tipoContrato; ?></td>
                    <td colspan='2'><?php echo $jornadaLab; ?></td>
                </tr>
            </tbody>
        </table>
            <br>
    <table border= "1" style= "border-collapse:collapse;" width= "100%">
        <thead>
            <tr >
                <td colspan='3' style="background-color: #818181ff; font-size:20px; "><b>DATOS DE LA SOLICITUD DE VACACIONES</b></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td  style="background-color: #818181ff; font-size:20px; "  colspan='3'>
                    <b>Tipo de Vacaciones</b>
                </td>
            </tr>
               <tr>
                    <td><?= cb($vac === 'Reglamentarias', 'Reglamentarias'); ?></td>
                    <td><?= cb($vac === 'Atrasadas', 'Atrasadas'); ?></td>
                    <td><?= cb($vac === 'Adelantadas', 'Adelantadas'); ?></td>
            </tr>            
        </tbody>
    </table>
      <table border= "0" style= "border-collapse:collapse;" width= "100%">
        <thead>
             <tr>
                <td  style="background-color: #818181ff; font-size:20px; "  colspan='3'>
                    <b>Periodo de Vacaciones</b>
                </td>
            </tr>
        </thead>
        <tbody>
           
               <tr>
                    <td>FECHA SOLICITUD</td>
                    <td>DIA/MES/AÑO</td>
                    <td>DIAS RESTANTES</td>
            </tr> 
             <tr>
                    <td>Desde: </td>
                    <td><?php echo date("d-m-Y", strtotime($fechaInicioVacRaw)); ?> </td>
                    <td><?php if ($vac == 'Atrasadas') {
                        echo $diasCorrespondientes;
                    } else {
                        echo $diasRestantes;
                    }
                    ?> </td>
            </tr>
            <tr>
                    <td>Hasta: </td>
                    <td><?php echo date("d-m-Y", strtotime($fechaFinVacRaw)); ?> </td>
                    <td></td>
            </tr> 
             <tr>
                    <td></td>
                    <td></td>
                    <td>DIAS SOLICITADOS</td>
            </tr>  
             <tr>
                    <td>Reintegro: </td>
                    <td><?php echo date("d-m-Y", strtotime($fechaReintegroRaw)); ?> </td>
                    <td style='background-color: #111172ff; color:#ffffff;'><?php echo $diasSolicitados ?></td>
            </tr>            
        </tbody>
    </table>

    

    </main>
    
</body>
</html>


<?php
   
} elseif ($tipoImpresion == 'inc') {

    $resSQLInc = "SELECT 
                    a.Nombre, 
                    a.NumNomina, 
                    a.FechaIngreso, 
                    a.Cargo, 
                    a.Departamento, 
                    a.TipoContrato,   
                    b.FechaHoraIncidencia, 
                    b.LugarIncidencia, 
                    b.TipoIncidencia, 
                    b.DescripcionIncidencia, 
                    b.Testigos 
                FROM {$prefijobd}empleados AS a
                INNER JOIN {$prefijobd}empleadosincidencias AS b ON a.ID = b.FolioSub_RID WHERE b.ID = '{$idFolio}' ";
                  $runSQLInc = mysqli_query($cnx_cfdi2, $resSQLInc);
                    if ($runSQLInc === false) {
                        die("Error en la consulta: " . mysqli_error($cnx_cfdi2));
                    }
                    if (mysqli_num_rows($runSQLInc) == 0) {
                        die("La consulta no devolvió filas. Revisa idFolio='{$idFolio}' y el prefijo '{$prefijobd}'");
                    }


                    $rowSQLInc = mysqli_fetch_assoc($runSQLInc);
                    if (!$rowSQLInc) {
                        die("No se pudo leer la fila. fetch_assoc devolvió false.");
                    }



                    $nombreEmpleado         = isset($rowSQLInc['Nombre']) ? $rowSQLInc['Nombre'] : '';
                    $numNomina              = isset($rowSQLInc['NumNomina']) ? $rowSQLInc['NumNomina'] : '';
                    $cargoLab               = isset($rowSQLInc['Cargo']) ? $rowSQLInc['Cargo'] : '';
                    $departamento           = isset($rowSQLInc['Departamento']) ? $rowSQLInc['Departamento'] : '';
                    $tipoContrato           = isset($rowSQLInc['TipoContrato']) ? $rowSQLInc['TipoContrato'] : '';
                    $fechaHoraIncidenciaRaw = isset($rowSQLInc['FechaHoraIncidencia']) ? $rowSQLInc['FechaHoraIncidencia'] : null;
                    $lugarIncidencia        = isset($rowSQLInc['LugarIncidencia']) ? $rowSQLInc['LugarIncidencia'] : null;
                    $tipoIncidencia         = isset($rowSQLInc['TipoIncidencia']) ? $rowSQLInc['TipoIncidencia'] : null;
                    $descripcionIncidencia  = isset($rowSQLInc['DescripcionIncidencia']) ? $rowSQLInc['DescripcionIncidencia'] : '';
                    $testigos               = isset($rowSQLInc['Testigos']) ? $rowSQLInc['Testigos'] : '';

               

    


ob_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acuse de Incidencia</title>
</head>
		<style>
			@page {
                margin: 150px 25px;
            }

			body {
        		font-family: helvetica !important;
    		}
			
			header {
                position: fixed;
                top: -130px;
                left: 0px;
                right: 0px;
                height: 100px;
				font-family: helvetica;
				

            }
			
			footer {
                position: fixed; 
                bottom: 10px; 
                left: 0px; 
                right: 0px;
                height: 0px; 
				font-family: helvetica;

            }
			
			main {
			position: relative;
			top: 0px;
			left: 0cm;
			right: 0cm;
			margin-bottom: 195px;
			font-family: helvetica;

		  } 
		  .page-break {
				page-break-after: always;
			}
		  
			
			
		</style>
		
		
		
		<style>
			.page-break {
				page-break-after: always;
			}
		</style>
<body>
<htmlpageheader name="myHeader">
    <table width="100%">
        <tr>
            <td style="text-align:left;  vertical-align:top;">  
				<img src="<?php echo $rutalogo; ?>" style="width: 160px; height: auto;" alt="Logo" /> 
			</td>
            <td style=" font-size:20px; text-align:center; background-color: #a1a1a3;"><h4>Acuse de Incidencia</h4></td>
        </tr>
    </table>
</htmlpageheader>
<sethtmlpageheader name="myHeader" value="on" show-this-page="all" />
           

<htmlpagefooter name="myFooter">
    <div style="margin-top: 10px;">
                <table width="100%" cellpadding="6" cellspacing="0"
                        style="border-collapse:collapse; font-size:12px; table-layout:fixed;">

                 

                    <tr>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    <td style="width:33.33%; height:100px; border:1px solid #999;">&nbsp;</td>
                    </tr>
                    

                    <tr>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Conformidad del Empleado</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>Firma de Autorización del Gerente del Area y/o Director</b>
                        </td>
                        <td style="text-align:center; background:#a1a1a3; color:#000; font-size:12px; border:1px solid #999;">
                            <b>VO. BO. Recursos humanos</b>
                        </td>
                    </tr>
                   
                </table>
            </div>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter" value="on" />


    <main>

        <table border= "1" style= "border-collapse:collapse;" width= "100%">
            <thead>
                <tr >
                    <td colspan='4' style="background-color: #818181ff; font-size:20px; "><b>DATOS DEL EMPLEADO</b></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Nombre de Empleado</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Numero de Nomina</b></td>
                </tr>
                <tr>
                    <td colspan='2'><?php echo $nombreEmpleado; ?></td>
                    <td colspan='2'><?php echo $numNomina;?></td>
                </tr>
                <tr>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Cargo</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Departamento</b></td>
                </tr>
                <tr>
                    <td colspan='2'><?php echo $cargoLab;?></td>
                    <td colspan='2'><?php echo $departamento;?></td>
                </tr>
                <tr>
                    <td colspan='1' style="background-color: #818181ff; font-size:16px;"><b>Dia de la Incidencia</b></td>
                    <td colspan='1' style="background-color: #818181ff; font-size:16px;"><b>Lugar de la Incidencia</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Tipo de Incidencia</b></td>
                </tr>
                <tr>
                    <td colspan='1'><?php echo  date("d-m-Y H:i:s", strtotime($fechaHoraIncidenciaRaw));?></td>
                    <td colspan='1'><?php echo $lugarIncidencia; ?></td>
                    <td colspan='2'><?php echo $tipoIncidencia; ?></td>
                </tr>
                <tr>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Descripcion de la Incidencia</b></td>
                    <td colspan='2' style="background-color: #818181ff; font-size:16px;"><b>Testigos</b></td>
                </tr>
                <tr>
                    <td colspan='2'><?php echo $descripcionIncidencia; ?></td>
                    <td colspan='2'><?php echo $testigos; ?></td>
                </tr>
            </tbody>
        </table>
            <br>
 
    

    </main>
    
</body>
</html> 

<?php
}
require_once __DIR__ . '/vendor/autoload.php';
$html = ob_get_clean();
//die($html);
// Cargar mPDF sin namespace y con constructor antiguo
$mpdf = new mPDF('utf-8', 'letter'); // O simplemente new mPDF();


$mpdf->SetFont('Helvetica');
$mpdf->WriteHTML($html);

$nombre_pdf = $prefijo . " - Vacaciones.pdf";


$mpdf->Output($nombre_pdf, 'I');

// 👉 Para ver en el navegador cambia 'D' por 'I'
// $mpdf->Output($nombre_pdf, 'D');
exit;



?>