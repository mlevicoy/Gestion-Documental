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
				
		$tpl = new TemplatePower("../../interfaz/HTML/asociarSubInformeInforme.html");		
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");		
		
		if(isset($_SESSION["TEMP_DIR_ASOCIADO"]) and $_SESSION["TEMP_DIR_ASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: PROBLEMA CON DIRECTORIO");
			unset($_SESSION["TEMP_DIR_ASOCIADO"]);
		}		
		if(isset($_SESSION["TEMP_NO_ASOCIADO"]) and $_SESSION["TEMP_NO_ASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: SUB INFORMES YA ASOCIADOS");
			unset($_SESSION["TEMP_NO_ASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_CASI_ASOCIADO"]) and $_SESSION["TEMP_CASI_ASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ALGUNOS SUB INFORMES NO SE ASOCIARON");
			unset($_SESSION["TEMP_CASI_ASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_ASOCIADO"]) and $_SESSION["TEMP_ASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ASOCIACIÓN CORRECTA");
			unset($_SESSION["TEMP_ASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_ERROR_DESASOCIAR"]) and $_SESSION["TEMP_ERROR_DESASOCIAR"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: CONSULTAR CON EL ADMINISTRADOR");
			unset($_SESSION["TEMP_ERROR_DESASOCIAR"]);
		}
		if(isset($_SESSION["TEMP_CASI_ERROR_DESASOCIAR"]) and $_SESSION["TEMP_CASI_ERROR_DESASOCIAR"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","DESASOCIACIÓN CORRECTA CON EXCEPCIONES");
			unset($_SESSION["TEMP_CASI_ERROR_DESASOCIAR"]);
		}
		if(isset($_SESSION["TEMP_DESASOCIADO"]) and $_SESSION["TEMP_DESASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","DESASOCIACIÓN CORRECTAMENTE");
			unset($_SESSION["TEMP_DESASOCIADO"]);
		}
		//Cargamos el menu		
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
		if(!isset($_POST["cargador2"])){	
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALUE_INICIO","");
			$tpl->assign("INFO_INICIO","--- SELECCIONAR CONTRATO ---");
			//Cargamos los contratos
			$consulta3 = "select distinct contrato_informeContrato from informeContrato";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$x = $resultado3->num_rows;
			$z = 1;
			$resultado3->bind_result($contrato);
			while($resultado3->fetch()){
				$consulta4 = "select nombreCorto_contrato from contrato where id_contrato = ".$contrato;
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($nombreCorto_contrato);
				$resultado4->fetch();				
				if($z != $x){
					$tpl->newBlock("NOMBRE_CONTRATO");
					$tpl->assign("CODIGO_CONTRATO",$contrato);
					$tpl->assign("INFORMACION_CONTRATO",$nombreCorto_contrato);
					$z++;
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALUE_TERMINO",$contrato);
					$tpl->assign("INFO_TERMINO",$nombreCorto_contrato);				
				}				
			}
			$resultado3->close();
			$resultado4->close();
		}
		else{
			//Codigo del contrato
			$codigo_contrato = $_POST["nombreContrato"];						
			$_SESSION["CODIGO_CONTRATO"] = $codigo_contrato;
			
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALUE_TERMINO","");
			$tpl->assign("INFO_TERMINO","--- SELECCIONAR CONTRATO ---");
			//Cargamos los contratos
			$consulta3 = "select distinct contrato_informeContrato from informeContrato";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$x = $resultado3->num_rows;
			$z = 1;
			$resultado3->bind_result($contrato);
			while($resultado3->fetch()){
				$consulta4 = "select nombreCorto_contrato from contrato where id_contrato = ".$contrato;
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($nombreCorto_contrato);
				$resultado4->fetch();				
				if($codigo_contrato != $contrato){
					$tpl->newBlock("NOMBRE_CONTRATO");
					$tpl->assign("CODIGO_CONTRATO",$contrato);
					$tpl->assign("INFORMACION_CONTRATO",$nombreCorto_contrato);
					$z++;
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALUE_INICIO",$contrato);
					$tpl->assign("INFO_INICIO",$nombreCorto_contrato);				
				}				
			}
			$resultado3->close();
			$resultado4->close();
			//Obtenemos los informes ya asociados		
			$consulta5 = "select informe_informeContrato from informecontrato where contrato_informeContrato = ".$codigo_contrato;
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			$resultado5->store_result();
			$resultado5->bind_result($informe_informeContrato);
			while($resultado5->fetch()){				
				$consulta6 = "SELECT nombre_informe from informe WHERE codigo_informe = ".$informe_informeContrato;
				$resultado6 = $conexion_db->prepare($consulta6);
				$resultado6->execute();
				$resultado6->store_result();
				$resultado6->bind_result($nombreInforme);
				$resultado6->fetch();				
				$tpl->newBlock("NOMBRE_INFORME");
				$tpl->assign("CODIGO_INFORME",$informe_informeContrato);
				$tpl->assign("INFORMACION_INFORME",$nombreInforme);
			}
			//Obtenemos los sub-informes
			$tpl->gotoBlock("_ROOT");
			$consulta7 = "select codigo_subInforme, nombre_subInforme from subInforme order by nombre_subInforme";
			$resultado7 = $conexion_db->prepare($consulta7);
			$resultado7->execute();
			$resultado7->bind_result($codigo_subInforme, $nombre_subInforme);
			while($resultado7->fetch()){
				$tpl->newBlock("NOMBRE_SUB_INFORME");
				$tpl->assign("CODIGO_SUB_INFORME",$codigo_subInforme);
				$tpl->assign("INFORMACION_SUB_INFORME", $nombre_subInforme);
			}				
			//Cerramos resultados
			$resultado5->close();
			$resultado6->close();
			$resultado7->close();			
		}
		//Cerramos la conexión
		$resultado->close();
		$resultado2->close();		
		$conexion_db->close();
		//Se muestra la interfaz
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario	
		$boton = $_POST["cambiar"];
		$codigoContrato = $_SESSION["CODIGO_CONTRATO"];
		$codigoInforme = $_POST["nombreInforme"];
		$codigoSubInforme = $_POST["nombreSubInforme"]; 
		
		//Banderas de control
		$asociado = 0;
		$errorRuta = 0;
		
		if(strcmp($_POST["cambiar"],"ASOCIAR") == 0){			
			//Recorremos los sub informes que se van a asociar
			for($i=0;$i<count($codigoSubInforme);$i++){
				//Verificamos que el sub Informe ya no este asociado al informe y al contrato
				$consulta = "select * from subInformeInforme where contrato_subInformeInforme = ".$codigoContrato." and informe_subInformeInforme = ".
				$codigoInforme." and subInforme_subInformeInforme = ".$codigoSubInforme[$i];
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				//El sub informe no esta asociado al informe
				if($resultado->num_rows == 0){	
								
					//Obtenemos el nombre del informe y su estado
					$consulta2 = "select nombre_informe, estado_informe from informe where codigo_informe = ".$codigoInforme;
					$resultado2 = $conexion_db->prepare($consulta2);
					$resultado2->execute();
					$resultado2->store_result();
					$resultado2->bind_result($nombre_informe, $estado_informe);
					$resultado2->fetch();
					//Verificamos si esta disabled					
					$nombre_informe = mb_strtoupper(sanear_string(html_entity_decode($nombre_informe)),'UTF-8');
					if($estado_informe == 0){
						$nombre_informe = $nombre_informe."_DISABLED";
					}
					
					//Obtenermos el nombre del subInforme y su estado
					$consulta3 = "select nombre_subInforme, estado_subInforme from subInforme where codigo_subInforme = ".$codigoSubInforme[$i];
					$resultado3 = $conexion_db->prepare($consulta3);
					$resultado3->execute();
					$resultado3->store_result();
					$resultado3->bind_result($nombre_subInforme, $estado_subInforme);
					$resultado3->fetch();	
					//Verificamos si esta disabled
					$nombre_subInforme = mb_strtoupper(sanear_string(html_entity_decode($nombre_subInforme)),'UTF-8');
					if($estado_subInforme == 0){
						$nombre_subInforme = $nombre_subInforme."_DISABLED";
					}
					
					//Obtenemos el directorio del contrato
					$consulta4 = "select directorio_contrato from contrato where id_contrato = ".$codigoContrato;
					$resultado4 = $conexion_db->prepare($consulta4);
					$resultado4->execute();
					$resultado4->store_result();
					$resultado4->bind_result($directorio_contrato);
					$resultado4->fetch();
					
					//Generamos la ruta
					$ruta = $directorio_contrato."/".$nombre_informe."/".$nombre_subInforme;
					
					//Creamos el direcctorio					
					if(!mkdir($ruta, 0777, true)){
						$errorRuta++;						
					}					
					//Guardamos la información en la base de datos
					$consulta5 = "insert into subinformeinforme (subInforme_subInformeInforme, informe_subInformeInforme, contrato_subInformeInforme) ".
					" values (".$codigoSubInforme[$i].", ".$codigoInforme.", ".$codigoContrato.")";
					$resultado5 = $conexion_db->prepare($consulta5);
					$resultado5->execute();
					
					//Recorremos el directorio buscando el informe para crear el subinforme
					/*$dir = opendir("../../documentos/");						
					while($recorrido = readdir($dir)){
						if($recorrido != "." && $recorrido != ".."){
							if(is_dir("../../documentos/".$recorrido)){
								if(file_exists("../../documentos/".$recorrido."/".$nombre_informe)){										
									$ruta = "../../documentos/".$recorrido."/".$nombre_informe."/".$nombre_subInforme;
									
								}
							}
						}
					}
					*/
				}
				else{
					//El sub informe esta asociado al informe
					$asociado++;
				}				
			}
			
			if($errorRuta == count($codigoSubInforme)){
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }			
				if(isset($resultado5)){ $resultado5->close(); }			
				$conexion_db->close();
				$_SESSION["TEMP_DIR_ASOCIADO"] = "SI";
				header("Location: asociarSubInformeInforme.php");					
			}
			else if($asociado == count($codigoSubInforme)){
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }			
				if(isset($resultado5)){ $resultado5->close(); }			
				$conexion_db->close();
				$_SESSION["TEMP_NO_ASOCIADO"] = "SI";
				header("Location: asociarSubInformeInforme.php");					
			}
			else if($errorRuta > 0 || $asociado > 0){
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }	
				if(isset($resultado5)){ $resultado5->close(); }					
				$conexion_db->close();
				$_SESSION["TEMP_CASI_ASOCIADO"] = "SI";
				header("Location: asociarSubInformeInforme.php");					
			}
			else{
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }	
				if(isset($resultado5)){ $resultado5->close(); }					
				$conexion_db->close();
				$_SESSION["TEMP_ASOCIADO"] = "SI";
				header("Location: asociarSubInformeInforme.php");					
			}
		}
		else{	//Eliminamos los subinformes
			//Variable control
			$control = 0;
			//Se recorre los subInformes para buscarlos
			for($i=0;$i<count($codigoSubInforme);$i++){
				//Verificamos que el sub-informe este asociado al informe y al contrato
				$consulta = "select * from subInformeInforme where subInforme_subInformeInforme = ".$codigoSubInforme[$i].
				" and informe_subInformeInforme = ".$codigoInforme." and contrato_subInformeInforme = ".$codigoContrato;
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				if($resultado->num_rows == 0){					 
					$control++;
				}
				else{
					//Obtenemos el nombre del informe y su estado
					$consulta2 = "select nombre_informe, estado_informe from informe where codigo_informe = ".$codigoInforme;
					$resultado2 = $conexion_db->prepare($consulta2);
					$resultado2->execute();
					$resultado2->store_result();
					$resultado2->bind_result($nombre_informe, $estado_informe);
					$resultado2->fetch();
					//Verificamos si esta disabled					
					$nombre_informe = mb_strtoupper(sanear_string(html_entity_decode($nombre_informe)),'UTF-8');
					if($estado_informe == 0){
						$nombre_informe = $nombre_informe."_DISABLED";
					}
					//Obtenermos el nombre del subInforme y su estado
					$consulta3 = "select nombre_subInforme, estado_subInforme from subInforme where codigo_subInforme = ".$codigoSubInforme[$i];
					$resultado3 = $conexion_db->prepare($consulta3);
					$resultado3->execute();
					$resultado3->store_result();
					$resultado3->bind_result($nombre_subInforme, $estado_subInforme);
					$resultado3->fetch();	
					//Verificamos si esta disabled
					$nombre_subInforme = mb_strtoupper(sanear_string(html_entity_decode($nombre_subInforme)),'UTF-8');
					if($estado_subInforme == 0){
						$nombre_subInforme = $nombre_subInforme."_DISABLED";
					}
					
					//Obtenemos el directorio del contrato
					$consulta4 = "select directorio_contrato from contrato where id_contrato = ".$codigoContrato;
					$resultado4 = $conexion_db->prepare($consulta4);
					$resultado4->execute();
					$resultado4->store_result();
					$resultado4->bind_result($directorio_contrato);
					$resultado4->fetch();
					
					//Generamos la ruta
					$ruta = $directorio_contrato."/".$nombre_informe."/".$nombre_subInforme;
					
					//Eliminamos el directorio
					deleteDirectory($ruta);					
					
					//Recorremos el directorio buscando el informe para crear el subinforme
					/*$dir = opendir("../../documentos/");						
					while($recorrido = readdir($dir)){
						if($recorrido != "." && $recorrido != ".."){
							if(is_dir("../../documentos/".$recorrido)){
								if(file_exists("../../documentos/".$recorrido."/".$nombre_informe."/".$nombre_subInforme)){										
									$ruta = "../../documentos/".$recorrido."/".$nombre_informe."/".$nombre_subInforme;
									deleteDirectory($ruta);							
								}							
							}
						}
					}*/
					//Eliminamos la información en la base de datos
					$consulta5 = "delete from subInformeInforme where subInforme_subInformeInforme = ".$codigoSubInforme[$i].
					" and informe_subInformeInforme = ".$codigoInforme." and contrato_subInformeInforme = ".$codigoContrato;
					$resultado5 = $conexion_db->prepare($consulta5);
					$resultado5->execute();
				}
			}		
			//Liberar variables, conexión e indicar mensajes			
			if($control == count($codigoSubInforme)){
				if(isset($resultado)){ $resultado->close(); }
				$conexion_db->close();
				$_SESSION["TEMP_ERROR_DESASOCIAR"] = "SI";
				header("Location: asociarSubInformeInforme.php");
			}
			else if($control > 0 and $control != count($codigoSubInforme)){
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }
				if(isset($resultado5)){ $resultado5->close(); }				
				$conexion_db->close();
				$_SESSION["TEMP_CASI_ERROR_DESASOCIAR"] = "SI";
				header("Location: asociarSubInformeInforme.php");
			}
			else if($control == 0){
				if(isset($resultado)){ $resultado->close(); }
				if(isset($resultado2)){ $resultado2->close(); }
				if(isset($resultado3)){ $resultado3->close(); }
				if(isset($resultado4)){ $resultado4->close(); }
				if(isset($resultado5)){ $resultado5->close(); }				
				$conexion_db->close();
				$_SESSION["TEMP_DESASOCIADO"] = "SI";
				header("Location: asociarSubInformeInforme.php");
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
		$string = str_replace(" ", "_", $string);
	   	return $string;
	}
	
	function deleteDirectory($dir){
    	if(!$dh = @opendir($dir)) return;
    	while(false !== ($current = readdir($dh))){
        	if($current != '.' && $current != '..'){            	
            	if(!@unlink($dir.'/'.$current)) 
                	deleteDirectory($dir.'/'.$current);
       		}       
    	}
	   	closedir($dh);    	
    	@rmdir($dir);	
	}
?>