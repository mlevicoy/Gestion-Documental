<?PHP
	//LIBRERIAS
	require_once("../TemplatePower/class.TemplatePower.inc.php");
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Inicio sesiones
	session_start();
	
	//Consulto si viene de donde tiene que venir
	if(isset($_SESSION["VENGO"]) and strcmp($_SESSION["VENGO"], "index.php") == 0){
		if(!isset($_POST["cargador"]) and !isset($_SESSION["PREGUNTAS"])){
			$tpl = new TemplatePower("../../interfaz/HTML/preguntas.html");
			$tpl->prepare();			
			$tpl->assign("DISPLAY_MENSAJE","none");
			$tpl->assign("SALTO_LINEA","");
			
			/*Respuesta automática*/
			if(isset($_SESSION["ID_REPETIDO"]) and strcmp($_SESSION["ID_REPETIDO"], "SI") == 0){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","No se pueden repetir las preguntas");
				unset($_SESSION["ID_REPETIDO"]);
			}			
			
			//Obtenemos las preguntas
			$consulta1 = "select id_pregunta, texto_pregunta from preguntas";
			$resultado1 = $conexion_db->prepare($consulta1);
			$resultado1->execute();
			$resultado1->store_result();
			$resultado1->bind_result($id_pregunta, $texto_pregunta);
			while($resultado1->fetch()){
				$tpl->newBlock("PREGUNTA1");
				$tpl->assign("ID_PREGUNTA1", $id_pregunta);
				$tpl->assign("TEXTO_PREGUNTA1", $texto_pregunta);
				$tpl->newBlock("PREGUNTA2");
				$tpl->assign("ID_PREGUNTA2", $id_pregunta);
				$tpl->assign("TEXTO_PREGUNTA2", $texto_pregunta);
				$tpl->newBlock("PREGUNTA3");
				$tpl->assign("ID_PREGUNTA3", $id_pregunta);
				$tpl->assign("TEXTO_PREGUNTA3", $texto_pregunta);
			}
			$resultado1->close();
			$conexion_db->close();			
			$tpl->printToScreen();
		}
		else if(isset($_POST["cargador"]) and !isset($_SESSION["PREGUNTAS"])){
			//Formulario
			$idPregunta1 = $_POST["pregunta1"];
			$idPregunta2 = $_POST["pregunta2"];
			$idPregunta3 = $_POST["pregunta3"];
			$rPregunta1 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta1"]),'UTF-8'))); 
			$rPregunta2 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta2"]),'UTF-8'))); 
			$rPregunta3 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta3"]),'UTF-8'))); 
							
			//Escapamos caracteres especiales 
			$rPregunta1 = mysqli_real_escape_string($conexion_db, $rPregunta1);
			$rPregunta2 = mysqli_real_escape_string($conexion_db, $rPregunta2);
			$rPregunta3 = mysqli_real_escape_string($conexion_db, $rPregunta3);
			
			//Verificamos si se repiten las preguntas
			if($idPregunta1 == $idPregunta2 || $idPregunta1 == $idPregunta3 || $idPregunta2 == $idPregunta3){
				$_SESSION["ID_REPETIDO"] = "SI";
				header("Location: preguntas.php");
			}
			else{
				//Eliminamos si existe alguna respuesta vincula al usuario
				$consulta1 = "delete from respuestas where idUsuario_respuesta = ".$_SESSION["CODIGOUSUARIO"];
				$resultado1 = $conexion_db->prepare($consulta1);
				$resultado1->execute();
				//Almacenamos las respuestas								
				$consulta2 = "insert into respuestas (idUsuario_respuesta, idPregunta_respuesta, texto_respuesta) values (".$_SESSION["CODIGOUSUARIO"].", ".$idPregunta1.", '".
				$rPregunta1."')";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$consulta3 = "insert into respuestas (idUsuario_respuesta, idPregunta_respuesta, texto_respuesta) values (".$_SESSION["CODIGOUSUARIO"].", ".$idPregunta2.", '".
				$rPregunta2."')";
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$consulta4 = "insert into respuestas (idUsuario_respuesta, idPregunta_respuesta, texto_respuesta) values (".$_SESSION["CODIGOUSUARIO"].", ".$idPregunta3.", '".
				$rPregunta3."')";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();				
				//Limpiamos la conexion
				$conexion_db->close();
				//Llamamos a cambio de contraseña
				$_SESSION["PREGUNTAS"] = "preguntas.php";
				$tpl = new TemplatePower("../../interfaz/HTML/contrasena_inicio.html");
				$tpl->prepare();			
				$tpl->assign("DISPLAY_MENSAJE","none");
				$tpl->assign("SALTO_LINEA","");
				$tpl->printToScreen();
			}		
		}		
		else if(isset($_POST["cargador"]) and isset($_SESSION["PREGUNTAS"])){			
			//Informacion del formulario
			$contrasena_anterior = htmlentities(trim($_POST["password_anterior"]));
			$contrasena_nueva = htmlentities(trim($_POST["password_nuevo"]));
			$confirmar_contrasena_nueva = htmlentities(trim($_POST["password_nuevo2"]));
			//Escapamos caracteres especiales 
			$contrasena_anterior = mysqli_real_escape_string($conexion_db, $contrasena_anterior);
			$contrasena_nueva = mysqli_real_escape_string($conexion_db, $contrasena_nueva);
			$confirmar_contrasena_nueva = mysqli_real_escape_string($conexion_db, $confirmar_contrasena_nueva);			
			//Validamos la contraseña anterior 
			if(!password_verify($contrasena_anterior,$_SESSION["CONTRASENA"])){
				$_SESSION["ERROR_PASSWORD"] = "SI";
				header('Location: preguntas.php');
			}
			else{
				//Verificamos que las dos contraseñas sean iguales
				if(strcmp($contrasena_nueva,$confirmar_contrasena_nueva) == 0){
					//Realizamos el cambio de contraseña
					$consulta1 = "update usuario set contrasena_usuario = '".password_hash($contrasena_nueva,PASSWORD_BCRYPT)."' where codigo_usuario = ".$_SESSION["CODIGOUSUARIO"];
					$resultado1 = $conexion_db->prepare($consulta1);
					$resultado1->execute();
					//Cambiamos la bandera a 0 y llamamos a cambio de contraseña
					$consulta2 = "update usuario set nuevo_usuario = 0 where codigo_usuario = ".$_SESSION["CODIGOUSUARIO"];
					$resultado2 = $conexion_db->prepare($consulta2);
					$resultado2->execute();
					//Eliminamos todas las variables de session comprometidas
					if(isset($_SESSION["CODIGOUSUARIO"])){
						unset($_SESSION["CODIGOUSUARIO"]);
					}
					if(isset($_SESSION["ERROR_PASSWORD"])){
						unset($_SESSION["ERROR_PASSWORD"]);
					}
					if(isset($_SESSION["CONTRASENA"])){
						unset($_SESSION["CONTRASENA"]);
					}
					if(isset($_SESSION["VENGO"])){
						unset($_SESSION["VENGO"]);
					}
					if(isset($_SESSION["ID_REPETIDO"])){
						unset($_SESSION["ID_REPETIDO"]);
					}
					if(isset($_SESSION["ERROR_NUEVA_PASSWORD"])){
						unset($_SESSION["ERROR_NUEVA_PASSWORD"]);
					}
					//Cerramos la conexion
					$conexion_db->close();
					//Salimos del sistema
					header("Location: salir.php");
				}
				else{
					$_SESSION["ERROR_NUEVA_PASSWORD"] = "SI";
					header('Location: preguntas.php');					
				}	
			}
				
			echo $contrasena_anterior."<br/>".$contrasena_nueva."<br/>".$confirmar_contrasena_nueva;
			exit;
				
		}
		else if(!isset($_POST["cargador"]) and isset($_SESSION["PREGUNTAS"])){
			//Cargamos la página
			$tpl = new TemplatePower("../../interfaz/HTML/contrasena_inicio.html");
			$tpl->prepare();			
			$tpl->assign("DISPLAY_MENSAJE","none");
			$tpl->assign("SALTO_LINEA","");
			/*Respuesta automática*/
			if(isset($_SESSION["ERROR_PASSWORD"]) and strcmp($_SESSION["ERROR_PASSWORD"], "SI") == 0){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","Contraseña incorrecta");
				unset($_SESSION["ERROR_PASSWORD"]);
			}
			if(isset($_SESSION["ERROR_NUEVA_PASSWORD"]) and strcmp($_SESSION["ERROR_NUEVA_PASSWORD"], "SI") == 0){
				$tpl->assign("DISPLAY_MENSAJE","block");
				$tpl->assign("SALTO_LINEA","<br/><br/>");
				$tpl->assign("MENSAJE","Nueva contraseña incorrecta");
				unset($_SESSION["ERROR_NUEVA_PASSWORD"]);
			}						
			$tpl->printToScreen();
		}
		else{
			header("Location: salir.php");		
		}
	}
	else{				
		header("Location: salir.php");
	}
?>