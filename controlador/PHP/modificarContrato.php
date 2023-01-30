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
				
		$tpl = new TemplatePower("../../interfaz/HTML/modificarContrato.html");
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
		if(isset($_SESSION["TEMP_HABILITAR_CORRECTA"]) and $_SESSION["TEMP_HABILITAR_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","CONTRATO HABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_HABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_HABILITAR_INCORRECTO"]) and $_SESSION["TEMP_HABILITAR_INCORRECTO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: CONSULTAR CON ADMINISTRADOR");
			unset($_SESSION["TEMP_HABILITAR_INCORRECTO"]);
		}
		if(isset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]) and $_SESSION["TEMP_DESHABILITAR_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","CONTRATO DESHABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_DESHABILITAR_INCORRECTO"]) and $_SESSION["TEMP_DESHABILITAR_INCORRECTO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: CONSULTAR CON ADMINISTRADOR");
			unset($_SESSION["TEMP_DESHABILITAR_INCORRECTO"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","CONTRATO ACTUALIZADO CORRECTAMENTE");
			unset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_INCORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_INCORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: CONSULTAR CON ADMINISTRADOR");
			unset($_SESSION["TEMP_ACTUALIZACION_INCORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ERROR_FECHA"]) and $_SESSION["TEMP_ERROR_FECHA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: LAS FECHAS NO CORRESPONDEN");
			unset($_SESSION["TEMP_ERROR_FECHA"]);
		}
		if(isset($_SESSION["TEMP_ERROR_DIRECTORIO"]) and $_SESSION["TEMP_ERROR_DIRECTORIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: NO SE PUDO RENOMBRAR DIRECTORIO");
			unset($_SESSION["TEMP_ERROR_DIRECTORIO"]);
		}		
				
		/* Fin respuesta automática */
		
		/* Redireccionamiento */
		if(!isset($_GET["id"])){			
			/* Par el disabled en el Javascript */			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 1);
			$tpl->assign("VALOR3",1);
			$tpl->assign("SELECT_INICIO","0");
			$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR OPCIÓN ---");
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "");			
			$tpl->assign("HABILITAR_DESHABILIITAR", "HABILITAR");

			/* Se buscan los contratos */
			$consulta3 = "SELECT id_contrato, nombreCorto_contrato FROM contrato";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->bind_result($id_contrato, $nombreCorto_contrato);
			while($resultado3->fetch()){
				$tpl->newBlock("CONTRATO_BUSCAR");
				$tpl->assign("CODIGO_CONTRATO", $id_contrato);
				$tpl->assign("NOMBRE_CORTO_CONTRATO", $nombreCorto_contrato);				
			}			
			$resultado3->close();			
		}
		else{
			//ID del contrato
			$codigo_contrato = $_GET["id"];			
			//Enable los controles input y select			
			$tpl->assign("VALOR1","formulario_usuario");
			$tpl->assign("VALOR2", 0);
			$tpl->assign("VALOR3",1);			
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "--- SELECCIONAR OPCIÓN ---");			
			//llenamos nuevamente el select			
			$consulta4 = "SELECT id_contrato, nombreCorto_contrato FROM contrato";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			$resultado4->bind_result($id_contrato, $nombreCorto_contrato);
			while($resultado4->fetch()){
				if(strcmp($id_contrato,$codigo_contrato) != 0){
					$tpl->newBlock("CONTRATO_BUSCAR");
					$tpl->assign("CODIGO_CONTRATO",$id_contrato);
					$tpl->assign("NOMBRE_CORTO_CONTRATO",$nombreCorto_contrato);				
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("SELECT_INICIO",$id_contrato);
					$tpl->assign("VALOR_SELECT_INICIO",$nombreCorto_contrato);					
				}
			}
			$resultado4->close();						

			//Obtenemos la información del contrato
			$consulta5 = "select * from contrato where id_contrato = ".$codigo_contrato;
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			$resultado5->bind_result($id_contrato, $nombreCompleto_contrato, $nombreCorto_contrato, $nombreCortoSaneado_contrato, $fechaInicio_contrato, $fechaTermino_contrato, 
			$resolucion_contrato, $estado_contrato, $habilitado_contrato, $directorio_contrato);
			$resultado5->fetch();
			
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("NOMBRE_COMPLETO_CONTRATO", $nombreCompleto_contrato);
			$tpl->assign("NOMBRE_CORTO_CONTRATO", $nombreCorto_contrato);			
			if(strcmp($resolucion_contrato,"NULL") == 0){				
				$tpl->assign("VALUE_RESOLUCION", "");								
			}
			else{
				$tpl->assign("VALUE_RESOLUCION", $resolucion_contrato);
				$tpl->assign("CHECKBOX_CONTRATO", "");				
			}			
			$tpl->assign("FECHA_INICIO_CONTRATO", $fechaInicio_contrato);
			$tpl->assign("FECHA_TERMINO_CONTRATO", $fechaTermino_contrato);
			$tpl->assign("ESTADO3", "");
			$tpl->assign("VALOR_ESTADO3", "--- SELECCIONAR OPCIÓN ---");
			if($estado_contrato == 0){
				$tpl->assign("ESTADO1", "0");
				$tpl->assign("VALOR_ESTADO1", "Finalizada");
				$tpl->assign("ESTADO2", "1");
				$tpl->assign("VALOR_ESTADO2", "Iniciada");
			}
			else{
				$tpl->assign("ESTADO1", "1");
				$tpl->assign("VALOR_ESTADO1", "Iniciada");
				$tpl->assign("ESTADO2", "0");
				$tpl->assign("VALOR_ESTADO2", "Finalizada");
			}			
			$tpl->assign("RUTA_CAMINO", $directorio_contrato);
			if($habilitado_contrato == 0){
				$tpl->assign("HABILITAR_DESHABILIITAR", "HABILITAR");
			}
			else{
				$tpl->assign("HABILITAR_DESHABILIITAR", "DESHABILITAR");
			}
			$resultado5->close();
					
		}
		
		//CERRAMOS LOS RESULTADOS Y CONEXION
		$resultado->close();
		$resultado2->close();		
		$conexion_db->close();	
		$tpl->printToScreen();
	}
	else{
		//Boton
		$boton = $_POST["cambiar"];
		//Obtenemos el ID del contrato
		$contratoID = $_POST["contratoBuscar"];
		$rutaContrato = $_POST["ruta"];
			
		//OPCIÓN HABILITAR
		if(strcmp($boton,"HABILITAR") == 0){						
			//Se renombra el directorio
			$consulta = "select directorio_contrato from contrato where id_contrato = ".$contratoID;
			$resultado = $conexion_db->prepare($consulta);			
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($directorio_contrato);
			$resultado->fetch();
			//El nombre puede ser Nombre_DISABLED, Nombre_DISABLED_FINISH, Nombre_FINISH_DISABLED
			$directorio = explode("_DISABLED", $directorio_contrato);	//Quitamos el _DISABLED
			$directorio = implode($directorio);
			$directorio = trim($directorio);
			if(rename($directorio_contrato, $directorio)){
				//Habilitar el contrato
				$consulta2 = "update contrato set habilitado_contrato = 1, directorio_contrato = '".$directorio."' where id_contrato = ".$contratoID;
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado->close();
				$resultado2->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_HABILITAR_CORRECTA"] = "SI";
				header("Location: modificarContrato.php");				
			}
			else{
				$resultado->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_HABILITAR_INCORRECTO"] = "SI";
				header("Location: modificarContrato.php");				
			}
		}
		//OPCION DESHABILITAR
		if(strcmp($boton,"DESHABILITAR") == 0){
			//Se renombra el directorio
			$consulta = "select directorio_contrato from contrato where id_contrato = ".$contratoID;
			$resultado = $conexion_db->prepare($consulta);			
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($directorio_contrato);
			$resultado->fetch();
			if(rename($directorio_contrato, $directorio_contrato."_DISABLED")){
				//Deshabilitar el contrato
				$consulta2 = "update contrato set habilitado_contrato = 0, directorio_contrato = '".$directorio_contrato."_DISABLED' where id_contrato = ".
				$contratoID;
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado->close();
				$resultado2->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_DESHABILITAR_CORRECTA"] = "SI";
				header("Location: modificarContrato.php");
			}
			else{
				$resultado->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_DESHABILITAR_INCORRECTO"] = "SI";
				header("Location: modificarContrato.php");
			}
		}
		//OPCIÓN ACTUALIZAR
		else if(strcmp($boton,"ACTUALIZAR") == 0){
			//Informacion del formulario
			$nombreCompleto = htmlentities(mb_strtoupper(trim($_POST["nombreCompleto"]),'UTF-8')); 
			$nombreCorto = htmlentities(mb_strtoupper(trim($_POST["nombreCorto"]),'UTF-8')); 		
			$resolucion = htmlentities(mb_strtoupper(trim($_POST["resolucion"]),'UTF-8'));
            $fechaInicio = $_POST["fechaInicio"];		
			$fechaTermino = $_POST["fechaTermino"];
            $estado = $_POST["estado"];		
			
			$nombreCompleto = mysqli_real_escape_string($conexion_db,$nombreCompleto);
			$nombreCorto = mysqli_real_escape_string($conexion_db,$nombreCorto);
			$resolucion = mysqli_real_escape_string($conexion_db,$resolucion);
			//Verificamos que el nombre corto o el nombre largo no exista
			$consulta = "select * from contrato where id_contrato <> ".$contratoID." and (nombreCompleto_contrato = '".$nombreCompleto.
			"' or nombreCorto_contrato = '".$nombreCorto."')";
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			//Se realiza la modificación
			if($resultado->num_rows == 0){
				//Validamos las fechas inicio y termino
				if($fechaTermino != "0000-00-00" and $fechaInicio >= $fechaTermino){
					$resultado->close();
					$conexion_db->close();
					$_SESSION["TEMP_ERROR_FECHA"] = "SI";
					header('Location: modificarContrato.php');
				}												
				//Verificamos si el contrato esta iniciado o finalizado				
				$ruta_TEMP = explode("_DISABLED",$rutaContrato);	//Dividimos la ruta en _DISABLED
				if($estado == 0 and count($ruta_TEMP) == 2){	//Finalizada se agrega _DISABLED y _FINISH
					$ruta = "../../documentos/".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8')."_DISABLED_FINISH";	
				
				}
				if($estado == 0 and count($ruta_TEMP) == 1){	//Finalizada se agrega _FINISH
					$ruta = "../../documentos/".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8')."_FINISH";	
				
				}
				if($estado == 1 and count($ruta_TEMP) == 2){	//Iniciada se agrega _DISABLED
					$ruta = "../../documentos/".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8')."_DISABLED";	
				
				}
				if($estado == 1 and count($ruta_TEMP) == 1){	//Iniciada
					$ruta = "../../documentos/".mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8');	
				
				}				
							
				//Generamos el nuevo directorio
				if(!rename($rutaContrato, $ruta)){
					$resultado->close(); 
					$conexion_db->close();
					$_SESSION["TEMP_ERROR_DIRECTORIO"] = "SI";
					header('Location: modificarContrato.php');				
				}
				else{
					//Declaramos los array
					$ruta_array = array();
					$rutaInformeContrato_array = array();
					$rutaNueva_array = array();					
					$rutaNueva_array = explode("/",$ruta);
					
					//Actualizamos tabla contrato
					$consulta2 = "update contrato set nombreCompleto_contrato = '".$nombreCompleto."', nombreCorto_contrato = '".$nombreCorto."', nombreCortoSaneado_contrato = '".
					mb_strtoupper(sanear_string($_POST["nombreCorto"]),'UTF-8')."', fechaInicio_contrato = '".$fechaInicio."', fechaTermino_contrato = '".$fechaTermino."', resolucion_contrato = '".$resolucion.
					"', estado_contrato = ".$estado.", directorio_contrato = '".$ruta."' where id_contrato = ".$contratoID;				
					$resultado2 = $conexion_db->prepare($consulta2);										
					$resultado2->execute();
										
					//Obtenemos la ruta de los informes que estan en documentos
					$consulta6 = "select codigo_documento, ruta_documento from documento where contrato_documento = ".$contratoID;				
					$resultado6 = $conexion_db->prepare($consulta6);
					$resultado6->execute();
					$resultado6->store_result();
					$resultado6->bind_result($codigo_documento, $ruta_documento);
					//Se realiza la modificación
					if($resultado6->num_rows != 0){
						while($resultado6->fetch()){
							//Colocamos la ruta en un array								
							$ruta_array = explode("/",$ruta_documento);
							//Reemplazamos la nueva ruta
							$ruta_array[0] = $rutaNueva_array[0];
							$ruta_array[1] = $rutaNueva_array[1];
							$ruta_array[2] = $rutaNueva_array[2];
							$ruta_array[3] = $rutaNueva_array[3];
							//Actualizamos la tabla documento
							$consulta7 = "update documento set ruta_documento = '".implode("/",$ruta_array)."' where codigo_documento = ".$codigo_documento;								
							$resultado7 = $conexion_db->prepare($consulta7);										
							$resultado7->execute();
						}
					}
					
					//Obtenemos la ruta de los informecontrato
					$consulta8 = "select codigo_informeContrato, ruta_informeContrato from informecontrato where contrato_informeContrato = ".$contratoID;				
					$resultado8 = $conexion_db->prepare($consulta8);
					$resultado8->execute();
					$resultado8->store_result();
					$resultado8->bind_result($codigo_informeContrato, $ruta_informeContrato);
					//Se realiza la modificacion
                    if($resultado8->num_rows != 0){
                        while($resultado8->fetch()){
							//Colocamos la ruta en un array								
							$rutaInformeContrato_array = explode("/",$ruta_informeContrato);
							//Reemplazamos la nueva ruta
							$rutaInformeContrato_array[0] = $rutaNueva_array[0];
							$rutaInformeContrato_array[1] = $rutaNueva_array[1];
							$rutaInformeContrato_array[2] = $rutaNueva_array[2];
							$rutaInformeContrato_array[3] = $rutaNueva_array[3];
							//Actualizamos la tabla documento
							$consulta9 = "update informecontrato set ruta_informeContrato = '".implode("/",$rutaInformeContrato_array).
                            "' where codigo_informeContrato = ".$codigo_informeContrato;								
							$resultado9 = $conexion_db->prepare($consulta9);										
							$resultado9->execute();
						}                        
                    }
										
					//Cerramos las conexiones
					if(isset($resultado)){ $resultado->close(); }
					if(isset($resultado2)){ $resultado2->close(); }
					if(isset($resultado6)){ $resultado6->close(); }
					if(isset($resultado7)){ $resultado7->close(); }					
                    if(isset($resultado8)){ $resultado8->close(); }
                    if(isset($resultado9)){ $resultado9->close(); }
					$conexion_db->close();					
					//Redireccionamos
					$_SESSION["TEMP_ACTUALIZACION_CORRECTA"] = "SI";
					header("Location: modificarContrato.php");
				}				
			}
			//Se repite el nombre, no se puede realizar la modificacion
			else{
				$resultado->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_ACTUALIZACION_INCORRECTA"] = "SI";
				header("Location: modificarContrato.php");
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