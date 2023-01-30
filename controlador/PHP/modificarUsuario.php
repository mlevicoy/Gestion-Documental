<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validarAdministrador();
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		/* Cargamos la página y el menú */
		$consulta = "select distinct titulo_menu, icono_menu from menu";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();	
		$resultado->store_result();
		$resultado->bind_result($titulo_menu, $icono_menu);
				
		$tpl = new TemplatePower("../../interfaz/HTML/modificarUsuario.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);		
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		
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
		$tpl->gotoBlock("_ROOT");			
		/* Fin cargamos la página y el menú */
		
		/* Respuesta automática */
		if(isset($_SESSION["TEMP_ELIMINACION_CORRECTA"]) and $_SESSION["TEMP_ELIMINACION_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ELIMINADO CORRECTAMENTE");
			unset($_SESSION["TEMP_ELIMINACION_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ACTUALIZADO CORRECTAMENTE");
			unset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]);
		}		
		/* Fin respuesta automática */
		
		/* Redireccionamiento */
		if(!isset($_GET["id"])){			
			/* Par el disabled en el Javascript */			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 1);
			$tpl->assign("VALOR3",1);
			$tpl->assign("SELECT_INICIO","0");
			$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR USUARIO ---");
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "");			

			/* Se buscan los usuarios menos el administrador */
			$consulta3 = "SELECT usuario.`codigo_usuario`, usuario.`nombre_usuario`, datosUsuario.`nombre_datosUsuario`, ".
			"datosUsuario.`apellidos_datosUsuario` FROM usuario inner join datosUsuario on `codigo_usuario` = `codigo_datosUsuario` ".
			"where usuario.`nombre_usuario` <> 'mlevicoy' order by datosUsuario.`nombre_datosUsuario`";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->bind_result($codigo_usuario,$nombre_usuario,$nombre_propio,$apellido_propio);
			while($resultado3->fetch()){
				$tpl->newBlock("USUARIO_BUSCAR");
				$tpl->assign("CODIGO_USUARIO",$codigo_usuario);
				$tpl->assign("NOMBRE_USUARIO",$nombre_propio." ".$apellido_propio." (".$nombre_usuario.")");				
			}			
			$resultado3->close();			
		}
		else{
			//ID del usuario
			$codigo_usr = $_GET["id"];
			//Enable los controles input y select			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 0);
			$tpl->assign("VALOR3",1);			
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "--- SELECCIONAR USUARIO ---");			
			//llenamos nuevamente el select			
			$consulta4 = "SELECT usuario.`codigo_usuario`, usuario.`nombre_usuario`, datosUsuario.`nombre_datosUsuario`, ".
			"datosUsuario.`apellidos_datosUsuario` FROM usuario inner join datosUsuario on `codigo_usuario` = `codigo_datosUsuario` ".
			"where usuario.`nombre_usuario` <> 'mlevicoy' order by datosUsuario.`nombre_datosUsuario`";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			$resultado4->bind_result($codigo_usuario,$nombre_usuario,$nombre_propio,$apellido_propio);
			while($resultado4->fetch()){
				if(strcmp($codigo_usuario,$codigo_usr) != 0){
					$tpl->newBlock("USUARIO_BUSCAR");
					$tpl->assign("CODIGO_USUARIO",$codigo_usuario);
					$tpl->assign("NOMBRE_USUARIO",$nombre_propio." ".$apellido_propio." (".$nombre_usuario.")");				
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("SELECT_INICIO",$codigo_usuario);
					$tpl->assign("VALOR_SELECT_INICIO",$nombre_propio." ".$apellido_propio." (".$nombre_usuario.")");					
				}
			}
			$resultado4->close();						

			//Cruzamos las tablas para generar todas las variables de session
			$consulta5 = "select usuario.`nombre_usuario`, usuario.`contrasena_usuario`, usuario.`cuenta_usuario`, usuario.`habilitado_usuario`, ".
			"datosUsuario.`nombre_datosUsuario`, datosUsuario.`apellidos_datosUsuario`, datosUsuario.`cargo_datosUsuario`, ".
			"datosUsuario.`correo_datosUsuario` from usuario inner join datosUsuario on datosUsuario.`codigo_datosUsuario` = usuario.`codigo_usuario` ".
			"where usuario.`codigo_usuario` = ".$codigo_usr;	
					
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			$resultado5->bind_result($nombre_usuario, $contrasena_usuario, $cod_cuenta_usuario, $cod_habilitado_usuario, $nombre_datosUsuario, 
			$apellidos_datosUsuario, $cargo_datosUsuario, $correo_datosUsuario);
			$resultado5->fetch();
			
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("NOMBRE_FORMULARIO", $nombre_datosUsuario);
			$tpl->assign("APELLIDO_FORMULARIO", $apellidos_datosUsuario);
			$tpl->assign("CARGO_FORMULARIO", $cargo_datosUsuario);
			$tpl->assign("CORREO_FORMULARIO", $correo_datosUsuario);
			$tpl->assign("USERNAME_FORMULARIO", $nombre_usuario);
			$tpl->assign("CONTRASENA_FORMULARIO", $contrasena_usuario);
			$tpl->assign("PASSWORD_ORIGINAL", $contrasena_usuario);
			$tpl->assign("PRIVILEGIO2", "0");
			$tpl->assign("INFO_PRIVILEGIO2", "--- SELECCIONAR PRIVILEGIO ---");
			$tpl->assign("ESTADO3", "");
			$tpl->assign("VALOR_ESTADO3", "--- SELECCIONAR ESTADO ---");
			if($cod_habilitado_usuario == 0){
				$tpl->assign("ESTADO1", "0");
				$tpl->assign("VALOR_ESTADO1", "Deshabilitado");
				$tpl->assign("ESTADO2", "1");
				$tpl->assign("VALOR_ESTADO2", "Habilitado");
			}
			else{
				$tpl->assign("ESTADO1", "1");
				$tpl->assign("VALOR_ESTADO1", "Habilitado");
				$tpl->assign("ESTADO2", "0");
				$tpl->assign("VALOR_ESTADO2", "Deshabilitado");
			}						
			$resultado5->close();
			
			$consulta6 = "select codigo_tipoCuenta, nombre_tipoCuenta from tipoCuenta";
			$resultado6 = $conexion_db->prepare($consulta6);
			$resultado6->execute();
			$resultado6->bind_result($codigo_tipoCuenta, $nombre_tipoCuenta);
			while($resultado6->fetch()){
				if(strcmp($codigo_tipoCuenta,$cod_cuenta_usuario) != 0){
					$tpl->newBlock("BLOCK_PRIVILEGIOS");
					$tpl->assign("COD_PRIVILEGIO",$codigo_tipoCuenta);
					$tpl->assign("NOMBRE_PRIVILEGIO",$nombre_tipoCuenta);								
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("PRIVILEGIO1", $codigo_tipoCuenta);
					$tpl->assign("INFO_PRIVILEGIO1", $nombre_tipoCuenta);
				}
			}			
			$resultado6->close();
			
			//LLenamos el select de contratos
    		$consulta7 = "select id_contrato, nombreCorto_contrato from contrato";
			$resultado7 = $conexion_db->prepare($consulta7);
			$resultado7->execute();
            $resultado7->store_result();
			$resultado7->bind_result($id_contrato, $nombreCorto_contrato);
			while($resultado7->fetch()){
				$consulta8 = "select count(*) as ctdad from usuarioContrato where contrato_usuarioContrato = ".$id_contrato." and usuario_usuarioContrato = ".
                $codigo_usr;
                $resultado8 = $conexion_db->prepare($consulta8);
                $resultado8->execute();
                $resultado8->store_result();
                $resultado8->bind_result($cantidad_usr);
                while($resultado8->fetch()){
                    if($cantidad_usr == 1){
                        $tpl->newBlock("CONTRATOS_SELECCIONADO");
				        $tpl->assign("COD_CONTRATO",$id_contrato);
				        $tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);
                    }
                    else{
                        $tpl->gotoBlock("_ROOT");
                        $tpl->newBlock("CONTRATOS_NOSELECCIONADO");
				        $tpl->assign("COD_CONTRATO2",$id_contrato);
				        $tpl->assign("NOMBRE_CONTRATO2",$nombreCorto_contrato);
                    }                    
                }	
			}			
			$resultado7->close();
            $resultado8->close();
		}
		/*FIN REDIRECCIONAMIENTO*/
		
		//CERRAMOS LOS RESULTADOS Y CONEXION
		$resultado->close();
		$resultado2->close();		
		$conexion_db->close();	
		$tpl->printToScreen();
	}
	else{
		//Boton
		$boton = $_POST["cambiar"];
		//Obtenemos el ID del usuario
		$userID = $_POST["usuarioBuscar"];
			
		//OPCIÓN ELIMINAR
		if(strcmp($boton,"ELIMINAR") == 0){			
			//Consulta para eliminar el usuario
			$consulta = "delete usuario, datosUsuario from usuario join datosUsuario on usuario.codigo_usuario = datosUsuario.codigo_datosUsuario where ".
			"usuario.codigo_usuario=".$userID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
            //Eliminamos los datos de contratoUsuario
            $consulta2 = "delete from usuarioContrato where usuario_usuarioContrato = ".$userID;
            $resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			//Eliminamos las respuestas del usuario
			$consulta3 = "delete from respuestas where idUsuario_respuesta = ".$userID;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
            //Cerramos la conexión			
			$conexion_db->close();
            //Redireccionamos
			$_SESSION["TEMP_ELIMINACION_CORRECTA"] = "SI";
			header("Location: modificarUsuario.php");
		}
		//OPCIÓN ACTUALIZAR
		else if(strcmp($boton,"ACTUALIZAR") == 0){
			//Informacion del formulario
			//Tabla datosUsuario
			$nombre = htmlentities(ucwords(mb_strtolower(trim($_POST["nombre"]),'UTF-8'))); 
			$apellido = htmlentities(ucwords(mb_strtolower(trim($_POST["apellido"]),'UTF-8'))); 
			$cargo = htmlentities(ucwords(mb_strtolower(trim($_POST["cargo"]),'UTF-8')));
			$correo = htmlentities(mb_strtolower(trim($_POST["correo"]),'UTF-8')); 			
			//Tabla usuario
			$userName = htmlentities(mb_strtolower(trim($_POST["userName"]),'UTF-8')); 
			$contrasena = htmlentities(trim($_POST["contrasena"])); 
			$contrasena_original = $_POST["contrasena_original"];			
			$privilegio = $_POST["privilegio"]; //Administrador, Usuario o Usuario avanzado
			$estado = $_POST["estado"]; //Habilitado o Deshabilitado
			$contratos = $_POST["idContrato"];
            
			//Escapamos caracteres especiales 
			$nombre = mysqli_real_escape_string($conexion_db, $nombre);
			$apellido = mysqli_real_escape_string($conexion_db, $apellido);
			$cargo = mysqli_real_escape_string($conexion_db, $cargo);
			$correo = mysqli_real_escape_string($conexion_db, $correo);
			$userName = mysqli_real_escape_string($conexion_db, $userName);
			$contrasena = mysqli_real_escape_string($conexion_db, $contrasena);			
			$contrasena_original = mysqli_real_escape_string($conexion_db, $contrasena_original);
			
			//Revisamos la contraseña
			if(strcmp($contrasena,$contrasena_original) != 0){
				$contrasena = password_hash($contrasena,PASSWORD_BCRYPT);				
			}
			//Actualizamos las tablas			
			//Usuario
			$consulta = "update usuario set nombre_usuario='".$userName."', contrasena_usuario='".$contrasena."', cuenta_usuario=".
			$privilegio.", conectado_usuario=0, habilitado_usuario=".$estado." where codigo_usuario=".$userID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			//datosUsuario
			$consulta2 = "update datosUsuario set nombre_datosUsuario='".$nombre."', apellidos_datosUsuario='".$apellido."', cargo_datosUsuario='".
			$cargo."', correo_datosUsuario='".$correo."' where codigo_datosUsuario=".$userID;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();            
            //Eliminamos la asociación al contrato
			$consulta3 = "delete from usuarioContrato where usuario_usuarioContrato = ".$userID;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();			
			//Realizamos la asociación al contrato
			for($i=0;$i<count($contratos);$i++){				
				$consulta4 = "insert into usuarioContrato (contrato_usuarioContrato, usuario_usuarioContrato) values (".$contratos[$i].", ".$userID.")";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
			}	
            //Cerramos conexion
			//$resultado->close();
			//$resultado2->close();
			$conexion_db->close();			
			//Redireccionamos
			$_SESSION["TEMP_ACTUALIZACION_CORRECTA"] = "SI";
			header("Location: modificarUsuario.php");
		}		
	}
?>