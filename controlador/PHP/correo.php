<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	require_once("../phpmailer/class.phpmailer.php");
	
	//Funciones validación session	
	validaTiempo();
	
	//Carga inicial de la página
	if(!isset($_POST["cargador"]) and !isset($_POST["cargador2"])){
		header("Location: sistema.php");
	}
	else if(isset($_POST["cargador"]) and !isset($_POST["cargador2"])){		
		//Datos del formulario
		$idDocumento = $_POST["idDocumento"];
		$idDueno = $_POST["idDueno"];
		
		//Obtenemos el correo del dueño
		$consulta = "select correo_datosUsuario from datosUsuario where codigo_datosUsuario = ".$idDueno;
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($correo_datosUsuario);
		$resultado->fetch();
		
		//Cargamos la página de correo
		$tpl = new TemplatePower("../../interfaz/HTML/correo.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);	
		
		if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="administrador.php">Administrar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="trabajarDocumento.php">Regresar</a>');
		}
		//Se coloca la dirección de correo del dueño
		$tpl->assign("PARA", $correo_datosUsuario);	
		$tpl->printToScreen();	
	}
	else{
		//Datos del formulario
		$para = $_POST["para"];
		$conCopia = $_POST["conCopia"];
		$asunto = $_POST["asunto"];
		$cuerpo = $_POST["cuerpo"];						
		
		//Buscamos el nombre del dueño
		$consulta = "select nombre_datosUsuario, apellidos_datosUsuario from datosUsuario where correo_datosUsuario = '".$para."'";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($nombre_datosUsuario, $apellidos_datosUsuario);
		$resultado->fetch();
		
		//Datos del servidor
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = "smtp.gmail.com";	
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "tls";
		$mail->SMTPDebug = 1;		
		$mail->Username = "mlevicoy@gmail.com";
		$mail->Password = "MaLo1979";
		$mail->Port = 465;
	
		$mail->From = $mail->Username;
		$mail->FromName = "Mensaje Correo Sistema Gestión Documental";
		//$mail->AddAddress("manuel.levicoy@bogado.cl", "Nombre del usuario que recibe");
		$mail->AddAddress($para, $nombre_datosUsuario." ".$apellidos_datosUsuario);
		
		if(!empty(trim($conCopia))){
			$separaCopia = explode(",", $conCopia);	
			for($i=0;$i<count($separaCopia);$i++){
				$mail->AddCC(trim($separaCopia[$i]), "Copia usuario ".$i+1);
			}		
		}	
		
		$mail->WordWrap = 50;
		$mail->IsHTML(true);
	
		$mail->Subject = trim($asunto);		//Asunto
		$message = trim($cuerpo);	//Cuerpo del mensaje
		$mail->Body = $message."<br/><br/><br/>¡No responder este correo!";
		$mail->CharSet = "UTF-8";
	
		if(!$mail->Send()){
			$_SESSION["TEMP_ENVIO_INCORRECTO"] = $mail->ErrorInfo;
			header("Location: trabajarDocumento.php");		
		}
		else{
			$_SESSION["TEMP_ENVIO_CORRECTO"] = "SI";
			header("Location: trabajarDocumento.php");		
		}
	}
?>