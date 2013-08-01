<style type='text/css'>
.normal {
  width: 800px;
  border: 1px solid #050505;
  border-collapse: collapse;
}
.normal th, .normal td {
  border: 1px solid #050505;
}

.chronoform { display:none; }
</style>
<?php
//require_once('sistema_enlinea/util_lib.php');

// traducefecha.php
// 14 de Octubre de 2003
// Traduce una fecha en formato mm/dd/yyy a formato texto en castellano
// Desde la pagina llamaremos a la funcion
// include("traducefecha.php");
// echo traducefecha("11/15/2003"); Visualiza la fecha
// Donde la fecha ponemos la variable que queremos traducir en formato mm/dd/yyyy
//
function traducefecha($fecha)
    {
    $fecha= strtotime($fecha); // convierte la fecha de formato mm/dd/yyyy a marca de tiempo
    $diasemana=date("w", $fecha);// optiene el número del dia de la semana. El 0 es domingo
       switch ($diasemana)
       {
       case "0":
          $diasemana="Domingo";
          break;
       case "1":
          $diasemana="Lunes";
          break;
       case "2":
          $diasemana="Martes";
          break;
       case "3":
          $diasemana="Miércoles";
          break;
       case "4":
          $diasemana="Jueves";
          break;
       case "5":
          $diasemana="Viernes";
          break;
       case "6":
          $diasemana="Sábado";
          break;
       }
    $dia=date("d",$fecha); // día del mes en número
    $mes=date("m",$fecha); // número del mes de 01 a 12
       switch($mes)
       {
       case "01":
          $mes="Enero";
          break;
       case "02":
          $mes="Febrero";
          break;
       case "03":
          $mes="Marzo";
          break;
       case "04":
          $mes="Abril";
          break;
       case "05":
          $mes="Mayo";
          break;
       case "06":
          $mes="Junio";
          break;
       case "07":
          $mes="Julio";
          break;
       case "08":
          $mes="Agosto";
          break;
       case "09":
          $mes="Septiembre";
          break;
       case "10":
          $mes="Octubre";
          break;
       case "11":
          $mes="Noviembre";
          break;
       case "12":
          $mes="Diciembre";
          break;
       }
    $ano=date("Y",$fecha); // optenemos el año en formato 4 digitos
    $fecha= $diasemana.", ".$dia." de ".$mes." de ".$ano; // unimos el resultado en una unica cadena
    return $fecha; //enviamos la fecha al programa
    }
	
	
	
