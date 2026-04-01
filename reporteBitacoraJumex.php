<?php
error_reporting(0);//necesario para poder insertar valores en NULL y no arroje errores 
require_once('lib_mpdf/pdf/mpdf.php');
require_once('cnx_cfdi2.php');
mysqli_select_db($cnx_cfdi2,$database_cfdi);    

$prefijobd = $_GET['prefijodb'];
$id = $_GET["id"];
$time = date('d/m/Y');//CURRENT_TIME

$html='<meta http-equiv="Content-Type" content="text/html; charset= UTF-8">
<style>
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header img {
        max-height: 50px; /* Ajusta el tamaño de la imagen según necesites */
    }
    .header .title {
        flex: 1;
        text-align: center;
    }
    .header .date {
        text-align: right;
    }
</style>
    <thead>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div class="header">
    <div class="title">
    <h3>CONTROL ENTREGA DE EVIDENCIAS JUMEX</h3>
    </div>
    <div class="date">
    <h6>Fecha: '.$time.'</h6>
    </div>
    <img src="http://kamionaje.ddns.net/cfdipro/Grupo_Jumex_logo.svg" alt="Logo">
    </div>

</thead>
<tbody>
    <table border="1" class="tablas_form" style="padding:0px; margin:0 0 15 0; font-size:12px;" cellspacing="0" cellpadding="2" id="table_busqueda_rep">
        <tr>
            <th>No. de Porte</th>
            <th>Transporte</th>
            <th>ID</th>
            <th>Orden de Embarque Jumex</th>
            <th>No. Factura Jumex / Pedido sin Cargo y/o Transferencia</th>
            <th colspan="3">Evidencias</th>
            <th>Operador/Persona que Entrega Documentos</th>
            <th>Observaciones</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <th>Folio</th>
            <th>Sello</th>
            <th>Firma</th>
            <td></td>
            <td></td>
        </tr>';

$queryRemisiones = "SELECT XFolio, '30003977' AS NoProv, RemisionOperador AS Ticket, Factura, Transferencia, FolioJMX,
                    Sello, Firma, (SELECT Operador FROM ".$prefijobd."Operadores WHERE ID = Rem.Operador_RID) AS Operador, IdJumex
                    FROM ".$prefijobd."Remisiones Rem WHERE FolioSubViajes_RID = '".$id."';";
$runsqlRemisiones = mysqli_query($cnx_cfdi2, $queryRemisiones);
if (!$runsqlRemisiones) {//debug
	$mensaje  = 'Consulta no valida [REMISIONES]: ' . mysqli_error() . "\n";
	$mensaje .= 'Consulta completa: ' . $queryRemisiones;
	die($mensaje);
}
while ($rowsqlRemisiones = mysqli_fetch_assoc($runsqlRemisiones)){
	$xfolio = $rowsqlRemisiones['XFolio'];
	$noProv = $rowsqlRemisiones['NoProv'];
	$ticket = $rowsqlRemisiones['Ticket'];
    $factura = $rowsqlRemisiones['Factura'];
    $transferencia = $rowsqlRemisiones['Transferencia'];
    $IdJumex = $rowsqlRemisiones['IdJumex'];
    
    if (!empty($factura) && !empty($transferencia)) {
        $facturaTransferencia = $factura . ' / ' . $transferencia;
    } elseif (!empty($factura)) {
        $facturaTransferencia = $factura;
    } elseif (!empty($transferencia)) {
        $facturaTransferencia = $transferencia;
    } else {
        $facturaTransferencia = ''; 
    }

    $folioJMX = ($rowsqlRemisiones['FolioJMX'] == '1') ? 'X' : '';
    $sello = ($rowsqlRemisiones['Sello'] == '1') ? 'X' : '';
    $firma = ($rowsqlRemisiones['Firma'] == '1') ? 'X' : '';
	$operador = $rowsqlRemisiones['Operador'];

    $html.='<tr>
        <td align="center">'.$xfolio.' </td>
        <td align="left">'.$noProv.' </td>
        <td align="left">'.$IdJumex.' </td>
        <td align="left">'.$ticket.' </td>
        <td align="left">'.$facturaTransferencia.' </td>
        <td align="left">'.$folioJMX.' </td>
        <td align="left">'.$sello.' </td>
        <td align="left">'.$firma.' </td>
        <td align="left">'.$operador.' </td>
        <td align="left"></td>
        
    </tr>';
    }

    $html.='</tbody>
        </table>';


$mpdf = new mPDF('c', 'A4-L');
$css = file_get_contents('css/style_pdf.css');
$mpdf->setFooter(' {DATE d-m-Y H:i} / Tractosoft / Página {PAGENO}');
$mpdf->defaultfooterline = 0;
$mpdf->writeHTML($css, 1);
$mpdf->writeHTML($html);
$mpdf->Output('Reporte_Liquidacion_Jumex'.date("d-m-Y")."_".date("h:i").'.pdf', 'I');
						
?>