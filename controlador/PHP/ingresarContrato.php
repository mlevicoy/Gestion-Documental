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
				
		$tpl = new TemplatePower("../../interfaz/HTML/ingresarContrato.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		
		if(isset($_SESSION["TEMP_NOMBRE_REPETIDO"]) and $_SESSION["TEMP_NOMBRE_REPETIDO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: YA EXISTE EL NOMBRE");
			unset($_SESSION["TEMP_NOMBRE_REPETIDO"]);
		}
		if(isset($_SESSION["TEMP_ERROR_DIRECTORIO"]) and $_SESSION["TEMP_ERROR_DIRECTORIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: FALLO DIRECTORIO");
			unset($_SESSION["TEMP_ERROR_DIRECTORIO"]);
		}
		
		if(isset($_SESSION["TEMP_OK_CONTRATO"]) and $_SESSION["TEMP_OK_CONTRATO"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","CONTRATO INGRESADO CORRECTAMENTE");
			unset($_SESSION["TEMP_OK_CONTRATO"]);
		}
		
		if(isset($_SESSION["TEMP_ERROR_FECHA"]) and $_SESSION["TEMP_ERROR_FECHA"] = "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: NO COINCIDEN LAS FECHAS");
			unset($_SESSION["TEMP_ERROR_FECHA"]);
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
		$nombreCompleto = htmlentities(mb_strtoupper(trim($_POST["nombreCompleto"]),'UTF-8')); 
		$nombreCorto = htmlentities(mb_strtoupper(trim($_POST["nombreCorto"]),'UTF-8')); 		
		$resolucion = htmlentities(mb_strtoupper(trim($_POST["resolucion"]),'UTF-8'));
        $fechaInicio = $_POST["fechaInicio"];		
		$fechaTermino = $_POST["fechaTermino"];
        $estado = $_POST["estado"];
	
    	//Validamos las fechas
		if($fechaTermino != "0000-00-00" and $fechaInicio >= $fechaTermino){
			$_SESSION["TEMP_ERROR_FECHA"] = "SI";
			header('Location: ingresarContrato.php');
		}
		
		//Verificamos que nombre o nombre corto no exista
		//Realizamos la consulta
		$nombreCompleto = mysqli_real_escape_string($conexion_db,$nombreCompleto);		
		$nombreCorto = mysqli_real_escape_string($conexion_db,$nombreCorto);
		$resolucion = mysqli_real_escape_string($conexion_db,$resolucion);			
		
		$consulta = "SELECT * FROM `contrato` WHERE `nombreCompleto_contrato` = '".$nombreCompleto."' or `nombreCorto_contrato` = '".$nombreCorto."'";
		//Preparamos la conexión
		$resultado = $conexion_db->prepare($consulta);
		//Ejecutamos la consulta
		$resultado->execute();
		//Guardamos el resultado	
		$resultado->store_result();
		//Consultamos si el nombre de usuario existe o no			
		if($resultado->num_rows != 0){
			$resultado->close(); 
			$conexion_db->close();
			$_SESSION["TEMP_NOMBRE_REPETIDO"] = "SI";
			header('Location: ingresarContrato.php');
		}
		else{
			//Generamos el directorio
			$ruta = "../../documentos/".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8');
			if(!mkdir($ruta, 0777, true)){
				$resultado->close(); 
				$conexion_db->close();
				$_SESSION["TEMP_ERROR_DIRECTORIO"] = "SI";
				header('Location: ingresarContrato.php');				
			}
			else{
				//Almacenamos el contrato
				$consulta2 = "INSERT INTO `contrato` (`nombreCompleto_contrato`, `nombreCorto_contrato`, `nombreCortoSaneado_contrato`, `fechaInicio_contrato`, ".
				"`fechaTermino_contrato`, `resolucion_contrato`, `estado_contrato`, `habilitado_contrato`, `directorio_contrato`) values ('".
				$nombreCompleto."', '".$nombreCorto."', '".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8')."', '".$fechaInicio."', '".$fechaTermino."', '".
				$resolucion."', ".$estado.", 1, '".$ruta."')";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				//Finalizamos la consulta y conexión
				$resultado->close();
				$resultado2->close();
				$conexion_db->close();				
				$_SESSION["TEMP_OK_CONTRATO"] = "SI";
				header('Location: ingresarContrato.php');
			}			
		}
	}
	
	function sanear_string($string){
		
		$string = trim($string);	//Elimina espacios en blanco al principio y al final
		
		$string = str_replace(array('á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);		
    	$string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
		$string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
	    $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
	    $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
    	$string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);				
		$string = str_replace(array("\\", "¨", "º", "°", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", 
									"¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", "."), '', $string);
		$string = str_replace("  ", " ", $string);
		$string = str_replace(" ", "_", $string);
	   	return $string;
	}
?>