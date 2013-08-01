<?php
include('procesoConciliacion.php');
/*
ADM debito manual //para carga de saldo 

*/

	//jimport( 'joomla.database.database.mysql' );
	define('MONTO_RENTA_MENSUAL',85);
	
	
	//actualizarSaldo();
	procesarCobroMensual();
	generarListadoIPsValidas(); 
	
?>

