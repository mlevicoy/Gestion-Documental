<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	/*Verificamos la si hay alguna actualizacion activa*/
	$consulta = "select actualizacion_activa from contrato where id_contrato = ".$_SESSION["CODIGOCONTRATO"];
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();
	$resultado->store_result();
	$resultado->bind_result($actualizacion_activa);
	$resultado->fetch();
	if($actualizacion_activa == 1){		
		header("Location: actualizacionFechas.php");		
	}
	
	if(!isset($_GET["OPT1"])){		
		$tpl = new TemplatePower("../../interfaz/HTML/portada.html");
		$tpl->prepare();		
		$tpl->assign("CONTRATO",$_SESSION["NOMBRECONTRATO"]);
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);		
        $tpl->assign("DISPLAY_MENU3",'none;');
		//$tpl->assign("PANTALLA_BUSCADOR","block"); 
		//$tpl->assign("CODIGO_CONTRATO", $_SESSION["CODIGOCONTRATO"]);
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU",''); }
		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }	
        if(strcmp($_SESSION["TIPOCUENTA"], "Usuario Avanzado") == 0 || strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU2",''); }
		else{ $tpl->assign("DISPLAY_MENU2",'pointer-events: none;cursor: default;'); }	
				
		$tpl->printToScreen();	
	}
?>