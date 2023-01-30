<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		$tpl = new TemplatePower("../../interfaz/HTML/contrasena_usr.html");
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
			<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="trabajarDocumento.php">Regresar</a>');
		}*/
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
		}
		else{
            $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
		}
		
		if(isset($_SESSION["TEMP_NO_IGUAL_PASSWD"]) and $_SESSION["TEMP_NO_IGUAL_PASSWD"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/>");
			$tpl->assign("MENSAJE","ERROR: NO COINCIDE LA CONFIRMACIÓN");
			unset($_SESSION["TEMP_NO_IGUAL_PASSWD"]);
		}		
		if(isset($_SESSION["TEMP_NO_PASSWD_ANTERIOR"]) and $_SESSION["TEMP_NO_PASSWD_ANTERIOR"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/>");
			$tpl->assign("MENSAJE","ERROR: CONTRASEÑA ANTERIOR NO COINCIDE");
			unset($_SESSION["TEMP_NO_PASSWD_ANTERIOR"]);
		}
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario
		$contrasenaUsuario = htmlentities(trim($_POST["password_anterior"]));
		$nuevaContrasena = htmlentities(trim($_POST["password_nuevo"]));
		$nuevaContrasena2 = htmlentities(trim($_POST["password_nuevo2"]));
		
		$contrasenaUsuario = mysqli_real_escape_string($conexion_db, $contrasenaUsuario);
		$nuevaContrasena = mysqli_real_escape_string($conexion_db, $nuevaContrasena);
		$nuevaContrasena2 = mysqli_real_escape_string($conexion_db, $nuevaContrasena2);
        
        //Verificamos la contraseña anterior		
		if(strcmp($contrasenaUsuario,$_SESSION["CONTRASENA"]) == 0){
			//Verificamos que contraseña1 sea igual a contraseña2
			if(strcmp($nuevaContrasena,$nuevaContrasena2) == 0){				
				//Actualizamos la contraseña ya cifrada
				$consulta = "update usuario set contrasena_usuario = '".password_hash($nuevaContrasena,PASSWORD_BCRYPT)."' where nombre_usuario = '".
				$_SESSION["USUARIO"]."'";
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();					
				$resultado->close();
				$conexion_db->close();
				
				//Se redirecciona a salir				
				header("Location: salir.php");
			}
			else{
				$_SESSION["TEMP_NO_IGUAL_PASSWD"] = "SI";
				header("Location: contrasena_usr.php");
			}
		}
		else{
			$_SESSION["TEMP_NO_PASSWD_ANTERIOR"] = "SI";
			header("Location: contrasena_usr.php");
		}
	}
?>