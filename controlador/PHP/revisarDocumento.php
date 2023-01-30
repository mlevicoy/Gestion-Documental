<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	//ID del documento
	$id = $_GET["id"];
	//Se carga la página
	$tpl = new TemplatePower("../../interfaz/HTML/revisarDocumento.html");
	$tpl->prepare();
	$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
	$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
	$tpl->assign("CARGO",$_SESSION["CARGO"]);
	$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
	$tpl->assign("CORREO",$_SESSION["CORREO"]);
	$tpl->assign("DISPLAY_MENSAJE","none");
	$tpl->assign("SALTO_LINEA","");
	$tpl->assign("PDF_NATIVO","none");
	$tpl->assign("VISOR_GOOGLE","none");
	$tpl->assign("IMAGEN_NATIVO","none");	
	/*if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
		$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
	}
	else{
		$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
		&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
	}*/
    if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
	}
	else{
        $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
	}	
	
	//Sacamos la información de la tabla documentos
	$consulta = "select contrato_documento, informe_documento, subInforme_documento, fecha_documento, fechaSubida_documento, horaSubida_documento, ".
	"destinatario_documento, descripcion_documento, nombre_documento, ruta_documento, idDueno_documento from documento where codigo_documento = ".$id;
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();
	$resultado->store_result();
	$resultado->bind_result($contrato_documento, $informe_documento, $subInforme_documento, $fecha_documento, $fechaSubida_documento, $horaSubida_documento, 
	$destinatario_documento, $descripcion_documento, $nombre_documento, $ruta_documento, $idDueno_documento);
	$resultado->fetch();
	
	//Colocamos el nombre del archivo
	//Generamos un array con la ruta
	$ruta = explode("/",$ruta_documento);
	//Eliminamos los dos primeros elementos del array ("../../")
	unset($ruta[0]);
	unset($ruta[1]);
	//Regeneramos el indice del array
	$ruta_regenerada = array_merge($ruta);
	//Unimos el array en un arreglo
	$ruta_string = implode("/", $ruta_regenerada);
	//Revisamos la extensión del archivo
	$trozos = explode(".", $nombre_documento);
	$extension = end($trozos);
	
	$cliente_web = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(stripos($cliente_web, 'android')!==false || stripos($cliente_web, 'iphone')!==false){
	    $tpl->assign("PDF_NATIVO","none");
        $tpl->assign("IMAGEN_NATIVO","none");
        $tpl->assign("DOWNLOAD_PHONE","inline");
        
		$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
		$tpl->assign("RUTA_ARCHIVO",$ruta_string);
	}
	else{
		if(strcmp($extension,"pdf") == 0 || strcmp($extension, "PDF") == 0){		
			$tpl->assign("PDF_NATIVO","inline");
            $tpl->assign("IMAGEN_NATIVO","none");
            $tpl->assign("DOWNLOAD_PHONE","none");
			$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
			$tpl->assign("RUTA_ARCHIVO",$ruta_string);			
		}
		else if(strcmp($extension, "jpg") == 0 || strcmp($extension, "png") == 0 || strcmp($extension, "bmp") == 0){
			$tpl->assign("PDF_NATIVO","none");
            $tpl->assign("IMAGEN_NATIVO","inline");
            $tpl->assign("DOWNLOAD_PHONE","none");	
			$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
			$tpl->assign("RUTA_ARCHIVO",$ruta_string);		
		}		
	}
    /*if(strcmp($_SERVER['SERVER_NAME'],"190.96.66.245") == 0){
        //Mostramos la información
		$tpl->assign("VISOR_GOOGLE","inline");	
		$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
		$tpl->assign("RUTA_ARCHIVO",$ruta_string);
    }*/
    //exit;
	/*
	if(strcmp($extension,"pdf") == 0 || strcmp($extension, "PDF") == 0){
		//Mostramos la información
		$tpl->assign("PDF_NATIVO","inline");
		$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
		$tpl->assign("RUTA_ARCHIVO",$ruta_string);
		/*echo $_SERVER['SERVER_NAME']."<br/>".$ruta_string;
		exit;*/
	/*}
	else if(strcmp($extension, "jpg") == 0 || strcmp($extension, "png") == 0 || strcmp($extension, "bmp") == 0){
		$tpl->assign("IMAGEN_NATIVO","inline");	
		$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
		$tpl->assign("RUTA_ARCHIVO",$ruta_string);		
	}
	else{
		//Mostramos la información
		$tpl->assign("VISOR_GOOGLE","inline");	
		$tpl->assign("NOMBRE_SERVIDOR", $_SERVER['SERVER_NAME']);	
		$tpl->assign("RUTA_ARCHIVO",$ruta_string);
	}*/
	
	//Buscamos el nombre del contrato
	$consulta2 = "select nombreCorto_contrato from contrato where id_contrato = ".$contrato_documento;
	$resultado2 = $conexion_db->prepare($consulta2);
	$resultado2->execute();
	$resultado2->store_result();
	$resultado2->bind_result($nombreCorto_contrato);
	$resultado2->fetch();
	$tpl->assign("CONTRATO", ucwords(mb_strtolower($nombreCorto_contrato, "UTF-8")));
	
	//Buscamos el nombre del informe
	$consulta3 = "select nombre_informe from informe where codigo_informe = ".$informe_documento;
	$resultado3 = $conexion_db->prepare($consulta3);
	$resultado3->execute();
	$resultado3->store_result();
	$resultado3->bind_result($nombre_informe);
	$resultado3->fetch();
	$tpl->assign("CATEGORIA", ucwords(mb_strtolower($nombre_informe, "UTF-8")));
	
	//Buscamos el nombre del subInforme
	$consulta4 = "select nombre_subInforme from subInforme where codigo_subInforme = ".$subInforme_documento;
	$resultado4 = $conexion_db->prepare($consulta4);
	$resultado4->execute();
	$resultado4->store_result();
	$resultado4->bind_result($nombre_subInforme);
	$resultado4->fetch();
	$tpl->assign("TIPO", ucwords(mb_strtolower($nombre_subInforme, "UTF-8")));
	
	//Nombre del documento
	$tpl->assign("NOMBRE_DOCUMENTO", ucwords(mb_strtolower($nombre_documento, "UTF-8")));
	
	//Fecha del documento
	$tpl->assign("FECHA_DOCUMENTO", fecha($fecha_documento));
	
	//Fecha de subida
	$tpl->assign("FECHA_SUBIDA", fecha($fechaSubida_documento));
	
	//Hora subida
	$tpl->assign("HORA_SUBIDA", $horaSubida_documento);
	
	//Dueño del documento
	$consulta5 = "select nombre_usuario, cuenta_usuario from usuario where codigo_usuario = ".$idDueno_documento;
	$resultado5 = $conexion_db->prepare($consulta5);
	$resultado5->execute();
	$resultado5->store_result();
	$resultado5->bind_result($nombre_usuario, $cuenta_usuario);
	$resultado5->fetch();
	$tpl->assign("DUENO_DOCUMENTO", ucwords(mb_strtolower($nombre_usuario, "UTF-8")));
	
	//Cargo del dueño
	$consulta6 = "select nombre_tipoCuenta from tipoCuenta where codigo_tipoCuenta = ".$cuenta_usuario;
	$resultado6 = $conexion_db->prepare($consulta6);
	$resultado6->execute();
	$resultado6->store_result();
	$resultado6->bind_result($nombre_tipoCuenta);
	$resultado6->fetch();
	$tpl->assign("CARGO_DUENO", ucwords(mb_strtolower($nombre_tipoCuenta, "UTF-8")));
	
	//Destinatario
	$consulta7 = "select nombre_destinatario, apellido_destinatario, cargo_destinatario from destinatario where id_destinatario = ".$destinatario_documento;
	$resultado7 = $conexion_db->prepare($consulta7);
	$resultado7->execute();
	$resultado7->store_result();
	$resultado7->bind_result($nombre_destinatario, $apellido_destinatario, $cargo_destinatario);
	$resultado7->fetch();
	$tpl->assign("DESTINATARIO", ucwords(mb_strtolower($nombre_destinatario." ".$apellido_destinatario." (".$cargo_destinatario.")", "UTF-8")));
	
	//Descripción
	$tpl->assign("DESCREPCION_DOCUMENTO", ucfirst(mb_strtolower($descripcion_documento, "UTF-8")));
	
	//Actualizamos el historia
	//$fecha_historial = date("Y-m-d");
	//$hora_historial = date("H:m:s");	
	//$mensaje = strval("Documento revisado por el usuario ".$_SESSION['NOMBRE']." ".$_SESSION['APELLIDO']." (".$_SESSION['USUARIO'].
	//"), el d&iacute;a ".fecha($fecha_historial)." a las ".$hora_historial." horas.");			
	//$consulta8 = "insert into historial (documento_historial, mensaje_historial) values (".$id.", '".$mensaje."')";		
	//$resultado8 = $conexion_db->prepare($consulta8);
	//$resultado8->execute();			
	//Obtenemos el historia
	$i=1;
	$consulta9 = "select mensaje_historial from historial where documento_historial = ".$id;
	$resultado9 = $conexion_db->prepare($consulta9);
	$resultado9->execute();
	$resultado9->store_result();
	$resultado9->bind_result($mensaje_historial);
	while($resultado9->fetch()){
		$tpl->newBlock("HISTORIAL");
		$tpl->assign("INDICE", $i);
		$tpl->assign("DATOS_HISTORIAL", $mensaje_historial);
		$i++;
	}
    $i=1;
	$consulta9 = "select mensaje_historial from historial where documento_historial = ".$id;
	$resultado9 = $conexion_db->prepare($consulta9);
	$resultado9->execute();
	$resultado9->store_result();
	$resultado9->bind_result($mensaje_historial);
	while($resultado9->fetch()){
		$tpl->newBlock("HISTORIAL2");
		$tpl->assign("INDICE2", $i);
		$tpl->assign("DATOS_HISTORIAL2", $mensaje_historial);
		$i++;
	}

	
	//ID del dueño y documento
	$tpl->gotoBlock("_ROOT");
	$tpl->assign("ID_DOCUMENTO", $id);
	$tpl->assign("ID_DUENO", $idDueno_documento);
		
	$tpl->printToScreen();	
	
	//Fecha
	function fecha($string){
		//Separar fecha
		$fecha = explode("-", $string);		
		switch((int)$fecha[1]){
			case 1:
				$texto = $fecha[2]." de enero de ".$fecha[0];
				break;
			case 2:
				$texto = $fecha[2]." de febrero de ".$fecha[0];
				break;
			case 3:
				$texto = $fecha[2]." de marzo de ".$fecha[0];
				break;
			case 4:
				$texto = $fecha[2]." de abril de ".$fecha[0];
				break;
			case 5:
				$texto = $fecha[2]." de mayo de ".$fecha[0];
				break;
			case 6:
				$texto = $fecha[2]." de junio de ".$fecha[0];
				break;
			case 7:
				$texto = $fecha[2]." de julio de ".$fecha[0];
				break;
			case 8:
				$texto = $fecha[2]." de agosto de ".$fecha[0];
				break;
			case 9:
				$texto = $fecha[2]." de septiembre de ".$fecha[0];
				break;
			case 10:
				$texto = $fecha[2]." de octubre de ".$fecha[0];
				break;
			case 11:
				$texto = $fecha[2]." de noviembre de ".$fecha[0];
				break;
			case 12:
				$texto = $fecha[2]." de diciembre de ".$fecha[0];
				break;
		}
		return $texto;
	}
?>