<?php
//2023/01/13

/*Programa que ejecuta el BAT "LiberarMIyRUN" dentro de la carpeta
cfdipro*/

//Define nombre de BAT y variable de control

$nombrebat = "LiberaMIyRUN.bat";

$control = 0;

//Ejecuta el archivo .bat

$linea = exec("C:\\xampp\\htdocs\\cfdipro\\LiberarMIyRUN.bat");

//Mensaje de confirmacion

if($linea == true){
	
	echo "Run y MI liberados, favor de cerrar esta pestaña";
	
}else{
	
	echo "No se ha podido liberar el RUN ni el MI, favor de revisar";
	
}

?>