<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	if(!isset($_POST["cargador"])){				
		$tpl = new TemplatePower("../../interfaz/HTML/anadirDestinatario.html");
		$tpl->prepare();
		$tpl->assign("NOMBRECONTRATO",$_SESSION["NOMBRECONTRATO"]);
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		/*if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="subirDocumento.php">Regresar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="subirDocumento.php">Regresar</a>');
		}*/
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
		}
		else{
            $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
		}
		
		if(isset($_SESSION["ERROR_DESTINATARIO"]) and $_SESSION["ERROR_DESTINATARIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");				
			$tpl->assign("MENSAJE","ERROR: EL DESTINATARIO YA EXISTE");
			unset($_SESSION["ERROR_DESTINATARIO"]);
		}		
								
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario		
		//$nombre = htmlentities(mb_strtoupper(trim($_POST["nombre"]),'UTF-8')); 
		//$apellido = htmlentities(mb_strtoupper(trim($_POST["apellido"]),'UTF-8')); 
		$cargo = htmlentities(mb_strtoupper(trim($_POST["cargo"]),'UTF-8'));
		//$empresa = htmlentities(mb_strtoupper(trim($_POST["empresa"]),'UTF-8'));
		//Verificamos que el destinatario no exista
        $consulta = "select count(*) as cantidad_destinatario from destinatario where cargo_destinatario = '".$cargo."'";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($cantidad_destinatario);
		$resultado->fetch();
        if($cantidad_destinatario == 0){
			//Guardamos la informacion			
			$consulta2 = "insert into destinatario (id_destinatario, cargo_destinatario) values ('', '".$cargo."')";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			//Cerramos y redireccionamos
			$resultado->close();
			$resultado2->close();
			$conexion_db->close();
			header("Location: subirDocumento.php");
		}
		else{
			$resultado->close();
			$conexion_db->close();
			$_SESSION["ERROR_DESTINATARIO"] = "SI";
			header("Location: anadirDestinatario.php");
		}		
	}
?>
