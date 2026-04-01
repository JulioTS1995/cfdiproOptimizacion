<?php
//Inicio la transaccion

	require_once('cnx_cfdi.php');
    mysql_select_db($database_cfdi, $cnx_cfdi);
	
	$prefijodb = $_GET["prefijodb"];
	$id_factura = $_GET["id"];
	
	$sql_00="SELECT COUNT(*) as total FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura;
	$res_00=mysql_query($sql_00);
	while ($fila_0=mysql_fetch_array($res_00)){
		$v_total = $fila_0['total'];
	}
	
	if($v_total == 0){
		echo "<h2>No existen Remisiones anexadas en la factura.</h2>";
	} else {
	
	
		//Buscar Remisiones anexadas a la factura
		$sql_01="SELECT * FROM ".$prefijodb."facturasdetalle WHERE FolioSubDetalle_RID = ".$id_factura;
		$res_01=mysql_query($sql_01);
		while ($fila_exp1=mysql_fetch_array($res_01)){
			$id_remision = $fila_exp1['Remision_RID'];
			

			//Buscar FacturasSub de la Remision
			$sql_fs="SELECT * FROM ".$prefijodb."facturassub WHERE Remision = ".$id_remision;
			$res_fs=mysql_query($sql_fs);
			while ($fila_fs=mysql_fetch_array($res_fs)){
				$id_facturasub = $fila_fs['ID'];
				$descripcion_facturasub = $fila_fs['Descripcion'];
			}
			
			//Buscar RemisionesSub de la Remision
			$sql_fs1="SELECT COUNT(*) as t2 FROM ".$prefijodb."remisionessub WHERE FolioSub_RID = ".$id_remision." LIMIT 1";
			$res_fs1=mysql_query($sql_fs1);
			while ($fila_fs1=mysql_fetch_array($res_fs1)){
				$v_total2 = $fila_fs1['t2'];
			}
			
			if($v_total2 == 0){
				//No existen detalles en la RemisionSub
			} else {
				
			
				//Buscar RemisionesSub de la Remision
				$sql_rs="SELECT * FROM ".$prefijodb."remisionessub WHERE FolioSub_RID = ".$id_remision." LIMIT 1";
				$res_rs=mysql_query($sql_rs);
				while ($fila_rs=mysql_fetch_array($res_rs)){
					$rs_cantidad = $fila_rs['Cantidad'];
					$rs_embalaje = $fila_rs['Embalaje'];
					$rs_referencia = $fila_rs['Referencia'];
					$rs_pedimento = $fila_rs['Pedimento'];
					$rs_ordenCompra = $fila_rs['OrdenCompra'];
				}
				
				//Hacer Update
				$upd_s ="UPDATE ".$prefijodb."facturassub SET 
					Descripcion = '".$descripcion_facturasub." CANTIDAD:".$rs_cantidad." PRODUCTO:".$rs_embalaje." ".$rs_referencia." PEDIMENTO: ".$rs_pedimento." ORDEN DE COMPRA: ".$rs_ordenCompra."'
					WHERE ID = ".$id_facturasub;
				
				mysql_query("UPDATE ".$prefijodb."facturassub SET 
					Descripcion = '".$descripcion_facturasub." CANTIDAD:".$rs_cantidad." PRODUCTO:".$rs_embalaje." ".$rs_referencia." PEDIMENTO: ".$rs_pedimento." ORDEN DE COMPRA: ".$rs_ordenCompra."'
					WHERE ID = ".$id_facturasub);
					
				//echo $upd_s;
					
				$rs_cantidad = '';
				$rs_embalaje = '';
				$rs_referencia = '';
				$rs_pedimento = '';
				$rs_ordenCompra = '';
			}//Fin Busca RemisionSub
			
			
		
			
		} //Fin Busqueda de Remisiones anexadas a la factura seleccionada
		
		
		echo "<h2>Se realizo la actualizacion de los Detalles de Embalaje con Exito.</h2>";
	
	}// Fin valida Remisiones Anexadas
	
	

	//http://localhost/cfdipro/factura_update_embalaje.php?prefijodb=prbmefra_&id=206604

?>