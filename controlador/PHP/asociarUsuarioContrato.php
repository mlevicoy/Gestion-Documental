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
				
		$tpl = new TemplatePower("../../interfaz/HTML/asociarUsuarioContrato.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");

		if(isset($_SESSION["TEMP_CORRECTA_ASOCIACION"]) and $_SESSION["TEMP_CORRECTA_ASOCIACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ASOCIADO CORRECTAMENTE");
			unset($_SESSION["TEMP_CORRECTA_ASOCIACION"]);
		}
		if(isset($_SESSION["TEMP_INCORRECTA_ASOCIACION"]) and $_SESSION["TEMP_INCORRECTA_ASOCIACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: YA ESTA ASOCIADO");
			unset($_SESSION["TEMP_INCORRECTA_ASOCIACION"]);
		}
		if(isset($_SESSION["TEMP_CORRECTA_DESASOCIACION"]) and $_SESSION["TEMP_CORRECTA_DESASOCIACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","DESASOCIACIÓN CORRECTA");
			unset($_SESSION["TEMP_CORRECTA_DESASOCIACION"]);
		}
		if(isset($_SESSION["TEMP_INCORRECTA_DESASOCIACION"]) and $_SESSION["TEMP_INCORRECTA_DESASOCIACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: USUARIO NO ASOCIADO AL CONTRATO");
			unset($_SESSION["TEMP_INCORRECTA_DESASOCIACION"]);
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
				
		//Obtenemos los usuario
		$tpl->gotoBlock("_ROOT");
		$consulta3 = "select usuario.codigo_usuario, usuario.nombre_usuario, datosUsuario.nombre_datosUsuario, datosUsuario.apellidos_datosUsuario ".
		"from usuario inner join datosUsuario on datosUsuario.codigo_datosUsuario = usuario.codigo_usuario";
		$resultado3 = $conexion_db->prepare($consulta3);
		$resultado3->execute();
		$resultado3->store_result();
		$resultado3->bind_result($codigoUsuario, $nombreUsuario, $nombreRealUsuario, $apellidoUsuario);
		while($resultado3->fetch()){
			$tpl->newBlock("NOMBRE_USUARIO");
			$tpl->assign("CODIGO_USUARIO",$codigoUsuario);
			$tpl->assign("INFORMACION_USUARIO",$nombreRealUsuario." ".$apellidoUsuario." (".$nombreUsuario.")");			
		}		
		
		//Obtenemos los contratos
		$tpl->gotoBlock("_ROOT");
		$consulta4 = "select id_contrato, nombreCorto_contrato from contrato";
		$resultado4 = $conexion_db->prepare($consulta4);
		$resultado4->execute();
		$resultado4->bind_result($id_contrato, $nombreCorto_contrato);
		while($resultado4->fetch()){
			$tpl->newBlock("NOMBRE_CONTRATO");
			$tpl->assign("CODIGO_CONTRATO",$id_contrato);
			$tpl->assign("INFORMACION_CONTRATO", $nombreCorto_contrato);
		}
		
		//Cerramos la conexión
		$resultado->close();
		$resultado2->close();		
		$resultado3->close();
		$resultado4->close();
		$conexion_db->close();
		//Se muestra la interfaz
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario
		$boton = $_POST["cambiar"];		
		$codigoUsuario = $_POST["nombreUsuario"]; 
		$codigoContrato = $_POST["nombreContrato"];
		//Banderas de control
		$control = 0;
			
		if(strcmp($boton, "ASOCIAR") == 0){
			//Se realiza la asociación		
			for($i=0;$i<count($codigoContrato);$i++){
				$consulta = "select * from usuarioContrato where contrato_usuarioContrato = ".$codigoContrato[$i]." and usuario_usuarioContrato = ".
				$codigoUsuario;
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				if($resultado->fetch() == 0){
					$consulta2 = "insert into usuarioContrato (contrato_usuarioContrato, usuario_usuarioContrato) values (".
					$codigoContrato[$i].", ".$codigoUsuario.")";
					$resultado2 = $conexion_db->prepare($consulta2);
					$resultado2->execute();			
				}
				else{
					$control++;
				}	
			}			
			//Se cierran las conexiones
			$resultado->close();				
			//Se genera los mensajes
			if($control == count($codigoContrato)){
				//Cerramos la conexión				
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_INCORRECTA_ASOCIACION"] = "SI";
				header("Location: asociarUsuarioContrato.php");
			}
			else{
				//Cerramos la conexion
				$resultado2->close();
				$conexion_db->close();
				//redireccionamos
				$_SESSION["TEMP_CORRECTA_ASOCIACION"] = "SI";
				header("Location: asociarUsuarioContrato.php");
			}		
		}
		else{
			//Validamos control
			$control = 0;
			//Se realiza la Desasociación		
			for($i=0;$i<count($codigoContrato);$i++){
				$consulta = "delete from usuarioContrato where contrato_usuarioContrato = ".$codigoContrato[$i]." and usuario_usuarioContrato = ".
				$codigoUsuario;
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();				
				if($resultado->affected_rows != 0){
					$control++;
				}	
			}
			//Se cierran las conexiones
			$resultado->close();				
			//Se genera los mensajes
			if($control == 0){
				//Cerramos la conexión				
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_INCORRECTA_DESASOCIACION"] = "SI";
				header("Location: asociarUsuarioContrato.php");
			}
			else{
				//Cerramos la conexion				
				$conexion_db->close();
				//redireccionamos
				$_SESSION["TEMP_CORRECTA_DESASOCIACION"] = "SI";
				header("Location: asociarUsuarioContrato.php");
			}			
		}
	}
?>