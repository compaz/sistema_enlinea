<?php 

require_once('sistema_enlinea/procesoConciliacion.php');
function generarListadoCortados(){

		$dbo = JFactory::getDBO();
		$con = $dbo->getConnection();
		
		//estatus 1, abonado, 2 exonerado
		$sql = "
		SELECT LPAD(tax_code,10,' ') as tax_code, name, ip, ip2, telefono1, telefono2, name, mac, mac2, nrocontrato,email,address1
		FROM bamboo_clients
		WHERE 
		SALDO <0
		AND STATUS =  '1'
		ORDER BY  `bamboo_clients`.`nrocontrato` ASC 
		";
		//echo $sql;
		$result = util_ejecutarSql($sql, $con);
		$total = mysql_num_rows($result);
		
		?>
<style type="text/css">
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

<table width="741" border="1" class='normal' >
  <tr class="FondoTituloCol">
    <td colspan="9"><h5>Listado de clientes Cortados</h5></td>
  </tr>
   <tr class="FondoTituloCol">
    <td colspan="9"><h5>Numero de Clientes Cortados: <?php echo $total; ?></h5></td>
  </tr>
  <tr class="FondoTituloCol">
	<td width="50" bgcolor="#333333"><h5>Gdoc</h5></td>
    <td width="35" bgcolor="#333333"><h5>cedula</h5></td>
    <td width="51" bgcolor="#333333"><h5>IP 1</h5></td>
    <td width="40" bgcolor="#333333"><h5>MAC 1</h5></td>
    <td width="90" bgcolor="#333333"><h5>Telefono 1</h5></td>
    <td width="71" bgcolor="#333333"><h5>Telefono 2</h5></td>
    <td width="63" bgcolor="#333333"><h5>Email</h5></td>
  </tr>
		<?php 
		$i=0;
		while ($row = mysql_fetch_assoc($result)) {
			$i++;		
			?>
   		  <tr bgcolor=<?php echo $i%2?"#ECEEFF":"#FFFFFF";?> >
		  <td colspan="9" class="celdaTabla"><strong>Nombre</strong>: <?php echo $row['name']."<strong> Dirección:</strong> ". $row['address1']; ?></td>
		  </tr>
			<tr  class="celdaTabla" bgcolor=<?php echo $i%2?"#ECEEFF":"#FFFFFF";?> >
	<td align="center"><?php echo $row['nrocontrato']; ?>
	<?php
    	$cedula = trim($row['tax_code']); 
    	echo "{japopup type='iframe' content='index.php?option=com_chronoforms&chronoform=consultarusuario&cedula=$cedula' width='860' height='600' group1='thickbox'}";
    	?>
    	<img src="..//sistema_enlinea//imagenes//b_search.png" alt="Ver" height="16" width="16" />
    	<?php echo "{/japopup}"; ?>
	</td>
    <td align="center" id="cedula" > <?php echo $cedula; ?></td>
    
    <td align="center" ><?php echo $row['ip']; ?></td>
    <td align="center" ><?php echo $row['mac']; ?></td>
    <td align="center" ><?php echo $row['telefono1']; ?></td>
    <td align="center" ><?php echo $row['telefono2']; ?></td>
    <td align="center" ><?php echo $row['email']; ?></td>
    
  </tr>
			<?php

		}
		?>
		<tr>
    <td colspan="9">&nbsp;Numero de Clientes Cortados: <?php echo $total; ?></td>
  </tr>
</table>
		<?php
	}
	
	
	

?>