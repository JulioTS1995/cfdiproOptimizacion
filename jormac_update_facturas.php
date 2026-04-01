<?php  


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>JORMAC Update Facturas</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</head>
<body>
	
	<div class="container">
		<br>
			<div class="row">
				<div class="col-12">
					<div id="2" style="text-align:center;">
						<h1 style="text-align: center;color:#0059b3; line-height: 100px;">Actualizar Facturas JORMAC</h1>
					</div>
				</div>
			</div>
		
			<div class="col-12">
				<form action="jormac_procesa_facturas.php" name="form1" method="post" enctype="application/x-www-form-urlencoded" id="form1">
				  <div class="form-group">
					<label for="xfolio"><strong>XFolio:</strong></label>
					<input type="input" class="form-control" id="xfolio" name="xfolio" placeholder="XFOLIO">
				  </div>
				  <button type="submit" class="btn btn-primary">Procesar</button>
				</form>	
			</div>
		</div>
	</div>
	
</body>
</html>

<!-- http://174.142.204.88/cfdipro/leds_update_facturas.php -->