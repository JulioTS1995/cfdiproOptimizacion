<?php
   
	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	if (!isset($_GET['prefijodb']) || empty($_GET['prefijodb'])) {
    die("Falta el prefijo de la BD");
	}
	
	if (!isset($_GET['id']) || empty($_GET['id'])) {
		die("Falta Remision");
	}
	
	$id_remision = $_GET["id"];
	
	 
	
	//$id_compra = 1640739;
	
	$prefijobd = @mysql_escape_string($_GET["prefijodb"]);
	
	//$prefijobd = "solosa_";
	
	//Buscar datos Remision
	$sql03="SELECT * FROM " . $prefijobd . "remisiones WHERE ID = ".$id_remision;
	$res_sql03=mysql_query($sql03);
								
	while ($fila_sql03 = mysql_fetch_array($res_sql03)){
        $destinatario_domicilio_remision = $fila_sql03['DestinatarioDomicilio'];
		$destinatario_localidad_remision = $fila_sql03['DestinatarioLocalidad'];
		$remitente_domicilio_remision = $fila_sql03['RemitenteDomicilio'];
		$remitente_localidad_remision = $fila_sql03['RemitenteLocalidad'];
		$xfolio_remision = $fila_sql03['XFolio'];
		
	}
	
	$v_dir_destinatario = $destinatario_domicilio_remision." ".$destinatario_localidad_remision;
	$v_dir_remitente = $remitente_domicilio_remision." ".$remitente_localidad_remision;


?>
<!DOCTYPE html>
<html lang="es-ES">

<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow">


  <title>Tractosoft Rutas</title>
  <link rel="shortcut icon" href="imagenes/logo_ts.ico">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
 
 <!--<link rel="stylesheet" type="text/css" href="../css/consultas.css"> -->
    <style>
      html, body, #map-canvas {
        
		width:100%;
		height: 100%;
        margin: auto;
        padding: 0px
      }
    </style>

  <meta name="description" content="Crear rutas con puntos intermedios y gráfico de altitudes">
  <meta name="keywords" content="Crear rutas con puntos intermedios y gráfico de altitudes">
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js?"></script>
  <script src="https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places,weather&amp;language=es&amp;region=ES&amp;key=AIzaSyBd4XNbsJSeLdInftHuNUFx5gToDmKQHlA"></script>

    

</head>

