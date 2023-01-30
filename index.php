<?PHP
	//Permite usar las planillas templatePower para separar el código PHP y HTML
	require_once("controlador/TemplatePower/class.TemplatePower.inc.php");
	//Permite realizar la conexión a las base de datos MYSQL
	require_once("controlador/PHP/conexion.php");
	
	//Inicio sesiones
	session_start();
	
	//Consulto si ya hay un usuario conectado
	if(isset($_SESSION["CONECTADO"]) and strcmp($_SESSION["CONECTADO"],"SI") == 0){
		if(isset($_SESSION["TIPOCUENTA"]) and strcmp($_SESSION["TIPOCUENTA"],"Administrador") == 0){
			header("Location: controlador/PHP/administrador.php");
		}
		else if(isset($_SESSION["TIPOCUENTA"]) and strcmp($_SESSION["TIPOCUENTA"],"Administrador") != 0){
			header("Location: controlador/PHP/sistema.php");
		}
		else{
			header("Location: controlador/PHP/salir.php");
		}
	}
	else{			
		//Carga inicial
		if(!isset($_POST["cargador"])){											//Verifica si cargador no existe		
			$tpl = new TemplatePower("interfaz/HTML/login.html");				//Indico la página a cargar
			$tpl->prepare();													//Preparo la página
			$tpl->assign("DISPLAY_MENSAJE","none");
			$tpl->assign("SALTO_LINEA","");
            
            //Obtenemos todos los contratos
            $consulta = "select id_contrato, nombreCorto_contrato from contrato where habilitado_contrato = 1";
            $resultado = $conexion_db->prepare($consulta);
            $resultado->execute();
            $resultado->store_result();
            $resultado->bind_result($id_contrato, $nombreCorto_contrato);            
            while($resultado->fetch()){
                $tpl->newBlock("CONTRATOS");
                $tpl->assign("IDCONTRATO",$id_contrato);
                $tpl->assign("NOMBRECONTRATO",$nombreCorto_contrato);
            }
            $tpl->gotoBlock("_ROOT");
            	
			if(isset($_SESSION["TEMP_ERROR_NOMBRE"]) and $_SESSION["TEMP_ERROR_NOMBRE"] == "SI"){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","¡Usuario Incorrecto, Intente de Nuevo!");
				unset($_SESSION["TEMP_ERROR_NOMBRE"]);
			}
			if(isset($_SESSION["TEMP_ERROR_PASSWORD"]) and $_SESSION["TEMP_ERROR_PASSWORD"] == "SI"){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","¡Contraseña Incorrecta, Intente de Nuevo!");
				unset($_SESSION["TEMP_ERROR_PASSWORD"]);
			}
			if(isset($_SESSION["TEMP_ERROR_HABILITADO"]) and $_SESSION["TEMP_ERROR_HABILITADO"] == "SI"){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","¡Usuario Deshabilitado!");
				unset($_SESSION["TEMP_ERROR_HABILITADO"]);
			}
			if(isset($_SESSION["TEMP_ERROR_CONECTADO"]) and $_SESSION["TEMP_ERROR_CONECTADO"] == "SI"){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE",'Sesión ya Iniciada, Pulsar <a href="controlador/PHP/salir.php">"AQUI"</a>');
				unset($_SESSION["TEMP_ERROR_CONECTADO"]);
			}
            if(isset($_SESSION["TEMP_ERROR_CONTRATO"]) and $_SESSION["TEMP_ERROR_CONTRATO"] == "SI"){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE",'¡Contrato no Asociado!');
				unset($_SESSION["TEMP_ERROR_CONTRATO"]);
			}
            $resultado->close();
            $conexion_db->close();
			$tpl->printToScreen();												//Muestro la página						
		}
		//Validación de datos
		else{
			//Rescatamos las variables
			$nombreUsuario = htmlentities(mb_strtolower(trim($_POST["nombreUsuario"]),'UTF-8'));
			$contrasenaUsuario = htmlentities(trim($_POST["contrasena"]));
            $idContrato = $_POST["campoContrato"];
			$nombreUsuario = mysqli_real_escape_string($conexion_db, $nombreUsuario);
			$contrasenaUsuario = mysqli_real_escape_string($conexion_db, $contrasenaUsuario);			
			
			//Sacamos la información de la base de datos		
			$consulta = "select codigo_usuario, nombre_usuario, contrasena_usuario, cuenta_usuario, nuevo_usuario, conectado_usuario, habilitado_usuario from usuario ".
			"where nombre_usuario='".$nombreUsuario."'";
			$resultado = $conexion_db->prepare($consulta);//Preparamos la consulta
			//Realizamos la consulta
			$resultado->execute();		
			//Guardamos el resultado	
			$resultado->store_result();
			
			//Consultamos si el nombre de usuario existe o no			
			if($resultado->num_rows == 0){
				$resultado->close(); 
				$conexion_db->close();
				$_SESSION["TEMP_ERROR_NOMBRE"] = "SI";
				header('Location: index.php');
			}
			else{			
				//Vinculamos las variables
				$resultado->bind_result($codigo_usuario, $nombre_usuario, $contrasena_usuario, $cuenta_usuario, $nuevo_usuario, $conectado_usuario, $habilitado_usuario);
				//Sacamos la información
				$resultado->fetch();
                               
				//Verificamos el password				
				if(!password_verify($contrasenaUsuario, $contrasena_usuario)){
					$resultado->close(); 
					$conexion_db->close();
					$_SESSION["TEMP_ERROR_PASSWORD"] = "SI";
					header('Location: index.php');
				}
				else{
				    //Verificamos si el usuario esta asociado al contrato
                    $consulta5 = "select count(*) as ctdadContrato from usuarioContrato where usuario_usuarioContrato = ".$codigo_usuario.
                    " and contrato_usuarioContrato = ".$idContrato;
                    $resultado5 = $conexion_db->prepare($consulta5);
                    $resultado5->execute();
                    $resultado5->bind_result($ctdadContrato);
                    $resultado5->fetch();
                    if($ctdadContrato == 0){
                       $resultado->close(); 
                       $resultado5->close(); 
					   $conexion_db->close();
					   $_SESSION["TEMP_ERROR_CONTRATO"] = "SI";
					   header('Location: index.php');
                    }
                    else{
                        $resultado5->close();
                        //Obtenemos el nombre del contrato
                        if($idContrato != 0){
                            $_SESSION["CODIGOCONTRATO"] = $idContrato;                        
                            $consulta5 = "select nombreCorto_contrato from contrato where id_contrato = ".$idContrato;
                            $resultado5 = $conexion_db->prepare($consulta5);
                            $resultado5->execute();
                            $resultado5->bind_result($nombreCorto_contrato);
                            $resultado5->fetch();
                            $_SESSION["NOMBRECONTRATO"] = $nombreCorto_contrato;
                            $resultado5->close();
                        }
                        //Verificamos si es un usuario nuevo
                        if($nuevo_usuario == 1){
					   	   $resultado->close(); 
    					   $conexion_db->close();
                           $_SESSION["VENGO"] = "index.php"; 
                           $_SESSION["CODIGOUSUARIO"] = $codigo_usuario;						
                           $_SESSION["CONTRASENA"] = $contrasena_usuario;
                           header("location: controlador/php/preguntas.php");
					   }
					   //Verificamos si el usuario esta habilitado
					   else if($habilitado_usuario == 0){
					       $resultado->close(); 
						   $conexion_db->close();
						   $_SESSION["TEMP_ERROR_HABILITADO"] = "SI";
						   header('Location: index.php');
					   }
                       //Verificamos si el usuario esta conectado en otro computador
					   else if(!isset($_SESSION["CONECTADO"]) and $conectado_usuario == 1){
						   $resultado->close(); 
						   $conexion_db->close();
						   $_SESSION["USUARIO"] = $nombre_usuario;
						   $_SESSION["TEMP_ERROR_CONECTADO"] = "SI";
						   header('Location: index.php');					
					   }
                       else{
						   //Liberamos resultado
						   $resultado->close(); 
						   //Obtenemos variables de session
						   $_SESSION["CONECTADO"] = "SI";
						   $_SESSION["ULTIMO_ACCESO"] = date("Y-n-j H:i:s");						
						   $_SESSION["IDENTIFICADOR_USUARIO"] = $codigo_usuario;
						   $_SESSION["USUARIO"] = $nombre_usuario;
						   $_SESSION["CONTRASENA"] = $contrasenaUsuario;
						   //Obtenemos datos personales
						   $consulta2 = "select nombre_datosUsuario, apellidos_datosUsuario, cargo_datosUsuario, correo_datosUsuario from datosUsuario where ".
						   "codigo_datosUsuario = ".$codigo_usuario;
						   $resultado2 = $conexion_db->prepare($consulta2);
						   $resultado2->execute();
						   $resultado2->store_result();
						   $resultado2->bind_result($nombre_datosUsuario, $apellidos_datosUsuario, $cargo_datosUsuario, $correo_datosUsuario);
						   $resultado2->fetch();
						   //Obtenemos más variables de session					
						   $_SESSION["NOMBRE"] = $nombre_datosUsuario;
						   $_SESSION["APELLIDO"] = $apellidos_datosUsuario;
						   $_SESSION["CARGO"] = $cargo_datosUsuario;
						   $_SESSION["CORREO"] = $correo_datosUsuario;
						   //Obtenemos el tipo de cuenta
						   $consulta3 = "select nombre_tipoCuenta from tipoCuenta where codigo_tipoCuenta = ".$cuenta_usuario;
						   $resultado3 = $conexion_db->prepare($consulta3);
						   $resultado3->execute();
						   $resultado3->store_result();
						   $resultado3->bind_result($nombre_tipoCuenta);
						   $resultado3->fetch();						
						   //Obtenemos más variables de session						
						   $_SESSION["TIPOCUENTA"] = $nombre_tipoCuenta;
						   //pasamos el usuario a conectado
						   $consulta4 = "update usuario set conectado_usuario=1 where nombre_usuario = '".$nombreUsuario."'";
						   $resultado4 = $conexion_db->prepare($consulta4);
						   $resultado4->execute();
																		
					       //Cerramos variables y conexión
					  	   if(isset($resultado1)){ $resultado->close(); }
						   if(isset($resultad2)){ $resultado2->close(); }
						   if(isset($resultad3)){ $resultado3->close(); }
						   $conexion_db->close();
						
						   //Enviamos a la página
						   if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0 and $idContrato == 0){
							   header("Location: controlador/PHP/administrador.php");
						   }
						   else{							
							   header("Location: controlador/PHP/portada.php");
						   }						
					   }
                    }
				}
			}	
		}
	}
?>