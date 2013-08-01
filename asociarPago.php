<?php

	include('procesoConciliacion.php');

	function procesarPagoAsociado( $cedula, $refSofitasa, $refOtroBanco,$monto2,$sysuser,$oper){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		
		if(!existeRef($refSofitasa,$con) || !is_null($monto2) ){
			//no esta asignada, asignarla
			//aqui se inserta en la tabla cpz_cli_mov
				if($monto2 == NULL){ //se trata de asignar pago por terceros bancos
					$sql = "SELECT 
					cred 
					FROM  cpz_pagoscsv  
					WHERE referencia like '%".$refSofitasa."%'
				";
				
				//asociar pago de terceros bancos
				$monto  = mysql_fetch_assoc(mysql_query($sql, $con)); 
				$sql = "INSERT INTO cpz_cli_mov 
					(cmv_id,cmv_cli_tax_code, cmv_pagcsv_ref,cmv_tipomov, cmv_monto, cmv_tipopago, cmv_fecha,cmv_fechareg,cmv_refex)
					VALUES(null, '".$cedula."', '".$refSofitasa."', '1', '".$monto['cred']."', '".$oper."',SYSDATE(),SYSDATE(),'".$refOtroBanco."');
				";
				//echo "<br>".$sql;
			}else{
				//asociar pagos de montos discretos
				$sql = "INSERT INTO cpz_cli_mov 
					(cmv_id,cmv_cli_tax_code, cmv_pagcsv_ref,cmv_tipomov, cmv_monto, cmv_tipopago, cmv_fecha,cmv_fechareg,cmv_refex)
					VALUES(null, '".$cedula."',NULL, '1', '".$monto2."', '".$oper."',SYSDATE(),SYSDATE(),NULL );
				";			
			}
			//echo "<br>[".$sql."]<br><br>";
			if(util_ejecutarSql($sql, $con))
				echo "<h5>Pago asociado exitosamente!</h5>";
				auditar("pago",$monto.$monto2,$cedula,$sysuser,$con);
				
		}else{
			echo "referencia en sofitasa ya fue asignada";
		}
	}
	

?>