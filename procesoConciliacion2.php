<style type='text/css'>
.normal {
  width: 800px;
  border: 1px solid #050505;
  border-collapse: collapse;
}
.normal th, .normal td {
  border: 1px solid #050505;
}

.FondoTituloCol {
	background-color: #444;
	font-family: "Courier New", Courier, monospace;
	font-size: 12px;
	color: #FFF;
	text-align: center;
}
.celdaTabla {
	font-family: "Courier New", Courier, monospace;
	font-size: 12px;
	color: #333;
	text-transform: uppercase;
}
</style>
<?php

require_once('sistema_enlinea/util_lib.php');

/*
REX //registro de exoneracion
RME //registro movimiento externo
DMS //Debito mensual por servicio
*/

	jimport('joomla.database.database.mysql');
	define('MONTO_RENTA_MENSUAL',85);
	/*	esta funcion actualiza el saldo para cada cliente, 
	*/

	/*
		metodo que carga el saldo en cada cliente, hace la conciliacion de los pagos reportados con los 
		cargados por CSV del banco
		
		*estos pagos no incluyen los depositos desde otros bancos que tienen que ser
		chequeados manualmente
	*/	
	function actualizarSaldo($param){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		/* consulta que trae el listado de pagos reportados que tambien estan en sofitasa y los carga al saldo de cada cliente
		*/
		$sql = "SELECT 
				tax_code,referencia as ref,cred,oper,fechamov
				FROM bamboo_clients b , jos_chronoforms_data_sdf_pagos a 
				INNER join cpz_pagoscsv c on cast(numdep as signed)= cast(referencia as signed)
				WHERE a.cedula = b.tax_code AND b.status = '1'
				ORDER BY c.fechamov desc
			";
			if($param && $param == "debug"){
				//echo $sql."<br>";
			}	
				
		$result = util_ejecutarSql($sql, $con);

		
		$i =0;
		$sIpline = "";
		while ($row = mysql_fetch_assoc($result)) {
			//aqui se inserta en la tabla cpz_cli_mov
			$sql = "INSERT INTO cpz_cli_mov 
				(cmv_id,cmv_cli_tax_code, cmv_pagcsv_ref,cmv_tipomov, cmv_monto, cmv_tipopago, cmv_fecha,cmv_fechareg)
				VALUES(null, '".$row['tax_code']."', '".$row['ref']."', '1', '".$row['cred']."', '".$row['oper']."','".$row['fechamov']."',SYSDATE() );
			";
			
			$res = existeRef($row['ref'],$con);
			if( $res == false){
				mysql_query($sql, $con);
				$i++;
				if(strcmp($param,"debug")==0)
					echo "<br> Se encontron un pago que coincide, cedula:".$row['tax_code']." referencia: ".$row['ref']." monto: ".$row['cred'];
			}else{
				if(strcmp($param,"debug")==0)
				{	//echo "<br> Registro ya existe = cedula: ".$row['tax_code']." ref: ".$row['ref']." monto: ".$row['cred'];
				}
			}
			
		}	
		
		//echo "<br> debug: ".strcmp($param,"debug")."  str: ".$param;
		if(strcmp($param,"debug")==0)
		if($i >0){
			echo "<br><h3>$i Registro(s) de saldo asignados</h3><br>";
		}else{
			echo "<br><h3>Todos los registros coincidentes fueron asignados.</h3><br>";
		}
		// Esto es ejecutado automáticamente al finalizar el script.
	}
	
	/*
		metodo que realiza el asiento de cobro cada vez que se ejecuta,
		la primera vez que se ejecuta en un mes crea un cobro por el monto correspondiente
		a cada cliente segun el plan que tenga
		
		las veces subsiguientes en el mismo mes no realiza mas el cobro, por mas que se ejecute.
		solo aplicara a los que aun para dicho mes no tengan su respectivo cobro asentado.
		esto descontara el saldo directamente en cada cliente
	*/
	
	function procesarCobroMensual(){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		//pasos
		
		//validar que el proceso se ejecuta a partir del dia 6 
		
		$esDiaDeCorte = validarDiaCorte($con);
		//echo "<br><h1>".$esDiaDeCorte."</h1>";
		if ($esDiaDeCorte){
			echo "<h3>".date("d-m-Y")." Es fecha de corte o posterior, aplicando movimientos de cobro mensual por servicio (DMS):</h3><br>";
		}else{
			echo "<br>".date("d-m-Y")." Aun no es dia de corte<br>";
			return 0;
		} 
		
		//1 seleccionar clientes que no tengan movimiento DMS (debito mensual por servicio) 
		//para el mes actual 
		// y que no tengan saldo negativo
		//retornar cedula y plan 
		$sql = 	"SELECT b.tax_code, c.pln_valor, b.name 
					FROM bamboo_clients AS b 
					INNER JOIN cpz_plan AS c ON b.plan = c.pln_id 
					WHERE b.status = '1' 
					AND b.tax_code 
					NOT IN (
					SELECT cmv_cli_tax_code 
					FROM cpz_cli_mov 
					WHERE cmv_tipopago = 'DMS' 
					AND MONTH( cmv_fecha ) = MONTH( CURDATE() ) 
					AND YEAR( cmv_fecha ) = YEAR( CURDATE() )
					);";
				//echo $sql;
		$result = util_ejecutarSql($sql, $con);
		
		if(($nro = mysql_num_rows($result)) == 0){
			echo "<h5>Todos los clientes tienen el cobro del servicio aplicado.</h5>";
			return 0;
		}else{
			echo "<br><h3>Insertando Debito mensual por servicio (movimientos DMSs)</h3><br>";
			echo "<h5>Encontrados ".$nro." clientes sin debito mensual por servicio para el mes y año en curso, creando movimiento DMS del mes actual.</h5>";
	
			$sIpline = "";
			while ($row = mysql_fetch_assoc($result)) {
				

				// si no existe el registro DMS para el cliente en el mes actual, crearlo	
				if(!existeRefDMS($row['tax_code'],$con)){
					//monto actual de cobro, deuda en el mes en curso del cliente 
					$valorDMS = obtenerValorDMS($con,$row['tax_code']);
					echo "<br>$$$ >> valorDMS ".$valorDMS."<br>";
					//2 crear movimiento DMS (debito mensual por servicio) para los clientes seleccionados.
					//por mas veces que se ejecute esta rutina solo se registrara una vez por cliente un movimiento DMS mensual	 
					$sql = "INSERT INTO  `cocompaz_jml1`.`cpz_cli_mov` 
						(
							`cmv_id` ,`cmv_cli_tax_code`,`cmv_pagcsv_ref` ,`cmv_tipomov` ,
							`cmv_monto`,`cmv_tipopago`,	`cmv_fecha`,`cmv_fechareg`,cmv_refex
						)VALUES (NULL,
							'".$row['tax_code']."',NULL,'-1',  
							'".$valorDMS."','DMS',
							SYSDATE(),SYSDATE(),NULL
						);";
						echo $sql;
					mysql_query($sql, $con);
					echo "<br> Creando DMS - Nombre: ".$row['name']."cedula: ".$row['tax_code']." monto: ".$valorDMS;
				}else{
					//echo "<br> Existe  DMS - Nombre: ".$row['name']."cedula: ".$row['tax_code']." monto: ".$valorDMS;
				}
			}
			//mysql_free_result($result);
		}
	}
