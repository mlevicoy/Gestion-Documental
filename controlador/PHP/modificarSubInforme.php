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
				
		$tpl = new TemplatePower("../../interfaz/HTML/modificarSubInforme.html");
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
			$tpl->assign("MENSAJE","SUB-INFORME HABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_HABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]) and $_SESSION["TEMP_DESHABILITAR_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");		
			$tpl->assign("MENSAJE","SUB-INFORME DESHABILITADO CORRECTAMENTE");
			unset($_SESSION["TEMP_DESHABILITAR_CORRECTA"]);
		}
		if(isset($_SESSION["TEMP_ACTUALIZACION_CORRECTA"]) and $_SESSION["TEMP_ACTUALIZACION_CORRECTA"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","block");
			$tpl->assign("SALTO_LINEA","<br/><br/>");
			$tpl->assign("MENSAJE","SUB-INFORME ACTUALIZADO CORRECTAMENTE");
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
			$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR SUB-DOCUMENTO ---");
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "");			
			$tpl->assign("HABILITAR_DESHABILIITAR", "HABILITAR");

			/* Se buscan los subInformes */
			$consulta3 = "SELECT codigo_subInforme, nombre_subInforme FROM subInforme";
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->bind_result($codigo_subInforme, $nombre_subInforme);
			while($resultado3->fetch()){
				$tpl->newBlock("SUB_INFORME_BUSCAR");
				$tpl->assign("CODIGO_SUBINFORME", $codigo_subInforme);
				$tpl->assign("NOMBRE_SUBINFORME", $nombre_subInforme);				
			}			
			$resultado3->close();			
		}
		else{
			//ID del contrato
			$id_subInforme = $_GET["id"];			
			//Enable los controles input y select			
			$tpl->assign("VALOR1","jsformulario");
			$tpl->assign("VALOR2", 0);
			$tpl->assign("VALOR3",1);			
			$tpl->assign("SELECT_TERMINO","0");
			$tpl->assign("VALOR_SELECT_TERMINO", "--- SELECCIONAR SUB-DOCUMENTO ---");			
			//llenamos nuevamente el select			
			$consulta4 = "SELECT codigo_subInforme, nombre_subInforme, descripcion_subInforme, estado_subInforme FROM subInforme";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			$resultado4->bind_result($codigo_subInforme, $nombre_subInforme, $descripcion_subInforme, $estado_subInforme);
			while($resultado4->fetch()){
				if(strcmp($id_subInforme,$codigo_subInforme) != 0){
					$tpl->newBlock("SUB_INFORME_BUSCAR");
					$tpl->assign("CODIGO_SUBINFORME",$codigo_subInforme);
					$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);				
				}
				else{
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("SELECT_INICIO",$codigo_subInforme);
					$tpl->assign("VALOR_SELECT_INICIO",$nombre_subInforme);					
				}
			}
			$resultado4->close();						

			//Obtenemos la información del contrato
			$consulta5 = "select * from subInforme where codigo_subInforme = ".$id_subInforme;
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			$resultado5->bind_result($codigo_subInforme, $nombre_subInforme, $descripcion_subInforme, $estado_subInforme);
			$resultado5->fetch();
			
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALOR_NOMBRE_SUBINFORME", $nombre_subInforme);
			$tpl->assign("VALOR_TEXTAREA_DESCRIPCION", $descripcion_subInforme);			
			$tpl->assign("ESTADO3", "");
			$tpl->assign("VALOR_ESTADO3", "--- SELECCIONAR ESTADO ---");
			if($estado_subInforme == 0){
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
			if($estado_subInforme == 0){
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
		$subInformeID = $_POST["subInformeBuscar"];		
			
		//OPCIÓN HABILITAR
		if(strcmp($boton,"HABILITAR") == 0){			
			//Obtenemos el nombre del sub informe
			//$consulta = "select nombre_subInforme from subInforme where codigo_subInforme = ".$subInformeID;
            $consulta = "select nombreSaneado_subInforme from subInforme where codigo_subInforme = ".$subInformeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombreSaneado_subInforme);
			$resultado->fetch();
			
			//Informe enable
			//$nombre_subInforme = sanear_string($nombre_subInforme);
			//$informeModificado = $nombre_informe."_DISABLED";
	
			//Habilitar el informe
			$consulta2 = "update subInforme set estado_subInforme = 1 where codigo_subInforme = ".$subInformeID;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			
			//Recorremos los contratos buscando el sub informe para habilitar
			$dir = opendir("../../documentos/");						
			while($recorrido = readdir($dir)){
				if($recorrido != "." && $recorrido != ".."){
					if(is_dir("../../documentos/".$recorrido)){
						$dir2 = opendir("../../documentos/".$recorrido);						
						while($recorrido2 = readdir($dir2)){
							if($recorrido2 != "." && $recorrido2 != ".."){
								if(is_dir("../../documentos/".$recorrido."/".$recorrido2)){
									if(file_exists("../../documentos/".$recorrido."/".$recorrido2."/".$nombreSaneado_subInforme."_DISABLED")){										
										rename("../../documentos/".$recorrido."/".$recorrido2."/".$nombreSaneado_subInforme."_DISABLED", "../../documentos/".
										$recorrido."/".$recorrido2."/".$nombreSaneado_subInforme);
									}
								}
							}
						}				
					}
				}
			}

			//Cerrar variables de co1nexión a DB
			$resultado->close();
			$resultado2->close();
			$conexion_db->close();
			
			//Redireccionamos
			$_SESSION["TEMP_HABILITAR_CORRECTA"] = "SI";
			header("Location: modificarSubInforme.php");
		}		
		//OPCION DESHABILITAR
		if(strcmp($boton,"DESHABILITAR") == 0){
			//Obtenemos el nombre del subinforme
			//$consulta = "select nombre_subInforme from subInforme where codigo_subInforme = ".$subInformeID;
            $consulta = "select nombreSaneado_subInforme from subInforme where codigo_subInforme = ".$subInformeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombreSaneado_subInforme);
			$resultado->fetch();
		
			//Sub Informe disabled
			//$nombre_subInforme = sanear_string($nombre_subInforme);
			$subInformeModificado = $nombreSaneado_subInforme."_DISABLED";
			
			//Deshabilitar el Sub Informe
			$consulta2 = "update subInforme set estado_subInforme = 0 where codigo_subInforme = ".$subInformeID;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
		
			//Recorremos los contratos buscando el sub informe para deshabilitar
			$dir = opendir("../../documentos/");						
			while($recorrido = readdir($dir)){
				if($recorrido != "." && $recorrido != ".."){
					if(is_dir("../../documentos/".$recorrido)){
						$dir2 = opendir("../../documentos/".$recorrido);						
						while($recorrido2 = readdir($dir2)){
							if($recorrido2 != "." && $recorrido2 != ".."){
								if(is_dir("../../documentos/".$recorrido."/".$recorrido2)){
									if(file_exists("../../documentos/".$recorrido."/".$recorrido2."/".$nombreSaneado_subInforme)){										
										rename("../../documentos/".$recorrido."/".$recorrido2."/".$nombreSaneado_subInforme, "../../documentos/".
										$recorrido."/".$recorrido2."/".$subInformeModificado);
									}
								}
							}
						}				
					}
				}
			}

			//Cerrar variables de co1nexión a DB
			$resultado->close();
			$resultado2->close();
			$conexion_db->close();

			//Redireccionamos
			$_SESSION["TEMP_DESHABILITAR_CORRECTA"] = "SI";
			header("Location: modificarSubInforme.php");
		}		
		//OPCIÓN ACTUALIZAR
		else if(strcmp($boton,"ACTUALIZAR") == 0){
			//Informacion del formulario
			$nombreSubInforme = htmlentities(mb_strtoupper(trim($_POST["nombreSubInforme"]),'UTF-8')); 
			$descripcionSubInforme = htmlentities(mb_strtoupper(trim($_POST["descripcionSubInforme"]),'UTF-8')); 					
			$estado = $_POST["estado"];		
			
			//Escapamos
			$nombreSubInforme = mysqli_real_escape_string($conexion_db, $nombreSubInforme);
			$descripcionSubInforme = mysqli_real_escape_string($conexion_db, $descripcionSubInforme);
            $nombreSubInformeSaneado = mb_strtoupper(sanear_string($_POST["nombreSubInforme"]),'UTF-8');

			//Obtenemos el nombre original del sub informe y su estado
			$consulta = "select nombre_subInforme, nombreSaneado_subInforme, estado_subInforme from subInforme where codigo_subInforme = ".$subInformeID;
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombre_subInforme, $nombreSaneado_subInforme, $estado_subInforme);
			$resultado->fetch();
			
			$nombre_subInforme_buscar_aux = $nombreSaneado_subInforme;	
			$estado_subInforme_aux = $estado_subInforme;
			
			
			//Verificamos que el nombre no exista
			$consulta2 = "select * from subInforme where codigo_subInforme <> ".$subInformeID." and nombre_subInforme = '".$nombreSubInforme."'";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();					
			if($resultado2->num_rows == 0){
				//Se realiza la modificación
				$consulta3 = "update subInforme set nombre_subInforme = '".$nombreSubInforme."', nombreSaneado_subInforme = '".$nombreSubInformeSaneado.
                "', descripcion_subInforme = '".$descripcionSubInforme."', estado_subInforme = ".$estado." where codigo_subInforme = ".$subInformeID;				
				$resultado3 = $conexion_db->prepare($consulta3);										
				$resultado3->execute();
								
				//Nombre a buscar
				if($estado_subInforme_aux == 1){ $nombre_subInforme_buscar = $nombre_subInforme_buscar_aux; }
				else if($estado_subInforme_aux == 0){ $nombre_subInforme_buscar = $nombre_subInforme_buscar_aux."_DISABLED"; }
				
				//Verificamos el disabled
				if($estado == 1){ $nombreSubInforme = $nombreSubInformeSaneado; }
				else if($estado == 0){ $nombreSubInforme = $nombreSubInformeSaneado."_DISABLED"; }
				
				
				//Recorremos los contratos buscando el sub informe para deshabilitar
				$dir = opendir("../../documentos/");						
				while($recorrido = readdir($dir)){
					if($recorrido != "." && $recorrido != ".."){
						if(is_dir("../../documentos/".$recorrido)){
							$dir2 = opendir("../../documentos/".$recorrido);						
							while($recorrido2 = readdir($dir2)){
								if($recorrido2 != "." && $recorrido2 != ".."){
									if(is_dir("../../documentos/".$recorrido."/".$recorrido2)){
										if(file_exists("../../documentos/".$recorrido."/".$recorrido2."/".$nombre_subInforme_buscar)){										
											rename("../../documentos/".$recorrido."/".$recorrido2."/".$nombre_subInforme_buscar, "../../documentos/".
											$recorrido."/".$recorrido2."/".$nombreSubInforme);
										}
									}
								}
							}				
						}
					}
				}
				
				//Cerramos las conexiones					
				$resultado->close();
				$resultado2->close();
				$resultado3->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_ACTUALIZACION_CORRECTA"] = "SI";
				header("Location: modificarSubInforme.php");
			}				
			//Se repite el nombre, no se puede realizar la modificacion
			else{
				$resultado->close();
				$conexion_db->close();
				//Redireccionamos
				$_SESSION["TEMP_ACTUALIZACION_INCORRECTA"] = "SI";
				header("Location: modificarSubInforme.php");
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