///obtener mes
function traduceMes($fecha)
    {
    $fecha= strtotime($fecha); // convierte la fecha de formato mm/dd/yyyy a marca de tiempo

    $mes=date("m",$fecha); // número del mes de 01 a 12
       switch($mes)
       {
       case "01":
          $mes="Enero";
          break;
       case "02":
          $mes="Febrero";
          break;
       case "03":
          $mes="Marzo";
          break;
       case "04":
          $mes="Abril";
          break;
       case "05":
          $mes="Mayo";
          break;
       case "06":
          $mes="Junio";
          break;
       case "07":
          $mes="Julio";
          break;
       case "08":
          $mes="Agosto";
          break;
       case "09":
          $mes="Septiembre";
          break;
       case "10":
          $mes="Octubre";
          break;
       case "11":
          $mes="Noviembre";
          break;
       case "12":
          $mes="Diciembre";
          break;
       }
    return $mes; //enviamos la fecha al programa
    }	
	 

	function ConsultarSaldoCliente(){
		
	$user = &JFactory::getUser();
	//print_r($user);

	$db =&JFactory::getDBO();
	
	$cedula =&JRequest::getString('cedula');
	$nroCon =&JRequest::getString('nro');
	$name =&JRequest::getString('name');
	$nroref =&JRequest::getString('ref');
	
	
	if(!$cedula){
		$cedula =  $HTTP_POST_VARS['cedula'];
		echo "<script>alert('cedula cargada por post')</script>";
	}
	
	
	if($nroCon)
	$str2 = " OR a.nrocontrato = '$nroCon' ";
	if($name)
	$str3 = " OR a.name LIKE '%$name%' ";
	if($nroref)
	$str4 = " OR c.cmv_pagcsv_ref = '$nroref' ";

	$sql = "SELECT 
	a.name AS nombre, 
	a.tax_code AS cedula,
	a.saldo, 
	c.cmv_pagcsv_ref AS ref, 
	c.cmv_monto * c.cmv_tipomov AS monto, 
	cmv_tipopago AS descripcion, 
	DATE_FORMAT( c.cmv_fecha, '%m/%d/%y') AS fecha,
	d.desc,
	a.address1,
	a.address2,
	a.telefono1,
	a.nodo,
	a.sector,
	a.nombrepc,
	a.ip,
	a.mac,
	a.ip2,
	a.mac2,
	a.ip3,
	a.mac3,
	a.email,
	DATE_FORMAT( a.fecha_ingreso,'%m/%d/%y'),  
	a.nrocontrato 
	FROM bamboo_clients a
	LEFT OUTER JOIN cpz_cli_mov c ON c.cmv_cli_tax_code = a.tax_code
	INNER JOIN cpz_plan d ON pln_id = a.plan
	WHERE a.tax_code =  '$cedula' ".$str2." ".$str3.
	" ".$str4." ORDER BY c.cmv_fecha DESC"; 
	
	$db->setQuery( $sql );
	$array = $db->loadRowList(); 
	//print_r($array);
	//echo $sql;
	
	if ($array[0] != NULL){
	
?>
<table width="200" border="1" class="normal">
  <tr>
    <td colspan="7" align="center" bgcolor="#CCCCCC" class="normal"><strong>Datos del cliente</strong></td>
  </tr>
  <tr>
    <td width="115"><h5 align="center">Nombre</h5></td>
    <td colspan="llego 6"><?php echo $array[0][0]; ?></td>
    <td align="center"><?php 
    			if($user->id != 0) {
    				$cedula = trim($array[0][1]);  
    				echo "<a href='index.php?option=com_chronoforms&chronoform=formClientesEdit&cedula=$cedula'><u>";
					?><img src="..//sistema_enlinea//imagenes//b_inline_edit.png" alt="Editar" height="16" width="16" /><u> Editar</u>    				
    				<?php echo "</a>";
    			}   
    		?>
    </td>
  </tr>
  <tr>
    <td ><h5 align="center">Cedula</h5></td>
    <td colspan="2"><?php echo $array[0][1]; ?></td>
    <td width="47" align="right"><h5>Plan:</h5></td>
    <td width="69" align="right"><?php echo $array[0][7]; ?></td>
    <td width="95" align="right"><h5>Cobro Mensual:</h5></td>
    <td align="center"><h2><?php echo obtenerValorDMS($db->getConnection(),$array[0][1]); ?>
    Bs</h2></td>
  </tr>
  <tr>
    <td><h1 align="center">Saldo</h1></td>
    <td colspan="2"><?php echo "<h1>".$array[0][2].",00 BsF"."</h1>(Con saldo igual o mayor a cero tendra conexión)"; ?></td>
    <td colspan="3" align="right"><h5>Estado del cliente:   </h5></td>
    <td align="center" bgcolor=<?php echo $array[0][2]>=0?"#00FF00":"#FF0000"; ?>><h1><?php echo $array[0][2]>=0?"AL DIA":"CORTADO"; ?></h1></td>
  </tr>
  <?php if ($user->id != 0) { ?>
  <tr>
    <td rowspan="2" align="center" ><h5>Direccion</h5></td>
    <td colspan="6" align="left" >
    <?php echo $array[0][8]." ".$array[0][9]; ?></td>
  </tr>
  <tr>
    <td colspan="6" align="left" >
    <?php echo " Nodo: ".$array[0][11]." Sector: ".$array[0][12]." Nombre PC: ".$array[0][13]; ?></td>
  </tr>
  
  
  <tr>
    <td align="center" ><h5>Telefonos</h5></td>
    <td colspan="2" align="left" >
		<?php echo $array[0][10]; ?>
	 </td>
    <td colspan="3" align="left" >
    	<div align="right">
      	<h5>Correo electronico:</h5>
    	</div>
    </td>
    <td align="center" >
	 	<?php echo $array[0][20]; ?>
    </td>
  </tr>
  
  <tr>
    <td align="center" ><h5>Fecha Ingreso</h5></td>
    <td colspan="5" align="left" >
	<?php echo $array[0][21]; ?></td>
    <td align="center" >&nbsp;</td>
  </tr>

  <tr>
    <td align="center"><h5>Numero de Cliente</h5></td>
    <td colspan="5" align="left">
	<?php echo $array[0][22]; ?></td>
    <td align="center" >&nbsp;</td>
  </tr>

  <tr>
    <td align="center" ><h5>[IP,MAC]</h5></td>
    <td align="left">
		<?php
			echo "IP 1 ".$array[0][14]."<br>";
			echo "IP 2 ".$array[0][16]."<br>";
			echo "IP 3 ".$array[0][18]."<br>";
		?>
	 </td>
	 <td>
	 	 <?php
			echo " MAC ".$array[0][15]."<br>";
			echo " MAC ".$array[0][17]."<br>";
			echo " MAC ".$array[0][19]."<br>";
		?>
	 </td>
  </tr>
  
  <?php } ?>
  <tr>
    <td colspan="7" align="center" bgcolor="#CCCCCC"><strong>Detalle de pagos y cobros</strong></td>
  </tr>
  <tr>
    <td align="center">Nº</td>
    <td align="center" width="249"><strong>Referencia</strong></td>
    <td align="center" width="49"><strong>Monto</strong></td>
    <td colspan="3" align="center"><strong>Descripción</strong></td>
    <td align="center" width="136" ><strong>Fecha</strong></td>
  </tr>
  <?php // while($row){
	  $i=1;
	  foreach ($array as &$row) {
	   ?>
  <tr bgcolor=<?php echo $i%2?"#EEEEEE":"#FFFFFF"; ?>>
  	<td align="center"  ><?php echo $i; ?></td>
    <td><strong><?php echo $row[3] == ""?"-------------":$row[3]." **"; ?></strong></td>
    <td align="right"><strong><?php echo $row[4]; ?></strong></td>
    <td colspan="3" align="center"><?php 
	
	if($row[5] == "DMS")
		echo "Cobro Mensual del Servicio, mes ".traduceMes($row[6]); 
	else if($row[5] == "DP")
		echo "Deposito Bancario."; 
	else if($row[5] == "PTB")
		echo "Deposito desde Otro Banco."; 
	else if($row[5] == "RMS")
		echo "Ajuste de Saldo a su favor.";
	else if($row[5] == "EXO")
		echo "Monto de Exonerado.";
	else if($row[5] == "ACS")
		echo "Ajuste en cobro del servicio.";
	else if($row[5] == "CDL")
		echo "Cambio de Latiguillo.";
	else if($row[5] == "CDL")
		echo "Cambio de Latiguillo.";
	else if($row[5] == "CDE")
		echo "Cambio de Equipo CPE.";	
	else if($row[5] == "CDA")
		echo "Cambio de Antena.";	
	else if($row[5] == "INS")
		echo "Instalacion del Cliente.";		
	else if($row[5] == "AFL")
		echo "Afiliacion del Cliente.";			
	else if($row[5] == "RDE")
		echo "Reparacion de Enlace Cliente-APN.";			
	else echo $row[5];
	
	?></td>
    <td align="center"><?php echo traducefecha($row[6]); ?></td>
  </tr>
  <tr>
  <?php /// $row = $db->loadRow(); 
  $i++;
  }
  }
   ?>
    <td colspan="7"><a href="www.compaz.com.ve">Compaz Comunicaciones.</a> Para reclamos o dudas escribanos a contacto@compaz.com.ve</td>
  </tr>
  <tr>
    <td colspan="7">** Referencias bancarias reportadas por el cliente, para reportar otro pago haga <a href="http://www.compaz.com.ve/index.php?option=com_chronoforms&amp;chronoform=sdf&amp;tmpl=component">click aquí</a></td>
  </tr>
</table>

<?php 
/*
}else{
 		echo "<script>alert('ERROR: CEDULA INVALIDA')</script>";
		$form->validation_errros['cedula'] = "ESTA CEDULA NO EXISTE, POR FAVOR REVISAR CEDULA 	VALIDA";
	return false;
}*/

	}
	
	//ConsultarSaldoCliente();
?>