/*
metodo que actualiza el archivo iplist.xml
para ser usado por los pfsense
*/
	function generarListadoIPsValidas($debug){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		//cargar archivo xml
		$iplist = new SimpleXMLElement('sistema_enlinea/iplist.xml', null, true);  
		
		//1 consutar clientes que tienen saldo mayor o igual a cero
		//si el cliente tiene saldo menor a cero esta en morosidad de pago. 
		//por lo tanto no puede ser habilitado.
		
		//estatus 1, abonado, 2 exonerado
		$sql = "
		SELECT LPAD(tax_code,10,' ') as tax_code, name, ip, ip2, ip3, ip4, name, mac, mac2, 
		mac3, mac4, nrocontrato,servidor 
		FROM bamboo_clients
		WHERE saldo >=0
		AND (STATUS =  '1' OR STATUS =  '2')
		ORDER BY  `bamboo_clients`.`nrocontrato` ASC 
		";
		//echo $sql;
		$result = util_ejecutarSql($sql, $con);
		$total = mysql_num_rows($result);
		
		?>
		<table border="1" class='normal' >
  <tr class="FondoTituloCol">
    <td colspan="12"><h5>Listado de clientes al dia, contenido del archivo IpList.xml</h5></td>
  </tr>
   <tr class="FondoTituloCol">
    <td colspan="12"><h5>Numero de Clientes al Dia: <?php echo $total; ?></h5></td>
  </tr>
  <tr class="FondoTituloCol">
	<td><h5>Gdoc</h5></td>
    <td><h5>Nombre</h5></td>
    <td><h5>Cedula</h5></td>
    <td><h5>IP 1</h5></td>
    <td><h5>MAC 1</h5></td>
    <td><h5>IP 2</h5></td>
    <td><h5>MAC 2</h5></td>
  </tr>
		<?php 
		$i=0;

		while ($row = mysql_fetch_assoc($result)) {
			$i++;
						
			$sIpline[$row['servidor']] .= $row['ip']." ";
			
			if ( $row['ip2']  != '' ) { 
								
				$sIpline[$row['servidor']] .= $row['ip2']." ";
			}
			if ( $row['ip3'] != ''  ) { 
				$sIpline[$row['servidor']] .= $row['ip3']." ";
			}
			if ( $row['ip4'] != ''  ) { 
				$sIpline[$row['servidor']] .= $row['ip4'];
			}
			?>
			<tr  class="celdaTabla" bgcolor=<?php echo $i%2?"#E8ECFF":"#FFFFFF";?> >
	<td align="center">
	<?php 
		echo $row['nrocontrato']; 
		$cedula = trim($row['tax_code']);     
    	echo "<a href='index.php?option=com_chronoforms&chronoform=consultarusuario&cedula=$cedula'>";
	?>
    	<img src="..//sistema_enlinea//imagenes//b_search.png" alt="Ver" height="16" width="16" />
   <?php    	
    	echo "</a> "; 
	?></td>
    <td align="left"><?php echo $row['name']; ?></td>
    <td align="center"><?php echo $cedula; ?></td>
    <td align="center"><?php echo $row['ip']; ?></td>
    <td align="center"><?php echo $row['mac']; ?></td>
    <td align="center"><?php echo $row['ip2']; ?></td>
    <td align="center"><?php echo $row['mac2']; ?></td>    
  </tr>
			<?php
		}
		?>
		<tr class="FondoTituloCol">
    <td colspan="12">&nbsp;Numero de Clientes al Dia: <?php echo $total; ?> -COMPAZ COMUNICACIONES 2012-</td>
  </tr>
</table>
		<?php
		//cargar el mismo listado de ips validas para ambos servidores,
		//configuracion inicial, deplorable pero permite pasar 
		//clientes de una red a otra sin problemas de corte.
		
		$iplist->servidor[0]->address = $sIpline[1];//quinimari
		$iplist->servidor[1]->address = $sIpline[2];//pueblonuevo
		$iplist->servidor[2]->address = $sIpline[3];//esmeraldina
		
		//guardar cambios en xml
		$iplist->asXML('sistema_enlinea/iplist.xml');
		 
		if($debug)
			print "<br><br><h2>Actualizando archivo IPlist.xml, asegurese de ejecutar actualizacion en servidores pfsense.</h2><br>".$iplist->asXML();	
		mysql_free_result($result);		
	}
	
	///
	///
	/// Actualizar saldo particular
	function actualizarSaldoCliente($tax_code){
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		/* consulta que trae el listado de pagos reportados que tambien estan en sofitasa y
		 los carga al saldo de cada cliente
		*/
		$sql = "SELECT 
				tax_code,referencia as ref,cred,oper,fechamov
				FROM bamboo_clients b , jos_chronoforms_data_sdf_pagos a 
				INNER join cpz_pagoscsv c on cast(numdep as signed)= cast(referencia as signed)
				WHERE a.cedula = b.tax_code AND b.status = '1'
				AND b.tax_code = '".$tax_code."'
			";		

		echo "<br>".$sql;
		$result = util_ejecutarSql($sql, $con);
		
		$i =0;
		$sIpline = "";
		//$num_rows = mysql_num_rows($result);

		//echo "$num_rows Rows\n";
		while ($row = mysql_fetch_assoc($result)){
			//aqui se inserta en la tabla cpz_cli_mov
			$sql = "INSERT INTO cpz_cli_mov 
				(cmv_id,cmv_cli_tax_code, cmv_pagcsv_ref,cmv_tipomov, cmv_monto, cmv_tipopago, cmv_fecha,cmv_fechareg)
				VALUES(null, '".$row['tax_code']."', '".$row['ref']."', '1', '".$row['cred']."', '".$row['oper']."','".$row['fechamov']."',SYSDATE() );
			";	
			//echo "<br>".$sql;
			$res = existeRef($row['ref'],$con);
			if( $res == false){
				mysql_query($sql, $con);
				$i++;
				//if(strcmp($param,"debug")==0)
					echo "<br> Creando registro   = cedula:".$row['tax_code']." ref: ".$row['ref']." monto: ".$row['cred'];
			}else{
				//if(strcmp($param,"debug")==0)
					echo "<br> Registro ya existe = cedula: ".$row['tax_code']." ref: ".$row['ref']." monto: ".$row['cred'];;
			}
		}			
		//echo "<br> debug: ".strcmp($param,"debug")."  str: ".$param;
		if(strcmp($param,"debug")==0)
		if($i >0){
			echo "<br><h3>$i Registro de saldo asignado</h3><br>";
		}else{
			echo "<br><h3>Pago Reportado ya esta asignado.</h3><br>";
		}
	}

//sentencia para aplicar UPDATE sobre el valor servidor segun la ip asignada
//IF( substring( ip, 1, 6 ) = '172.16', 1, IF( substring( ip, 1, 5 ) = '144.0', 2, 3 ) )
?>

