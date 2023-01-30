<?PHP
	//LIBRERIAS
	require_once("../TemplatePower/class.TemplatePower.inc.php");
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Consulto si viene de donde tiene que venir
	if(!isset($_POST["cargador"]) and !isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){
		$tpl = new TemplatePower("../../interfaz/HTML/errorAcceso.html");
		$tpl->prepare();			
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
			
		/*Respuesta automática*/
		if(isset($_SESSION["NO_USER"]) and strcmp($_SESSION["NO_USER"], "SI") == 0){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","No existe el usuario");
			unset($_SESSION["NO_USER"]);
		}
		/*Respuesta automática*/
		if(isset($_SESSION["NO_PREGUNTAS"]) and strcmp($_SESSION["NO_PREGUNTAS"], "NO") == 0){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","No coinciden las respuestas");
			unset($_SESSION["NO_PREGUNTAS"]);
		}	
        /*Respuesta automática*/
		if(isset($_SESSION["ERROR_PASSWORD"]) and strcmp($_SESSION["ERROR_PASSWORD"], "SI") == 0){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","Las contraseñas no son iguales");
			unset($_SESSION["ERROR_PASSWORD"]);
		}	
         
		$tpl->printToScreen();
	}
	else if(isset($_POST["cargador"]) and !isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){
		//Informacion Formulario
		$usuarioRecuperar = htmlentities(mb_strtolower(trim($_POST["usuarioRecuperar"]),'UTF-8')); 
		$usuarioRecuperar = mysqli_real_escape_string($conexion_db, $usuarioRecuperar);		
		//Validamos el nombre de usuario
		$consulta = "select count(*) as ctdadUsuario from usuario where nombre_usuario= '".$usuarioRecuperar."'";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($ctdadUsuario);
		$resultado->fetch();
		if($ctdadUsuario == 0){
			$resultado->close();
			$conexion_db->close();
			$_SESSION["NO_USER"] = "SI";
			header("Location: errorAcceso.php");	
		}
		else{
			//Contador
			$i = 1;
			//Cargamos el formulario con las preguntas
			$tpl = new TemplatePower("../../interfaz/HTML/responder_preguntas.html");
			$tpl->prepare();			
			$tpl->assign("DISPLAY_MENSAJE","none");
			$tpl->assign("SALTO_LINEA","");		
			//Obtenemos el ID del usuario
			$consulta2 = "select codigo_usuario from usuario where nombre_usuario = '".$usuarioRecuperar."'";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
			$resultado2->bind_result($codigo_usuario);
			$resultado2->fetch();
			//Variable session del codigo de usuario
			$_SESSION["CODIGO_USUARIO"] = $codigo_usuario;			
			//Obtenemos las preguntas, respuestas relacionadas al usuario
			$consulta3 = "select preguntas.texto_pregunta, respuestas.idPregunta_respuesta, respuestas.texto_respuesta from preguntas inner join respuestas on preguntas.id_pregunta ".
			"= respuestas.idPregunta_respuesta and respuestas.idUsuario_respuesta = ".$codigo_usuario;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($pregunta, $idPregunta, $respuesta);
			while($resultado3->fetch()){
				$tpl->assign("PREGUNTA".$i,$pregunta);
				$tpl->assign("IDPREGUNTA".$i,$idPregunta);
				$i++;
			}
			$resultado->close();
			$resultado2->close();
			$resultado3->close();
			$conexion_db->close();
			$tpl->printToScreen();
		}
	}
	else if(!isset($_POST["cargador"]) and isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){
		//Contador
		$count=0;		
		//Datos del formulario
		$ID_P1 = $_POST["idPregunta1"];
		$ID_P2 = $_POST["idPregunta2"];
		$ID_P3 = $_POST["idPregunta3"];		
		$R1 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta1"]),'UTF-8')));		
		$R2 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta2"]),'UTF-8')));
		$R3 = htmlentities(ucwords(mb_strtolower(trim($_POST["respuesta_pregunta3"]),'UTF-8')));
		//Verificamos que las respuestas sean las correctas
		$consulta = "select idPregunta_respuesta, texto_respuesta from respuestas where idUsuario_respuesta = ".$_SESSION["CODIGO_USUARIO"];
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($idPregunta, $respuesta);
		while($resultado->fetch()){
			if($idPregunta == $ID_P1 and strcmp($respuesta,$R1)==0){
				$count++;
			}
			else if($idPregunta == $ID_P2 and strcmp($respuesta,$R2)==0){
				$count++;
			}
			else if($idPregunta == $ID_P3 and strcmp($respuesta,$R3)==0){
				$count++;
			}
		}
		if($count == 3){				
			//Cargamos el formulario con las preguntas
			$tpl = new TemplatePower("../../interfaz/HTML/recuperar_contrasena.html");
			$tpl->prepare();			
			$tpl->assign("DISPLAY_MENSAJE","none");
			$tpl->assign("SALTO_LINEA","");					
			$resultado->close();
			$conexion_db->close();
			$tpl->printToScreen();
				
			//Variable de session para el cambio de contraseña
		
		//Cerramos la conexión y liberamos las consultas
		//Llamamos al cambio de contraseña
		}
		else{
			$_SESSION["NO_PREGUNTAS"] = "NO";	
			$resultado->close();
			$conexion_db->close();	
			header("Location: errorAcceso.php");		
		}
	}
	else if(!isset($_POST["cargador"]) and !isset($_POST["cargador2"]) and isset($_POST["cargador3"])){		
		//Datos del formulario
		$contrasena_nueva = htmlentities(trim($_POST["password_nuevo"]));
		$confirmar_contrasena_nueva = htmlentities(trim($_POST["password_nuevo2"]));
        //Verificamos que las contraseñas sean iguales
		if(strcmp($contrasena_nueva, $confirmar_contrasena_nueva) == 0){
		  $consulta = "update usuario set contrasena_usuario = '".password_hash($contrasena_nueva,PASSWORD_BCRYPT)."' where codigo_usuario = ".
          $_SESSION["CODIGO_USUARIO"];
		  $resultado = $conexion_db->prepare($consulta);
		  $resultado->execute();
          $conexion_db->close();
          header("Location: salir.php");
		}
		else{
		  //Eliminamos la variable de session
          unset($_SESSION["OK_PREGUNTAS"]);
          $_SESSION["ERROR_PASSWORD"] = "SI";
          header("Location: errorAcceso.php");			
		}
	}
?>