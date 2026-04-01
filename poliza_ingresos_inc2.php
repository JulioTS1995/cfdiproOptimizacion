<?php
				//========================================================
				// Genera lineas de IVA Provision(Cargo y abono)
				$iva_provImporte = ($rowabonossub["Importe"] * ($rowabonossub["xiva"] / 100));
				GenLineaPolizaMovimiento($archivotxt, $iva_provCarCta, $rowabonossub["factura"], "1", $iva_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $iva_provAboCta, $rowabonossub["factura"], "2", $iva_provImporte);

				//========================================================
				// Genera lineas de Retencion de IVA Provision(Cargo y abono)
				$ivaret_provImporte = ($rowabonossub["Importe"] * ($rowabonossub["xretencion"] / 100));
				GenLineaPolizaMovimiento($archivotxt, $ivaret_provCarCta, $rowabonossub["factura"], "1", $ivaret_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $ivaret_provAboCta, $rowabonossub["factura"], "2", $ivaret_provImporte);

				//========================================================
				// Genera lineas de Flete Provision(Cargo y abono)
				$flete_provImporte = ($rowabonossub["Importe"] - ($iva_provImporte + $ivaret_provImporte));
				GenLineaPolizaMovimiento($archivotxt, $flete_provCarCta, $rowabonossub["factura"], "1", $flete_provImporte);
				GenLineaPolizaMovimiento($archivotxt, $flete_provAboCta, $rowabonossub["factura"], "2", $flete_provImporte);
?>