<?php
	shell_exec('clear');
	echo " INICIANDO PROCESO DE ACTUALIZACION DE CLIENTES AL DIA\n";
	require_once("config.inc");
	//abrir archivo local config.xml
	$config = parse_config();
	
	//cargar archivo remoto de ips validas
	$archivoRemoto = "http://compaz.com.ve/sistema_enlinea/iplist.xml";

	if ($xml = new SimpleXMLElement($archivoRemoto, NULL, TRUE)) {
		print_r($xml);
	} else {
		exit('\nERROR FATAL AL CARGAR ARCHIVO REMOTO http://compaz.com.ve/sistema_enlinea/iplist.xml');
	}
	
	if($xml){
		if($config['system']['hostname'] == "quinimari"){
			if ( isset($xml->servidor[0]) )
			{
				$IPs = $xml->servidor[0]->address;
			}
			echo "\nCARGANDO LISTA DE IPS PARA SERVIDOR QUINIMARI\n";
		}else if($config['system']['hostname'] == "pueblonuevo")
		{
			if ( isset($xml->servidor[1]) ){
				$IPs = $xml->servidor[1]->address;	
			}
			echo "\nCARGANDO LISTA DE IPS PARA SERVIDOR PUEBLONUEVO\n";
		}else if($config['system']['hostname'] == "esmeraldina")
		{
			if ( isset($xml->servidor[2]) ){
				$IPs = $xml->servidor[2]->address;	
			}
			echo "\nCARGANDO LISTA DE IPS PARA SERVIDOR ESMERALDINA\n";
		}
		
		eregi_replace("^(\r\n)+|^(\n)+|^(\r)+|^(\n\r)+", ' ', $IPs);  		
		echo "\n Leyendo configuracion del servidor, procesando Alias presentes\n ";
		foreach($config['aliases']['alias'] as $aliasId => $valor)
		if ( isset($valor) )
		{
			
			if ( $valor['name'] == "AlDia")
			{
				echo "\n Valor Actual: ".$valor['address'];
				echo "\n\n Valor Nuevo : ".$IPs;
				if (isset($valor['address']))
				{
					//echo "\n Actualizando lista de ips permitidas ... \n";
					$config['aliases']['alias'][$aliasId]['address'] = $IPs;
					//echo "\n ".$config['aliases']['alias'][$aliasId]['address'];
							
				}
			}
		}
		$fp = fopen("ejemplo.txt","a");
		fwrite($fp, "Nombre: $nombre \t $texto" . PHP_EOL);
		fclose($fp);	
		
		write_config();		
		echo "\n PROCESO DE ACTUALIZACION DE CLIENTES AL DIA FINALIZADO.";
	}else{
		echo "\nError en lectura de archivo remoto, se detiene la operacion.";
	}
	
	
	
?>