<body onload="calcRuta()">

    <?php 

      //Asignar puntos

      $origen =  $v_dir_remitente;
      $destino = $v_dir_destinatario;

      $int1 = "";
      $int2 = "";
      $int3 = "";
      $int4 = "";
      $int5 = "";

    ?>

      <div class="container1" style="margin-top: 0;">
      <div style="margin-top: 20px;left: 35%; position:fixed;">
        <h1 class="titulo_1 col-12">Ruta Remision <?php echo $xfolio_remision; ?></h1>
		<!--<h5 class="titulo_1 col-12">Origen <?php echo $origen; ?> Destino <?php echo $destino; ?></h5>-->
      </div>
      <div style="margin:0px 0px 0px 100px;left: 2%;">
            <img src="imagenes/logo_ts.png" alt="tslogo" height="120">
        </div>
    
      
      
     
	

   </div>

    <div style="width:940px; margin:auto; margin-top:20; display: none"> 
    
    <div Style="border:solid 1px #CCCCCC; border-radius:10px ; width:width:890px;height 300px font-family:arial; font-size:10px; margin:10px 0px 0px 20px;"><br>
    
    <div Style="family:arial; font-size:10px; margin:0px 0px 0px 10px;"> 
      
    <label>Origen:  <input type="text" size="20" id="origen" name="origen" value="<?php echo $origen; ?>" style="width: 355px; padding-left:5px;"  ></label>

    <label>Destino:  <input type="text" size="20" id="destino" name="destino" value="<?php echo $destino; ?>" onclick="this.value=''" style="width: 355px; padding-left:5px;"></label>   
   <!--<br>
   <br>   
      <input type="button" class="button" onclick="getLocation();" value="Mi posición como origen">
   <br>
   <br>-->
   <br>
   <br>
      <b>Puntos intermedios:</b>
   <br>
   <br>      
      <div id="AllPoints">
        <!--<div id="Waypoint" style="display:none">-->
        <div id="Waypoint">
          <input type="text" name="points" size="50" placeholder="Intermedio1 ..." onfocus="autocompletar()" value="<?php echo $int1; ?>" />
          <!--<button onclick="DeletePoint(this)">X</button>-->
        </div>
        <div id="Waypoint">
          <input type="text" name="points" size="50" placeholder="Intermedio2 ..." onfocus="autocompletar()" value="<?php echo $int2; ?>" />
          <!--<button onclick="DeletePoint(this)">X</button>-->
        </div>
        <div id="Waypoint">
          <input type="text" name="points" size="50" placeholder="Intermedio3 ..." onfocus="autocompletar()" value="<?php echo $int3; ?>" />
          <!--<button onclick="DeletePoint(this)">X</button>-->
        </div>
        <div id="Waypoint">
          <input type="text" name="points" size="50" placeholder="Intermedio4 ..." onfocus="autocompletar()" value="<?php echo $int4; ?>" />
          <!--<button onclick="DeletePoint(this)">X</button>-->
        </div>
        <div id="Waypoint">
          <input type="text" name="points" size="50" placeholder="Intermedio5 ..." onfocus="autocompletar()" value="<?php echo $int5; ?>" />
          <!--<button onclick="DeletePoint(this)">X</button>-->
        </div>

      </div>
       <!--<button class="button" id="btnAddWaypoint" onclick="AddNewPoint()">Ingresar Paradas Intermedias</button>-->
   <br>
   <br> 
      <!--<label>Opciones de ruta</label>
      <input type="radio" name="travelMode" id="driving" value="DRIVING" checked onchange="calcRuta();">
      <label for="driving"><span></span> </label>
      <input type="radio" name="travelMode" id="bicycling" value="BICYCLING" onchange="calcRuta();">
      <label for="bicycling"><span></span> </label>
      <input type="radio" name="travelMode" id="transit" value="TRANSIT" onchange="calcRuta();">
      <label for="transit"><span></span> </label>
      <input type="radio" name="travelMode" id="walking" value="WALKING" onchange="calcRuta();">
      <label for="walking"><span></span> </label>
      <br>
      <input type="checkbox" id="trafico" onclick="estadotrafico();">
      <label for="trafico"><span></span> Ver estado del tráfico </label>
      <br>
      <input type="checkbox" id="autopista" onchange="calcRuta();">
      <label for="autopista"><span></span> Evitar autovías</label>
      <br>
      <input type="checkbox" id="peaje" onchange="calcRuta();">
      <label for="peaje"><span></span> Evitar peajes</label>
      <br>
      <input type="checkbox" name="optRuta" id="optWaypoints" onchange="calcRuta();">
      <label for="optWaypoints"><span></span> Optimizar etapas de ruta</label>
      <br>
      <input type="checkbox" name="nubes" id="nubes" value="nubes" onchange="meteo();">
      <label for="nubes"><span></span> Habilitar capa Nubes</label>
      <br>
      <input type="checkbox" name="temp" id="temp" value="temp" onchange="meteo();" />
      <label for="temp"><span></span> Temperaturas</label>
      <br>
      <br>
      <input type="button" class="button" onclick="reset();" value="Limpiar todo">-->
      <br>
      <br>
      <input type="button" class="button" onclick="calcRuta(); mostrar()" value="Trazar la ruta"> 
   <br>
   <br>
   </div>
   </div>
   </div>
   
   
   <div id='oculto' style='display:none; '>
   
    <!--<div style=" border:solid 1px #99CCFF; border-radius:10px; padding:5px; width:940px; margin:auto;" >
    Distancia y tiempo:<br>
    <a style="font-size:8px;">(El recorrido en el mapa es solamente sugerido)</a>
    </div>   -->
    
    <br>	
    </div> 
    <div id="dpanel"></div>
 <div id="">

        <div id="direcciones" style="width: 400px; height: 580px; padding:5px; float:left; margin:0px 0px 0px 70px; border:solid 1px border-radius:10px; overflow:scroll; "></div> 
        <div id="map-canvas" style="width: 900px; height: 580px; float:left; margin:0px 0px 0px 20px; border:solid 1px #99CCFF; border-radius:10px; "></div>
        <br>
        <br>

                
        <!--<div><img src="images/trafico.png" alt="Control tráfico" id="controltrafico" style="display:none;" /> </div>-->	
</div>
<br>
<br>
    
    


<script>

var rendererOptions = {
  draggable: false
};

var directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);;
var directionsService = new google.maps.DirectionsService();
var map;

