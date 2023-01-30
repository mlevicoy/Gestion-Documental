<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validarAdministrador();
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		//Cargamos la página y el menú
		$consulta = "select distinct titulo_menu, icono_menu from menu";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();	
		$resultado->store_result();
		$resultado->bind_result($titulo_menu, $icono_menu);

		$tpl = new TemplatePower("../../interfaz/HTML/contrasena.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");

		if(isset($_SESSION["TEMP_NO_IGUAL_PASSWD"]) and $_SESSION["TEMP_NO_IGUAL_PASSWD"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/>");
			$tpl->assign("MENSAJE","ERROR: NO COINCIDE LA CONFIRMACIÓN");			
			unset($_SESSION["TEMP_NO_IGUAL_PASSWD"]);
		}		
		if(isset($_SESSION["TEMP_NO_PASSWD_ANTERIOR"]) and $_SESSION["TEMP_NO_PASSWD_ANTERIOR"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/>");
			$tpl->assign("MENSAJE","ERROR: LA CONTRASEÑA ANTERIOR NO COINCIDE");
			unset($_SESSION["TEMP_NO_PASSWD_ANTERIOR"]);
		}
		
		while($resultado->fetch()){		
			$tpl->newBlock("BLOCK_TITULO");
			$tpl->assign("TITULO", ucwords(strtolower($titulo_menu)));			
			$tpl->assign("PAGINA","#");	
			$tpl->assign("ICONO", $icono_menu);			
			$consulta2 = "select `subtitulo_menu`,`pagina_menu` from menu where `titulo_menu` = '".$titulo_menu."'";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->bind_result($subtitulo_menu,$pagina_menu);		
			while($resultado2->fetch()){									
				if(strcmp($subtitulo_menu,"ZERO") !== 0){
					$tpl->newBlock("BLOCK_SUBTITULO");
					$tpl->assign("SUBTITULO",$subtitulo_menu);
					$tpl->assign("PAGINA2",$pagina_menu);			
				}
				else{
					$tpl->assign("PAGINA",$pagina_menu);			
				}
			}
		}
		$resultado->close();
		$resultado2->close();
		$conexion_db->close();	
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
				header("Location: contrasena.php");
			}
		}
		else{
			$_SESSION["TEMP_NO_PASSWD_ANTERIOR"] = "SI";
			header("Location: contrasena.php");
		}
	}
?>