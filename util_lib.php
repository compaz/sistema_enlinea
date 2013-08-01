<?php 
	
	function util_ejecutarSql($sql, $con){
		$_result = mysql_query($sql, $con);
		if (!$_result) {
			$message  = 'Invalid query: ' . mysql_error() . "\n";
			$message .= 'Whole query: ' . $query;
			die($message);
		}else return $_result;
	}

	function validarDiaCorte($con){
		$sql="SELECT DAY( CURDATE( ) ) >= valor AS result
			FROM cpz_parametros
			WHERE descri =  'dia de cobro'";
		$val = mysql_fetch_assoc(mysql_query($sql,$con));
		return $val['result'];
	}
	/*
		$sql= "SELECT b.name,c.pln_valor*a.valor as valor
				FROM  cpz_parametros AS a, bamboo_clients AS b
				INNER JOIN cpz_plan AS c ON c.pln_id = b.plan
				WHERE CURDATE( ) >=  a.fecha_inicio 
				AND CURDATE( ) <= a.fecha_fin AND b.tax_code ='".$tax_code."'";
				$res =mysql_fetch_assoc(mysql_query($sql,$con));
		return $res['valor'];
	
	*/
	
	function obtenerValorDMS($con,$tax_code){
		$sql= "SELECT IF( b.saldo <0, 0, c.pln_valor * a.valor ) AS valor
				FROM cpz_parametros AS a, bamboo_clients AS b
				INNER JOIN cpz_plan AS c ON c.pln_id = b.plan
				WHERE CURDATE() >= a.fecha_inicio
				AND CURDATE() <= a.fecha_fin
				AND STATUS = '1' AND b.tax_code ='".$tax_code."'";		
		$val = mysql_fetch_assoc(mysql_query($sql,$con));
		//echo "\n#######1<br>";
		
		//echo "<br>####### ".$val['valor']."######<br>";
		return $val['valor'];
	}
	
	/* verifica si la referencia bancaria ya fue asignada*/
	function existeRef($ref,$con){
		$sql= "	SELECT cmv_id FROM `cpz_cli_mov` WHERE `cmv_pagcsv_ref` = '".$ref."';";
		$r = mysql_fetch_assoc(mysql_query($sql,$con));
		//echo "<br>r ".$r['cmv_id']." existeRef: ".$sql;
		return $r?true:false;
	}

	/* verifica si ya existe el movimiento DMS para un cliente en el mes actual*/
	function existeRefDMS($tax_code,$con){
		$sql= "	SELECT * FROM `cpz_cli_mov` 
		WHERE MONTH(cmv_fechareg) = MONTH(curdate()) 
		AND YEAR(cmv_fechareg) = YEAR(curdate()) 
		AND cmv_tipopago = 'DMS' 
		AND cmv_cli_tax_code = '".$tax_code."' ;";
		echo "<br>".$sql;
		 $r = mysql_fetch_assoc(util_ejecutarSql($sql,$con));
		return !$r?false:true;
	}
	
	
	function aplicarDebitoManual($monto,$tax_code,$oper,$sysuser){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
			$sql = "INSERT INTO cpz_cli_mov 
				(cmv_id,cmv_cli_tax_code, cmv_pagcsv_ref,cmv_tipomov, cmv_monto, cmv_tipopago, cmv_fecha,cmv_fechareg)
				VALUES(null, '".$tax_code."',NULL, '-1', '".$monto."', '".$oper."',SYSDATE(),SYSDATE() );
			";
			echo "<br><br>";
			if(mysql_query($sql, $con)){
				echo "<script>alert('Creando registro >> cedula: $tax_code monto: $monto ');</script>";
				auditar("pago",$monto,$tax_code,$sysuser,$con);
			}else{
				echo "<script>alert('Registro no creado, error inesperado en bd>> cedula: $tax_code monto: $monto ');</script>";
			}			
	}
	
	function auditar($oper,$monto,$taxcode,$sysuser,$con){
		$sql = "INSERT INTO  `cocompaz_jml1`.`cpz_auditoria` (
				`cpz_aud_id` ,
				`cpz_aud_operacion` ,
				`cpz_aud_monto` ,
				`cpz_aud_tax_code` ,
				`cpz_aud_sysuser`
				)
				VALUES (
					NULL ,  '$oper',  '$monto',  '$taxcode',  '$sysuser'
				);";
		util_ejecutarSql($sql, $con);
	}

	//funcion para generar monto de exoneracion por instalacion segun el id de plan
	//si es antes del 6, exonera el mes actual, si es despues del 6 exonera 2 meses
	function generarMontoExoneracionInstalacion($con,$idPlan){
		$sql= "SELECT c.pln_valor * a.valor * IF( DATE_FORMAT( CURDATE( ) , '%e' ) <12, 1, 2 ) AS valor
				FROM cpz_parametros AS a, cpz_plan AS c
				WHERE c.pln_id = $idPlan
				AND CURDATE( ) >= a.fecha_inicio
				AND CURDATE( ) <= a.fecha_fin 
				";
				$res =mysql_fetch_assoc(mysql_query($sql,$con));
		return $res['valor'];
	}

?>