var ba = new google.maps.LatLng(23.6345005, -102.5527878);

function initialize() {

  var mapOptions = {
    zoom: 5,
    center: ba
  };
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  directionsDisplay.setMap(map);
  directionsDisplay.setPanel(document.getElementById('directionsPanel'));

  google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
    computeTotalDistance(directionsDisplay.getDirections());
  });

  calcRoute();
 
}

//////////////////////////////////////////////////// 

// añadir y eliminar puntos intermedios
function getPoints() {
    var waypts = new Array();
    var pointChildrens = document.getElementById("AllPoints").children;
    for (var i = 0; i < pointChildrens.length; i++) {
        var pointText = pointChildrens[i].children[0].value;
        if (pointText.trim() != '') {
            waypts.push({
                location: pointText,
                stopover: true
            });
        }
    }
    return waypts;
};
var totalPoints = 0;

function AddNewPoint() {
    totalPoints = totalPoints + 1;
    var pointElem = document.getElementById('Waypoint');
    if (totalPoints == 1) {
        pointElem.style.display = 'block';
    } else {
        // btnAddWaypoint.innerHTML = "Nuevo punto";
        var divAllPoints = document.getElementById('AllPoints');
        var nextPoint = pointElem.cloneNode(true);
        // nextPoint.children[0].textContent = "Nuevo punto:";
        nextPoint.children[0].value = "";
        divAllPoints.appendChild(nextPoint);
    }
};

function DeletePoint(control) {
    totalPoints = totalPoints - 1;
    var divToDelete = control.parentNode;
    var divAllPoints = document.getElementById('AllPoints');
    if (totalPoints == 0) {
        divToDelete.style.display = 'none';
    } else {
        divAllPoints.removeChild(divToDelete);
    }
};

function calcRuta() {
    var waypts = new Array();
    var pointsArray = document.getElementsByName('points');
    for (var i = 0; i < pointsArray.length; i++) {
        if (pointsArray[i].value != '') {
            waypts.push({
                location: pointsArray[i].value,
                stopover: true
            });
        }
    }
    var modo;  // todo lo descrito en modo es para tomar la elección de modo de ruta
    /*if (document.getElementById('driving').checked) {
        modo = google.maps.DirectionsTravelMode.DRIVING;
    } else if (document.getElementById('bicycling').checked) {
        modo = google.maps.DirectionsTravelMode.BICYCLING;
    } else if (document.getElementById('transit').checked) {
        modo = google.maps.DirectionsTravelMode.TRANSIT;
    } else if (document.getElementById('walking').checked) {
        modo = google.maps.DirectionsTravelMode.WALKING;
    } else {
        alert('Escoja un modo de ruta');
    }*/
    modo = google.maps.DirectionsTravelMode.DRIVING;
    var request = {
        origin: document.getElementById("origen").value,
        destination: document.getElementById("destino").value,
        waypoints: waypts,  // aquí van los puntos intermedios generados
        travelMode: modo, // el modo de ruta elegido
        unitSystem: google.maps.UnitSystem.METRIC,  // traducirá la distancia a Kilómetros, 
        //optimizeWaypoints: $("#optWaypoints").is(":checked"),  // optimiza la ruta si está chequeado
        provideRouteAlternatives: true,  // muestra automáticamente rutas alternativas
        //avoidHighways: document.getElementById('autopista').checked,  // evita autopistas
        //avoidTolls: document.getElementById('peaje').checked  // evita peajes
    };
    directionsService.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setMap(map);
            directionsDisplay.setPanel(document.getElementById('direcciones'));
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var summaryPanel = document.getElementById('dpanel');
            summaryPanel.innerHTML = '';
            // For each route, display summary information.
            for (var y = 0; y < route.legs.length; y++) {
              var routeSegment = y + 1;
              summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment +
                  '</b><br>';
              summaryPanel.innerHTML += route.legs[y].start_address + ' to ';
              summaryPanel.innerHTML += route.legs[y].end_address + '<br>';
              summaryPanel.innerHTML += route.legs[y].distance.text + '<br><br>';
            }

        } else {
            alert("No existen Rutas validas entre ambos puntos");
        }
    });
    
}


google.maps.event.addDomListener(window, 'load', initialize);


</script>

<script type="text/javascript">
         function mostrar(){
         document.getElementById('oculto').style.display = 'block';}
</script>



</body>
</html>

