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
				
		$tpl = new TemplatePower("../../interfaz/HTML/asociarContratoInforme.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");

		if(isset($_SESSION["TEMP_TODO_ASOCIADO"]) and $_SESSION["TEMP_TODO_ASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: INFORMES YA ESTABAN ASOCIADOS");
			unset($_SESSION["TEMP_TODO_ASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_ERROR_DIRECTORIO"]) and $_SESSION["TEMP_ERROR_DIRECTORIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: NO SE GENERO EL DIRECTORIO");
			unset($_SESSION["TEMP_ERROR_DIRECTORIO"]);
		}
		if(isset($_SESSION["TEMP_ALGUNOS_ASOCIADO"]) and $_SESSION["TEMP_ALGUNOS_ASOCIADO"]== "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: ALGUNOS INFORMES YA ESTABAN ASOCIADOS");
			unset($_SESSION["TEMP_ALGUNOS_ASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_ALGUNOS_DIRECTORIO"]) and $_SESSION["TEMP_ALGUNOS_DIRECTORIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: NO SE GENERO ALGUNOS DIRECTORIOS");
			unset($_SESSION["TEMP_ALGUNOS_DIRECTORIO"]);
		}
		if(isset($_SESSION["TEMP_OK_ASOCIACION"]) and $_SESSION["TEMP_OK_ASOCIACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ASOCIACIÓN CORRECTA");
			unset($_SESSION["TEMP_OK_ASOCIACION"]);
		}		
		if(isset($_SESSION["TEMP_ERROR_DESASOCIAR"]) and $_SESSION["TEMP_ERROR_DESASOCIAR"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: NO SE PUDO DESASOCIAR LOS INFORMES");
			unset($_SESSION["TEMP_ERROR_DESASOCIAR"]);
		}		
		if(isset($_SESSION["TEMP_CASI_ERROR_DESASOCIAR"]) and $_SESSION["TEMP_CASI_ERROR_DESASOCIAR"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: ALGUNOS INFORMES NO SE DESASOCIARON");
			unset($_SESSION["TEMP_CASI_ERROR_DESASOCIAR"]);
		}		
		if(isset($_SESSION["TEMP_DESASOCIADO"]) and $_SESSION["TEMP_DESASOCIADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","DESASOCIACIÓN CORRECTA");
			unset($_SESSION["TEMP_DESASOCIADO"]);
		}
		if(isset($_SESSION["TEMP_NO_DIRECTORIO"]) and $_SESSION["TEMP_NO_DIRECTORIO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","ERROR: INFORME NO ASOCIADO AL CONTRATO");
			unset($_SESSION["TEMP_NO_DIRECTORIO"]);
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
				
		//Obtenemos los contratos
		$tpl->gotoBlock("_ROOT");
		$consulta3 = "select id_contrato, nombreCorto_contrato from contrato";
		$resultado3 = $conexion_db->prepare($consulta3);
		$resultado3->execute();
		$resultado3->store_result();
		$resultado3->bind_result($id_contrato, $nombreCorto_contrato);
		while($resultado3->fetch()){
			$tpl->newBlock("NOMBRE_CONTRATO");   
			$tpl->assign("CODIGO_CONTRATO",$id_contrato);
			$tpl->assign("INFORMACION_CONTRATO",$nombreCorto_contrato);			
		}		
		
		//Obtenemos los informes
		$tpl->gotoBlock("_ROOT");
		$consulta4 = "select codigo_informe, nombre_informe from informe";
		$resultado4 = $conexion_db->prepare($consulta4);
		$resultado4->execute();
		$resultado4->bind_result($codigo_informe, $nombre_informe);
		while($resultado4->fetch()){
			$tpl->newBlock("NOMBRE_INFORME");
			$tpl->assign("CODIGO_INFORME",$codigo_informe);
			$tpl->assign("INFORMACION_INFORME", $nombre_informe);
		}
		
		//Cerramos la conexión
		$resultado->close();
		$resultado2->close();		
		$resultado3->close();
		$resultado4->close();
		$conexion_db->close();
		//Se muestra la interfaz
		$tpl->printToScreen();
	}
	else{
		//Informacion del formulario
		$boton = $_POST["cambiar"];
		$codigoContrato = $_POST["nombreContrato"];
		$codigoInforme = $_POST["nombreInforme"]; 
		//Banderas de control
		$asociado = 0;
		$errorRuta = 0;
		if(strcmp($_POST["cambiar"],"ASOCIAR") == 0){
			//Se realiza la asociación		
			for($i=0;$i<count($codigoInforme);$i++){
				//Verificamos si ya esta asociado el informe
				$consulta = "select * from informecontrato where contrato_informeContrato = ".$codigoContrato." and informe_informeContrato = ".
				$codigoInforme[$i];
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				//No esta asociado
				if($resultado->fetch() == 0){
					//Se crea el directorio fisicamente
					//Obtenemos el directorio del contrato
					$consulta2 = "select directorio_contrato from contrato where id_contrato = ".$codigoContrato;
					$resultado2 = $conexion_db->prepare($consulta2);
					$resultado2->execute();
					$resultado2->store_result();								
					$resultado2->bind_result($directorio_contrato);
					$resultado2->fetch();
					//Obtenemos el nombre del informe
					$consulta3 = "select nombre_informe, estado_informe from informe where codigo_informe = ".$codigoInforme[$i];
					$resultado3 = $conexion_db->prepare($consulta3);
					$resultado3->execute();				
					$resultado3->store_result();								
					$resultado3->bind_result($nombre_informe,$estado_informe);
					$resultado3->fetch();
					//Verificamos si esta disabled
					$nombre_informe = sanear_string($nombre_informe);
					if($estado_informe == 0){
						$nombre_informe = mb_strtoupper(sanear_string(html_entity_decode($nombre_informe)),'UTF-8');
						$nombre_informe = $nombre_informe."_DISABLED";
					}
					else{
						$nombre_informe = mb_strtoupper(sanear_string(html_entity_decode($nombre_informe)),'UTF-8');
					}
					
					//Nombre del directorio					
					$ruta = $directorio_contrato."/".$nombre_informe;
					//Generamos el directorio
					if(!mkdir($ruta, 0777, true)){
						$errorRuta++;
					}
					else{
						//El directorio se creo correctamente
						
						//Verificamos que el informe tenga asociado algun sub-informe
						/*$consulta4 = "select subInforme_subInformeInforme from subInformeInforme where informe_subInformeInforme = ".
						$codigoInforme[$i];
						$resultado4 = $conexion_db->prepare($consulta4);
						$resultado4->execute();
						$resultado4->store_result();
						if($resultado4->num_rows > 0){
							$resultado4->bind_result($subInforme_subInformeInforme);
							while($resultado4->fetch()){
								//Obtenemos el nombre del subInforme
								$consulta5 = "select nombre_subInforme, estado_subInforme from subInforme where codigo_subInforme = ".
								$subInforme_subInformeInforme;
								$resultado5 = $conexion_db->prepare($consulta5);
								$resultado5->execute();
								$resultado5->store_result();
								$resultado5->bind_result($nombre_subInforme,$estado_subInforme);
								$resultado5->fetch();
								//Verificamos si esta disabled
								$nombre_subInforme = sanear_string($nombre_subInforme);
								if($estado_subInforme == 0){
									$nombre_subInforme = mb_strtoupper(sanear_string(html_entity_decode($nombre_subInforme)),'UTF-8');
									$nombre_subInforme = $nombre_subInforme."_DISABLED";
								}
								else{
									$nombre_subInforme = mb_strtoupper(sanear_string(html_entity_decode($nombre_subInforme)),'UTF-8');
								}
								//Nombre del directorio					
								$ruta2 = $directorio_contrato."/".$nombre_informe."/".$nombre_subInforme;
								//Generamos el directorio
								if(!mkdir($ruta2, 0777, true)){
									$errorRuta++;
								}								
							}
						}*/
						//Se almacena la información en la base de datos
						$consulta6 = "insert into informecontrato (contrato_informeContrato, informe_informeContrato, ".
						"ruta_informeContrato) values (".$codigoContrato.", ".$codigoInforme[$i].", '".$ruta."')";
						$resultado6 = $conexion_db->prepare($consulta6);
						$resultado6->execute();												
					}
				}
				//Esta asociado
				else{
					$asociado++;
				}	
			}			
			//Se cierran las conexiones						
			if($asociado != 0 || $errorRuta != 0){
				if($asociado == count($codigoInforme)){
					$resultado->close();
					$conexion_db->close();
					$_SESSION["TEMP_TODO_ASOCIADO"] = "SI";
					header("Location: asociarContratoInforme.php");
				}
				if($errorRuta == count($codigoInforme)){
					$resultado->close();
					$resultado2->close();
					$resultado3->close();
					$conexion_db->close();
					$_SESSION["TEMP_ERROR_DIRECTORIO"] = "SI";
					header("Location: asociarContratoInforme.php");
				}
				if($asociado < count($codigoInforme)){
					$resultado->close();
					$resultado2->close();
					$resultado3->close();
					if(isset($resultado4)){
						$resultado4->close();
					}
					$conexion_db->close();
					$_SESSION["TEMP_ALGUNOS_ASOCIADO"] = "SI";
					header("Location: asociarContratoInforme.php");
				}
				if($errorRuta < count($codigoInforme)){
					$resultado->close();
					$resultado2->close();
					$resultado3->close();
					if(isset($resultado4)){
						$resultado4->close();
					}
					$conexion_db->close();
					$_SESSION["TEMP_ALGUNOS_DIRECTORIO"] = "SI";
					header("Location: asociarContratoInforme.php");
				}
			}
			else{
				$resultado->close();
				$resultado2->close();
				$resultado3->close();
				//$resultado4->close();
				$conexion_db->close();
				$_SESSION["TEMP_OK_ASOCIACION"] = "SI";
				header("Location: asociarContratoInforme.php");
			}				
		}
		else{
			//Variable control
			$control = 0;
			//Se elimina el directorio completo junto con sus subdirectorios y archivos			
			for($i=0;$i<count($codigoInforme);$i++){
				//Buscamos el directorio a eliminar
				$consulta = "select ruta_informeContrato from informeContrato where informe_informeContrato = ".$codigoInforme[$i].
				" and contrato_informeContrato = ".$codigoContrato;
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				$resultado->bind_result($ruta_informeContrato);
				$resultado->fetch();
				if(empty($ruta_informeContrato)){
					$resultado->close();
					$conexion_db->close();
					$_SESSION["TEMP_NO_DIRECTORIO"] = "SI";
					header("Location: asociarContratoInforme.php");
				}
				else{
					//Se elimina el directorio
					deleteDirectory($ruta_informeContrato);
					//Se modifica la base de datos
					if(!file_exists($ruta_informeContrato)){
						$consulta2 = "delete from informeContrato where informe_informeContrato = ".$codigoInforme[$i]." and contrato_informeContrato = ".
						$codigoContrato;
						$resultado2 = $conexion_db->prepare($consulta2);
						$resultado2->execute();
					}
					else{
						$control++;
					}
				}
			}
			if($control == count($codigoInforme)){
				$resultado->close();
				$conexion_db->close();
				$_SESSION["TEMP_ERROR_DESASOCIAR"] = "SI";
				header("Location: asociarContratoInforme.php");
			}
			else if($control > 0 and $control != count($codigoInforme)){
				$resultado->close();
				$resultado2->close();
				$conexion_db->close();
				$_SESSION["TEMP_CASI_ERROR_DESASOCIAR"] = "SI";
				header("Location: asociarContratoInforme.php");
			}
			else if($control == 0){
				$resultado->close();
				$resultado2->close();
				$conexion_db->close();
				$_SESSION["TEMP_DESASOCIADO"] = "SI";
				header("Location: asociarContratoInforme.php");
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