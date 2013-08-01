<?php

	//echo "<script>alert('reporte')</script>";
	require_once("excel.php"); 
	require_once("excel-ext.php");
	
	
	function generarGdoc() {
	
		//$dbo = JFactory::getDBO();
		//$con = $dbo->getConnection();
		$sql = "SELECT nrocontrato as 'Contrato', name as 'Nombre y Apellido', address1 as 'Direccion' ,
		fecha_ingreso as 'Fecha Ingreso', tax_code as 'Cedula', telefono1 as Telefono, email as Email, MAC, IP, mac2 as 'Mac 2',
		ip2 as  'IP 2', STATUS as 'Estatus', plan as 'Plan', saldo as 'Saldo'
				FROM  `bamboo_clients` 
				WHERE STATUS IN (
				'1',  '2'
		)
		ORDER BY nrocontrato ASC , 
		STATUS ASC";
	
		$con =  mysql_connect('localhost', 'cocompaz_usr1', '1234qwer');
		if (!$con) {
			die('No pudo conectarse: ' . mysql_error());
		}else{
			 mysql_select_db('cocompaz_jml1',$con);
		}
		//echo 'Conectado  satisfactoriamente';
		$result = mysql_query($sql, $con) or die(mysql_error());
		$total_clientes = mysql_num_rows($result);

		while($datatmp = mysql_fetch_assoc($result)) { 
			$data[] = $datatmp; 
		}  
		createExcel("Clientes_Compaz.xls", $data);
		//exit;
		echo "reporte porpantalla inicio";	
			error_reporting(E_ALL ^ E_NOTICE);

	}
	
	generarGdoc();
	echo "";
	reporteEnPantalla();
	
	
?>

<?php
	function reporteEnPantalla(){ 
		require_once 'excel_reader2.php';
		$data = new Spreadsheet_Excel_Reader("xlsfile://tmp/"."Clientes_Compaz.xls");
		echo "reporte porpantalla salida:";
?>
<html>
<head>
<style>
table.excel {
	border-style:ridge;
	border-width:1;
	border-collapse:collapse;
	font-family:sans-serif;
	font-size:12px;
}
table.excel thead th, table.excel tbody th {
	background:#CCCCCC;
	border-style:ridge;
	border-width:1;
	text-align: center;
	vertical-align:bottom;
}
table.excel tbody th {
	text-align:center;
	width:20px;
}
table.excel tbody td {
	vertical-align:bottom;
}
table.excel tbody td {
    padding: 0 3px;
	border: 1px solid #EEEEEE;
}
</style>
</head>
<body>
<?php echo $data->dump($row_numbers=false,$col_letters=false,$sheet=0,$table_class='excel'); ?>
</body>
</html>

<?php } ?>