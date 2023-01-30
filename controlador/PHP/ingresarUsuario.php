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
				
		$tpl = new TemplatePower("../../interfaz/HTML/ingresarUsuario.html");
		$tpl->prepare();		
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		
		if(isset($_SESSION["TEMP_ERROR_USERNAME"]) and $_SESSION["TEMP_ERROR_USERNAME"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");				
			$tpl->assign("MENSAJE","ERROR: YA EXISTE EL NOMBRE DE USUARIO");
			unset($_SESSION["TEMP_ERROR_USERNAME"]);
		}
		if(isset($_SESSION["TEMP_OK_USERNAME"]) and $_SESSION["TEMP_OK_USERNAME"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");				
			$tpl->assign("MENSAJE","USUARIO AGREGADO CORRECTAMENTE");
			unset($_SESSION["TEMP_OK_USERNAME"]);
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
		
		$consulta3 = "SELECT * FROM `tipocuenta` order by `nombre_tipoCuenta`";
		$resultado3 = $conexion_db->prepare($consulta3);
		$resultado3->execute();
		$resultado3->bind_result($codigo_tipo,$nombre_tipo);
		while($resultado3->fetch()){
			$tpl->newBlock("BLOCK_PRIVILEGIOS");
			$tpl->assign("COD_PRIVILEGIO",$codigo_tipo);
			$tpl->assign("NOMBRE_PRIVILEGIO",$nombre_tipo);			
		}
		
		$consulta4 = "select id_contrato, nombreCorto_contrato from contrato";
		$resultado4 = $conexion_db->prepare($consulta4);
		$resultado4->execute();
		$resultado4->bind_result($id_contrato, $nombreCorto_contrato);
		while($resultado4->fetch()){
			$tpl->newBlock("BLOCK_CONTRATOS");
			$tpl->assign("COD_CONTRATO",$id_contrato);
			$tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);
		}
		
		$resultado->close();
		$resultado2->close();
		$resultado3->close();
		$resultado4->close();
		$conexion_db->close();	
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario
		$nombre = htmlentities(ucwords(mb_strtolower(trim($_POST["nombre"]),'UTF-8'))); 
		$apellido = htmlentities(ucwords(mb_strtolower(trim($_POST["apellido"]),'UTF-8'))); 
		$cargo = htmlentities(ucwords(mb_strtolower(trim($_POST["cargo"]),'UTF-8')));
		$correo = htmlentities(mb_strtolower(trim($_POST["correo"]),'UTF-8')); 
		$privilegio = $_POST["privilegio"];
		$userName = htmlentities(mb_strtolower(trim($_POST["userName"]),'UTF-8')); 
		$contrasena = htmlentities(trim($_POST["contrasena"]));  
		$idcontrato = $_POST["idContrato"];
		$estado = $_POST["estado"];				
		
		//Verificamos si existe el usuario
		$consulta = "SELECT * FROM `usuario` WHERE `nombre_usuario` = '".$userName."'";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		if($resultado->num_rows != 0){
			$resultado->close();
			$conexion_db->close();
			$_SESSION["TEMP_ERROR_USERNAME"] = "SI";
			header("Location: ingresarUsuario.php");
		}
		else{
			//Datos formateados para BD
			$nombre = mysqli_real_escape_string($conexion_db, $nombre);
			$apellido = mysqli_real_escape_string($conexion_db, $apellido);
			$cargo = mysqli_real_escape_string($conexion_db, $cargo);
			$correo = mysqli_real_escape_string($conexion_db, $correo);
			$userName = mysqli_real_escape_string($conexion_db, $userName);
			$contrasena = mysqli_real_escape_string($conexion_db, $contrasena);
			
			//Almacenamos la informacion en la tabla usuario
			$consulta2 = "insert into usuario (nombre_usuario, contrasena_usuario, cuenta_usuario, nuevo_usuario, conectado_usuario, habilitado_usuario) ".
			"values ('".$userName."', '".password_hash($contrasena,PASSWORD_BCRYPT)."', ".$privilegio.", 1, 0, ".$estado.")";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();			
			
			//Obtenemos el codigo del usuario
			$consulta3 = "select codigo_usuario from usuario where nombre_usuario = '".$userName."'";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($codigo_usuario);
			$resultado3->fetch();

			//Almacenamos la información en la tabla datosUsuario
			$consulta4 = "insert into datosUsuario (`codigo_datosUsuario`,`nombre_datosUsuario`,`apellidos_datosUsuario`,`cargo_datosUsuario`,".
			"`correo_datosUsuario`) values (".$codigo_usuario.",'".$nombre."','".$apellido."','".$cargo."','".$correo."')";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			
			//Eliminamos la asociación al contrato
			$consulta5 = "delete from usuarioContrato where usuario_usuarioContrato = ".$codigo_usuario;
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			//Realizamos la asociación al contrato
			for($i=0;$i<count($idcontrato);$i++){				
				$consulta6 = "insert into usuarioContrato (contrato_usuarioContrato, usuario_usuarioContrato) values (".$idcontrato[$i].", ".$codigo_usuario.")";
				$resultado6 = $conexion_db->prepare($consulta6);
				$resultado6->execute();
			}			
			//Redireccionamiento
			$resultado->close();
			//$resultado2->close();
			$resultado3->close();
			//$resultado4->close();
			$conexion_db->close();
			$_SESSION["TEMP_OK_USERNAME"] = "SI";
			header("Location: ingresarUsuario.php");
		}
	}
?>