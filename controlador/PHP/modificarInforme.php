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
				
		$tpl = new TemplatePower("../../interfaz/HTML/modificarInforme.html");
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
			$tpl->assign("MENSAJE","INFORME HABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_HABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]) and $_SESSION["TEMP_DESHABILITAR_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","INFORME DESHABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","INFORME ACTUALIZADO CORRECTAMENTE");
			unset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_INCORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_INCORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: CONSULTAR CON ADMINISTRADOR");
			unset($_SESSION["TEMP_ACTUALIZACION_INCORRECTA"]);
		}
		/* Fin respuesta automática */
		
		/* Redireccionamiento */
		if(!isset($_GET["id"])){			
			/* Par el disabled en el Javascript */			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 1);
			$tpl->assign("VALOR3",1);
			$tpl->assign("SELECT_INICIO","0");
			$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR DOCUMENTO ---");
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "");			
			$tpl->assign("HABILITAR_DESHABILIITAR", "HABILITAR");

			/* Se buscan los contratos */
			$consulta3 = "SELECT codigo_informe, nombre_informe FROM informe";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->bind_result($codigo_informe, $nombre_informe);
			while($resultado3->fetch()){
				$tpl->newBlock("INFORME_BUSCAR");
				$tpl->assign("CODIGO_INFORME", $codigo_informe);
				$tpl->assign("NOMBRE_INFORME", $nombre_informe);				
			}			
			$resultado3->close();			
		}
		else{
			//ID del informe
			$id_informe = $_GET["id"];			
			//Enable los controles input y select			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 0);
			$tpl->assign("VALOR3",1);			
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "--- SELECCIONAR DOCUMENTO ---");			
			//llenamos nuevamente el select			
			$consulta4 = "SELECT codigo_informe, nombre_informe, descripcion_informe, estado_informe FROM informe";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			$resultado4->bind_result($codigo_informe, $nombre_informe, $descripcion_informe, $estado_informe);
			while($resultado4->fetch()){
				if(strcmp($id_informe,$codigo_informe) != 0){
					$tpl->newBlock("INFORME_BUSCAR");
					$tpl->assign("CODIGO_INFORME",$codigo_informe);
					$tpl->assign("NOMBRE_INFORME",$nombre_informe);				
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("SELECT_INICIO",$codigo_informe);
					$tpl->assign("VALOR_SELECT_INICIO",$nombre_informe);					
				}
			}
			$resultado4->close();						

			//Obtenemos la información del contrato
			$consulta5 = "select * from informe where codigo_informe = ".$id_informe;
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			$resultado5->bind_result($codigo_informe, $nombre_informe, $nombreSaneado_informe, $descripcion_informe, $estado_informe);
			$resultado5->fetch();
			
            $tpl->gotoBlock("_ROOT");
			$tpl->assign("VALOR_NOMBRE_INFORME", $nombre_informe);
			$tpl->assign("VALOR_TEXTAREA_DESCRIPCION", $descripcion_informe);			
			$tpl->assign("ESTADO3", "");
			$tpl->assign("VALOR_ESTADO3", "--- SELECCIONAR ESTADO ---");
			if($estado_informe == 0){
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
			if($estado_informe == 0){
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
		$informeID = $_POST["informeBuscar"];		
			
		//OPCIÓN HABILITAR
		if(strcmp($boton,"HABILITAR") == 0){			
			//Obtenemos el nombre del informe
			//$consulta = "select nombre_informe from informe where codigo_informe = ".$informeID;
            $consulta = "select nombreSaneado_informe from informe where codigo_informe = ".$informeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombreSaneado_informe);
			$resultado->fetch();
			//Informe disabled
			//$nombre_informe = sanear_string($nombre_informe);
			$informeModificado = $nombreSaneado_informe."_DISABLED";
	
			//Habilitar el informe
			$consulta2 = "update informe set estado_informe = 1 where codigo_informe = ".$informeID;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			
			//Recorremos los contratos buscando el informe para deshabilitar
			$dir = opendir("../../documentos/");						
			while($recorrido = readdir($dir)){
				if($recorrido != "." && $recorrido != ".."){
					if(is_dir("../../documentos/".$recorrido)){						
						if(file_exists("../../documentos/".$recorrido."/".$informeModificado)){
							rename("../../documentos/".$recorrido."/".$informeModificado, "../../documentos/".$recorrido."/".$nombreSaneado_informe);
						}						
					}					
				}
			}
			
            //Actualizamos la tabla informecontrato si es necesario
            $rutaInforme_array = array();
            $consulta15 = "select codigo_informeContrato, ruta_informeContrato from informecontrato where informe_informeContrato = ".$informeID;
            $resultado15 = $conexion_db->prepare($consulta15);
            $resultado15->execute();
            $resultado15->store_result();
            $resultado15->bind_result($codigo_informeContrato, $ruta_informeContrato);
            if($resultado15->num_rows != 0){
                while($resultado->fetch()){
                    $rutaInforme_array = explode("/",$ruta_informeContrato);
			        //Reemplazamos el nuevo nombre
                    $rutaInforme_array[4] = $nombreSaneado_informe;
				    //Actualizamos la tabla documento
					$consulta16 = "update informecontrato set ruta_informeContrato = '".implode("/",$rutaInforme_array).
                    "' where codigo_informeContrato = ".$codigo_informeContrato;								
				    $resultado16 = $conexion_db->prepare($consulta16);										
					$resultado16->execute();    
                }    
            }
			
			//Cerrar variables de conexión a DB			
			if(isset($resultado2)){ $resultado2->close(); }
            if(isset($resultado15)){ $resultado15->close(); }
            if(isset($resultado16)){ $resultado16->close(); }
            			
			$conexion_db->close();
			//Redireccionamos
			$_SESSION["TEMP_HABILITAR_CORRECTA"] = "SI";
			header("Location: modificarInforme.php");
		}
		//OPCION DESHABILITAR
		if(strcmp($boton,"DESHABILITAR") == 0){
			//Obtenemos el nombre del informe
			$consulta = "select nombreSaneado_informe from informe where codigo_informe = ".$informeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombreSaneado_informe);
			$resultado->fetch();
			//Informe disabled
			//$nombre_informe = sanear_string($nombre_informe);
			$informeModificado = $nombreSaneado_informe."_DISABLED";
			
			//Deshabilitar el informe
			$consulta2 = "update informe set estado_informe = 0 where codigo_informe = ".$informeID;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			
			//Recorremos los contratos buscando el informe para deshabilitar
			$dir = opendir("../../documentos/");						
			while($recorrido = readdir($dir)){
				if($recorrido != "." && $recorrido != ".."){
					if(is_dir("../../documentos/".$recorrido)){						
						if(file_exists("../../documentos/".$recorrido."/".$nombreSaneado_informe)){
							rename("../../documentos/".$recorrido."/".$nombreSaneado_informe, "../../documentos/".$recorrido."/".$informeModificado);
						}						
					}					
				}
			}
            
            //Actualizamos la tabla informecontrato si es necesario
            $rutaInforme_array = array();
            $consulta15 = "select codigo_informeContrato, ruta_informeContrato from informecontrato where informe_informeContrato = ".$informeID;
            $resultado15 = $conexion_db->prepare($consulta15);
            $resultado15->execute();
            $resultado15->store_result();
            $resultado15->bind_result($codigo_informeContrato, $ruta_informeContrato);
            if($resultado15->num_rows != 0){
                while($resultado->fetch()){
                    $rutaInforme_array = explode("/",$ruta_informeContrato);
			        //Reemplazamos el nuevo nombre
                    $rutaInforme_array[4] = $informeModificado;
				    //Actualizamos la tabla documento
					$consulta16 = "update informecontrato set ruta_informeContrato = '".implode("/",$rutaInforme_array).
                    "' where codigo_informeContrato = ".$codigo_informeContrato;								
				    $resultado16 = $conexion_db->prepare($consulta16);										
					$resultado16->execute();    
                }    
            }
			
			//Cerrar variables de conexión a DB
			if(isset($resultado)){ $resultado->close(); }
			if(isset($resultado2)){ $resultado2->close(); }
            if(isset($resultado15)){ $resultado15->close(); }
            if(isset($resultado16)){ $resultado16->close(); }
            
			$conexion_db->close();
			//Redireccionamos
			$_SESSION["TEMP_DESHABILITAR_CORRECTA"] = "SI";
			header("Location: modificarInforme.php");
		}
		//OPCIÓN ACTUALIZAR
		else if(strcmp($boton,"ACTUALIZAR") == 0){		  
		  	//Informacion del formulario
			$nombreInformeActualizado = htmlentities(mb_strtoupper(trim($_POST["nombreInforme"]),'UTF-8')); 
			$descripcionInformeActualizado = htmlentities(mb_strtoupper(trim($_POST["descripcionInforme"]),'UTF-8')); 					
			$estadoActualizado = $_POST["estadoInforme"];		
			
			//Escapamos
			$nombreInformeActualizado = mysqli_real_escape_string($conexion_db, $nombreInformeActualizado);
			$descripcionInformeActualizado = mysqli_real_escape_string($conexion_db, $descripcionInformeActualizado);
            $nombreInformeSaneadoActualizado = mb_strtoupper(sanear_string($_POST["nombreInforme"]),'UTF-8');
			
            //Obtenemos el nombre original del informe y su estado
			$consulta = "select nombre_informe, nombreSaneado_informe, estado_informe from informe where codigo_informe = ".$informeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombre_informe, $nombreSaneado_informe, $estado_informe);
			$resultado->fetch();
            
            $nombre_informe_buscar_aux = $nombreSaneado_informe;
            $estado_informe_buscar_aux = $estado_informe;
            
            //Verificamos que el nombre no exista
			$consulta2 = "select * from informe where codigo_informe <> ".$informeID." and nombre_informe = '".$nombreInforme."'";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
            
            if($resultado2->num_rows == 0){
			     //Se realiza la modificación
				$consulta3 = "update informe set nombre_informe = '".$nombreInformeActualizado."', nombreSaneado_informe = '".$nombreInformeSaneadoActualizado.
                "', descripcion_informe = '".$descripcionInformeActualizado."', estado_informe = ".$estadoActualizado." where codigo_informe = ".$informeID;				
				$resultado3 = $conexion_db->prepare($consulta3);										
				$resultado3->execute();
			
            	//Nombre a buscar
				if($estado_informe_buscar_aux == 1){ $nombre_informe_buscar = $nombre_informe_buscar_aux; }
				else if($estado_informe_buscar_aux == 0){ $nombre_informe_buscar = $nombre_informe_buscar_aux."_DISABLED"; }
				
				//Verificamos el disabled - datos a actualizar
				if($estadoActualizado == 1){ $nombreInforme = $nombreInformeSaneadoActualizado; }
				else if($estadoActualizado == 0){ $nombreInforme = $nombreInformeSaneadoActualizado."_DISABLED"; }
                
                //Recorremos los contratos buscando el informe para modificarlo
				$dir = opendir("../../documentos/");						
				while($recorrido = readdir($dir)){
					if($recorrido != "." && $recorrido != ".."){						
						if(is_dir("../../documentos/".$recorrido)){						
							if(file_exists("../../documentos/".$recorrido."/".$nombre_informe_buscar)){	
								rename("../../documentos/".$recorrido."/".$nombre_informe_buscar, "../../documentos/".$recorrido."/".$nombreInforme);
							}						
						}											
					}
				}
                
                //Actualizamos la tabla informecontrato si es necesario
                $rutaInforme_array = array();
                $consulta15 = "select codigo_informeContrato, ruta_informeContrato from informecontrato where informe_informeContrato = ".$informeID;
                $resultado15 = $conexion_db->prepare($consulta15);
                $resultado15->execute();
                $resultado15->store_result();
                $resultado15->bind_result($codigo_informeContrato, $ruta_informeContrato);
                if($resultado15->num_rows != 0){
                    while($resultado15->fetch()){
                        $rutaInforme_array = explode("/",$ruta_informeContrato);
				        //Reemplazamos el nuevo nombre
                        $rutaInforme_array[4] = $nombreInforme;
				        //Actualizamos la tabla documento
						$consulta16 = "update informecontrato set ruta_informeContrato = '".implode("/",$rutaInforme_array).
                        "' where codigo_informeContrato = ".$codigo_informeContrato;								
						$resultado16 = $conexion_db->prepare($consulta16);										
						$resultado16->execute();    
                    }    
                }

                //Actualizamos la tabla documento si es necesario
                $rutaDocumento_array = array();
                $consulta17 = "select codigo_documento, ruta_documento from documento where informe_documento = ".$informeID;
                $resultado17 = $conexion_db->prepare($consulta17);
                $resultado17->execute();
                $resultado17->store_result();
                $resultado17->bind_result($codigo_documento, $ruta_documento);
                if($resultado17->num_rows != 0){
                    while($resultado17->fetch()){
                        $rutaDocumento_array = explode("/",$ruta_documento);
				        //Reemplazamos el nuevo nombre
                        $rutaDocumento_array[4] = $nombreInforme;
				        //Actualizamos la tabla documento
						$consulta18 = "update documento set ruta_documento = '".implode("/",$rutaDocumento_array).
                        "' where codigo_documento = ".$codigo_documento;								
						$resultado18 = $conexion_db->prepare($consulta18);										
						$resultado18->execute();    
                    }    
                }			    
				
				//Cerramos las conexiones					
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
                if(isset($resultado15)){ $resultado15->close(); }
                if(isset($resultado16)){ $resultado16->close(); }
                if(isset($resultado17)){ $resultado17->close(); }
                if(isset($resultado18)){ $resultado18->close(); }
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_ACTUALIZACION_CORRECTA"] = "SI";
				header("Location: modificarInforme.php");
			}				
			//Se repite el nombre, no se puede realizar la modificacion
			else{
				$resultado->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_ACTUALIZACION_INCORRECTA"] = "SI";
				header("Location: modificarInforme.php");
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