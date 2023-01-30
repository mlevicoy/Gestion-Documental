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
				
		$tpl = new TemplatePower("../../interfaz/HTML/ingresarInforme.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		
		if(isset($_SESSION["TEMP_INFORME_REPETIDO"]) and $_SESSION["TEMP_INFORME_REPETIDO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: EL NOMBRE YA EXISTE");
			unset($_SESSION["TEMP_INFORME_REPETIDO"]);
		}
		if(isset($_SESSION["TEMP_INFORME_INGRESADO"]) and $_SESSION["TEMP_INFORME_INGRESADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","INFORME INGRESADO CORRECTAMENTE");
			unset($_SESSION["TEMP_INFORME_INGRESADO"]);
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
		//Cerramos la conexión
		$resultado->close();
		$resultado2->close();		
		$conexion_db->close();	
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario
		$nombreInformeNuevo = htmlentities(mb_strtoupper(trim($_POST["nombreInforme"]),'UTF-8')); 
		$descripcionInformeNuevo = htmlentities(mb_strtoupper(trim($_POST["descripcionInforme"]),'UTF-8')); 				
		$estadoInformeNuevo = $_POST["estadoInforme"];
		
		//Escapamos
		$nombreInformeNuevo = mysqli_real_escape_string($conexion_db, $nombreInformeNuevo);
		$descripcionInformeNuevo = mysqli_real_escape_string($conexion_db, $descripcionInformeNuevo);
        $nombreInformeSaneadoNuevo = mb_strtoupper(sanear_string($_POST["nombreInforme"]),'UTF-8');
		
		//Verificamos que el informe no exista
		$consulta = "select * from informe where nombre_informe = '".$nombreInformeNuevo."'";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		if($resultado->num_rows == 0){
			//Se guarda el informe en la base de datos
			$consulta2 = "insert into informe (nombre_informe, nombreSaneado_informe, descripcion_informe, estado_informe) values ('".
			$nombreInformeNuevo."','".$nombreInformeSaneadoNuevo."', '".$descripcionInformeNuevo."',".$estadoInformeNuevo.")";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado->close();
			$resultado2->close();
			$_SESSION["TEMP_INFORME_INGRESADO"] = "SI";
			header("Location: ingresarInforme.php");						
		}
		else{
			//No se guarda la información
			$resultado->close();
			$conexion_db->close();
			$_SESSION["TEMP_INFORME_REPETIDO"] = "SI";
			header("Location: ingresarInforme.php");			
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