<style type='text/css'>
.normal {
  width: 800px;
  border: 1px solid #050505;
  border-collapse: collapse;
}
.normal th, .normal td {
  border: 1px solid #050505;
}
</style>
<?php
//proceso carga csv

function procesarCarga(){
jimport( 'joomla.database.database.mysql' );
$csvsofitasa = JRequest::getVar( 'csvsofitasa', null, 'files', 'array' );

	if (($handle = fopen($csvsofitasa['tmp_name'], "r")) !== FALSE) {
		
		$name = $csvsofitasa['name'];
		$size = $csvsofitasa['size'];
		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		//recorrer cabecera de archivo csv sofitasa
		//lineas con informacion al principio del archivo
		$ano = date("Y");
		for($i = 0; $i < 5 ; $i++){
			
			if($i == 3){
				$obj = fgetcsv($handle, 1000, ";");
				$str = $obj[0];
				$x = substr($str, 17, 21);
				if(substr($str,18,3) == 'Ene')
				$mes = "01";
				else if(substr($str,18,3) == 'Feb')
				$mes = "02";
				else if(substr($str,18,3) == 'Mar')
				$mes = "03";
				else if(substr($str,18,3) == 'Abr')
				$mes = "04";
				else if(substr($str,18,3) == 'May')
				$mes = "05";
				else if(substr($str,18,3) == 'Jun')
				$mes = "06";
				else if(substr($str,18,3) == 'Jul')
				$mes = "07";
				else if(substr($str,18,3) == 'Ago')
				$mes = "08";
				else if(substr($str,18,3) == 'Sep')
				$mes = "09";
				else if(substr($str,18,3) == 'Oct')
				$mes = "10";
				else if(substr($str,18,3) == 'Nov')
				$mes = "11";
				else 
				$mes = "12";
			}else{
				fgetcsv($handle, 1000, ";");
			}
		}
?>
	<table width="317" border="1" class='normal' >
	  <tr>
		<td colspan="7"><h5>Mes: <?php echo $x." Fecha de sistema: ".date("F j, Y, g:i a"); ?> </h5></td>
	  <tr>
		<td colspan="7"><h5>Nro. de Cuenta: 137-0020-62-0001574311 </h5></td>
	  </tr>
	  <tr>
		<td width="36">CSV LN</td>
		<td width="36">Fecha</td>
		<td width="16">Referencia</td>
		<td width="20">Operacion</td>
		<td width="22" align="center">Debito</td>
		<td width="34" align="center">Credito</td>
		<td width="32" align="center">Saldo</td>
	  </tr>
<?php
		$i =0;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
		{
			$num = count($data);
			$data[3] = number_format((double)$data[3],2,'.','');
			$data[4] = number_format((double)$data[4],2,'.','');
			$data[1] = str_pad($data[1], 14, "0", STR_PAD_LEFT);
			$fecha = $ano."-".$mes."-".$data[0];
			
			$sql = "INSERT INTO `cpz_pagoscsv` (`referencia`, `dia`,
			`oper`, `debi`, `cred`, `saldo`, `fecha`, `observacion`, 
			`bnco`,`cpz_cedula`,fechamov) VALUES('$data[1]', '$data[0]', 
			'$data[2]', $data[3], $data[4], '$data[5]',CURRENT_TIMESTAMP, 
			'$x', '0137','0000000','$fecha');";

			if(strlen($data[0])> 1){
				mysql_query($sql, $con);
				$i++;
?>	
			  <tr>
				<td><?php echo $i; ?></td>
				<td><?php echo $fecha; ?></td>
				<td><?php echo $data[1]; ?></td>
				<td><?php echo $data[2]; ?></td>
				<td align="right"><?php echo $data[3]; ?></td>
				<td align="right"><?php echo $data[4]; ?></td>
				<td align="right"><?php echo $data[5]; ?></td>
			  </tr>		
<?php
			}
		}
		echo "</table>";
	}
	fclose($handle);

	include('sistema_enlinea/procesoConciliacion.php');
	actualizarSaldo("nodebug");
	procesarCobroMensual();
	generarListadoIPsValidas(true);
	
}
?>