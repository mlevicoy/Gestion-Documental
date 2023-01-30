<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	//Carga inicial y redireccionamiento
	if(!isset($_POST["cargador"])){
		//Cargamos la página	
		$tpl = new TemplatePower("../../interfaz/HTML/modificarDocumento.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);		
		/*if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="trabajarDocumento.php">Regresar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="trabajarDocumento.php">Regresar</a>');
		}*/
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
		}
		else{
            $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
		}
		//Vemos las variables $_GET					
		if(!isset($_GET["iddoc"]) and !isset($_GET["campo"])){ 
			header("Location: sistema.php"); 			
		}
		else{
			//Ingresamos la informacion
			if(isset($_GET["iddoc"]) and !isset($_GET["campo"])){
				//Informacion de la otra página: id del documento
				$_SESSION["ID_DOCUMENTO_ORIGINAL"] = $_GET["iddoc"];
				
				//Obtenemos la informacion de la base de datos
				$consulta = "select contrato_documento, informe_documento, subInforme_documento, numero_documento, fecha_documento, hoja_documento, ".
				"destinatario_documento, descripcion_documento, nombre_documento, ruta_documento from documento where codigo_documento = ".
				$_SESSION["ID_DOCUMENTO_ORIGINAL"];
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();
				$resultado->bind_result($contrato_documento, $informe_documento, $subInforme_documento, $numero_documento, $fecha_documento, $hoja_documento,
				$destinatario_documento, $descripcion_documento, $nombre_documento, $ruta_documento);
				$resultado->fetch();
				
				/*Generamos las variables de sesion del informe originar a modificar*/
				$_SESSION["ID_CONTRATO_DOCUMENTO_ORIGINAL"] = $contrato_documento;
				$_SESSION["ID_INFORME_DOCUMENTO_ORIGINAL"] =  $informe_documento;
				$_SESSION["ID_SUBINFORME_DOCUMENTO_ORIGINAL"] = $subInforme_documento;
				$_SESSION["ID_NUMERO_DOCUMENTO_ORIGINAL"] = $numero_documento;
				$_SESSION["FECHA_DOCUMENTO_ORIGINAL"] = $fecha_documento;
				$_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"] = $hoja_documento;
				$_SESSION["ID_DESTINATARIO_DOCUMENTO_ORIGINAL"] = $destinatario_documento;
				$_SESSION["DESCRIPCION_DOCUMENTO_ORIGINAL"] = $descripcion_documento;
				$_SESSION["NOMBRE_DOCUMENTO_ORIGINAL"] = $nombre_documento;
				$_SESSION["RUTA_DOCUMENTO_ORIGINAL"] = $ruta_documento;
				
				//Buscamos los contratos que esten asociados al usuario y no disabed
				$tpl->assign("VALOR_CONTRATO_TERMINO", "");
				$tpl->assign("CONTRATO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
			
				$consulta2 = "select contrato.id_contrato, contrato.nombreCorto_contrato from contrato inner join usuarioContrato on ".
				"contrato.id_contrato = usuarioContrato.contrato_usuarioContrato and contrato.habilitado_contrato = 1 and usuario_usuarioContrato = ".
				$_SESSION["IDENTIFICADOR_USUARIO"];
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($id_contrato, $nombreCorto_contrato);
				while($resultado2->fetch()){
					if($id_contrato == $contrato_documento){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_CONTRATO_INICIO",$id_contrato);
						$tpl->assign("CONTRATO_INICIO",$nombreCorto_contrato);
					}
					else{
						$tpl->newBlock("DESCRIPCION_CONTRATO"); 
						$tpl->assign("ID_CONTRATO",$id_contrato);
						$tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);						
					}
				}
				
				//Buscamos los informes asociados al contrato
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("VALOR_INFORME_TERMINO", ""); 
				$tpl->assign("INFORME_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
				$consulta3 = "select informe.codigo_informe, informe.nombre_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informe.estado_informe = 1 and informeContrato.contrato_informeContrato = ".
				$contrato_documento; 
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$resultado3->bind_result($codigo_informe, $nombre_informe);
				while($resultado3->fetch()){
					if($codigo_informe == $informe_documento){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_INICIO",$codigo_informe);
						$tpl->assign("INFORME_INICIO",$nombre_informe);
					}
					else{
						$tpl->newBlock("DESCRIPCION_INFORME"); 
						$tpl->assign("ID_INFORME",$codigo_informe);
						$tpl->assign("NOMBRE_INFORME",$nombre_informe);						
					}
				}
				
				//Buscamos los subInformes asociados al informe
				$na = 0;
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("VALOR_SUBINFORME_TERMINO", ""); 
				$tpl->assign("SUBINFORME_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
				$consulta4 = "select subInforme.codigo_subInforme, subInforme.nombre_subInforme from subInforme inner join subInformeInforme on ".
				"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme and subInforme.estado_subInforme = 1 and ".
				"informe_subInformeInforme = ".$informe_documento." and subInformeInforme.contrato_subInformeInforme = ".$contrato_documento;
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($codigo_subInforme, $nombre_subInforme);
				while($resultado4->fetch()){
					if($subInforme_documento == -1){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_SUBINFORME_INICIO",-1);
						$tpl->assign("SUBINFORME_INICIO","NO APLICA");
						$na = 1;
					}
					else if($codigo_subInforme == $subInforme_documento){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_SUBINFORME_INICIO",$codigo_subInforme);
						$tpl->assign("SUBINFORME_INICIO",$nombre_subInforme);
					}
					else{
						$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
						$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
						$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);						
					}
				}
				if($na == 0){
					$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
					$tpl->assign("ID_SUBINFORME",-1);
					$tpl->assign("NOMBRE_SUBINFORME","NO APLICA");						
				}
				
				//Buscamos los numero de documentos disponibles	
				//Buscamos los número ya usados				
				$consulta7 = "SELECT numero_documento FROM documento WHERE contrato_documento=".$contrato_documento." and informe_documento=".
				$informe_documento." and subInforme_documento=".$subInforme_documento;				
				$resultado7 = $conexion_db->prepare($consulta7);
				$resultado7->execute();
				$resultado7->store_result();
				//Si no hay documentos de ese sub-directorio
				if($resultado7->num_rows == 0){
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_NUMERO_INICIO", "");
					$tpl->assign("NUMERO_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
					//Presentamos los datos
					for($i=1;$i<200;$i++){						
						$tpl->newBlock("DESCRIPCION_NUMERO");
						$tpl->assign("ID_NUMERO",$i);
						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
					}
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_NUMERO_TERMINO",$i);
					$tpl->assign("NUMERO_TERMINO","DOCUMENTO N°".$i);				
				}
				//Si hay documentos en ese sub-directorio
				else{
					//Creamos el array
					for($i=0;$i<200;$i++){
						$numero[$i] = $i+1;
					}
					
					//Asignamos el resultado a las variables		
					$resultado7->bind_result($numero_documento);				
					//Recorremos las variable
					while($resultado7->fetch()){
						//Eliminamos los indices del arreglo
						unset($numero[$numero_documento-1]);
					}
				
					//regeneramos el índice y lo mostramos
					$numero_arreglado = array_merge($numero);	
					//Presentamos los datos
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_NUMERO_INICIO", $_SESSION["ID_NUMERO_DOCUMENTO_ORIGINAL"]);
					$tpl->assign("NUMERO_INICIO", "DOCUMENTO N°".$_SESSION["ID_NUMERO_DOCUMENTO_ORIGINAL"]);
					for($i=0;$i<count($numero_arreglado);$i++){
						$tpl->newBlock("DESCRIPCION_NUMERO");
						$tpl->assign("ID_NUMERO",$numero_arreglado[$i]);
						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$numero_arreglado[$i]);
					}
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_NUMERO_TERMINO", "");
					$tpl->assign("NUMERO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
				}
				
				$resultado->close();
				$resultado2->close();
				$resultado3->close();
				$resultado4->close();				
				$resultado7->close();
			}
			else if(!isset($_GET["iddoc"]) and isset($_GET["campo"])){
				/*Validamos campo e id*/
				if(!isset($_SESSION["ID_DOCUMENTO_ORIGINAL"])){
					header("Location: sistema.php");
				}
				else{
					//Contrato
					if($_GET["campo"] == 1){					
						//$_GET["id"] es el codigo del contrato
						//ID contrato modificado
						$_SESSION["ID_CONTRATO_MODIFICADO"] = $_GET["id"];
						//Buscamos los contratos que esten asociados al usuario
						$tpl->assign("VALOR_CONTRATO_TERMINO", "");
						$tpl->assign("CONTRATO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
			
						$consulta = "select contrato.id_contrato, contrato.nombreCorto_contrato from contrato inner join usuarioContrato on ".
						"contrato.id_contrato = usuarioContrato.contrato_usuarioContrato and contrato.habilitado_contrato = 1 and ".
						"usuario_usuarioContrato = ".$_SESSION["IDENTIFICADOR_USUARIO"];
						$resultado = $conexion_db->prepare($consulta);
						$resultado->execute();
						$resultado->store_result();
						$resultado->bind_result($id_contrato, $nombreCorto_contrato);
						while($resultado->fetch()){
							if($id_contrato == $_GET["id"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_CONTRATO_INICIO",$id_contrato);
								$tpl->assign("CONTRATO_INICIO",$nombreCorto_contrato);
							}
							else{
								$tpl->newBlock("DESCRIPCION_CONTRATO"); 
								$tpl->assign("ID_CONTRATO",$id_contrato);
								$tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);						
							}
						}
						
						//Buscamos los informes asociados al contrato seleccionado				
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_INICIO", ""); 						
						$tpl->assign("INFORME_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
						$x = 0;
						$consulta2 = "select informe.codigo_informe, informe.nombre_informe from informe inner join informeContrato on ".
						"informe.codigo_informe = informeContrato.informe_informeContrato and informe.estado_informe = 1 and ".
						"informeContrato.contrato_informeContrato = ".$_GET["id"]; 
						$resultado2 = $conexion_db->prepare($consulta2);
						$resultado2->execute();
						$resultado2->store_result();
						$cantidad = $resultado2->num_rows;
						$resultado2->bind_result($codigo_informe, $nombre_informe);
						while($resultado2->fetch()){
							if($x == $cantidad-1){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_INFORME_TERMINO",$codigo_informe);
								$tpl->assign("INFORME_TERMINO",$nombre_informe);
							}
							else{
								$tpl->newBlock("DESCRIPCION_INFORME"); 
								$tpl->assign("ID_INFORME",$codigo_informe);
								$tpl->assign("NOMBRE_INFORME",$nombre_informe);						
								$x++;
							}
						}
						
						//SubInformes vacio
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_SUBINFORME_INICIO", "");
						$tpl->assign("SUBINFORME_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
						$tpl->assign("VALOR_SUBINFORME_TERMINO", -1);
						$tpl->assign("SUBINFORME_TERMINO", "NO APLICA");
						
						//Agregamos el numero del documento
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_NUMERO_INICIO", "");
						$tpl->assign("NUMERO_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
						for($i=1;$i<200;$i++){
							$tpl->newBlock("DESCRIPCION_NUMERO");
							$tpl->assign("ID_NUMERO",$i);
							$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);							
						}
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_NUMERO_TERMINO", $i);
						$tpl->assign("NUMERO_TERMINO", "DOCUMENTO N°".$i);
						
						//Liberamos los resultados
						$resultado->close();
						$resultado2->close();
					}
					//Informe
					else if($_GET["campo"] == 2){
						//$_GET["id"] es el codigo del informe
						//ID contrato modificado
						//$_SESSION["ID_CONTRATO_MODIFICADO"] = $_GET["id"];
						if(!isset($_SESSION["ID_CONTRATO_MODIFICADO"])){
							$_SESSION["ID_CONTRATO_MODIFICADO"] = $_SESSION["ID_CONTRATO_DOCUMENTO_ORIGINAL"];
						}							
						$_SESSION["ID_INFORME_MODIFICADO"] = $_GET["id"];
						//Buscamos los contratos que esten asociados al usuario
						$tpl->assign("VALOR_CONTRATO_TERMINO", "");
						$tpl->assign("CONTRATO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
			
						$consulta = "select contrato.id_contrato, contrato.nombreCorto_contrato from contrato inner join usuarioContrato on ".
						"contrato.id_contrato = usuarioContrato.contrato_usuarioContrato and contrato.habilitado_contrato = 1 and ".
						"usuario_usuarioContrato = ".$_SESSION["IDENTIFICADOR_USUARIO"];
						$resultado = $conexion_db->prepare($consulta);
						$resultado->execute();
						$resultado->store_result();
						$resultado->bind_result($id_contrato, $nombreCorto_contrato);
						while($resultado->fetch()){
							if($id_contrato == $_SESSION["ID_CONTRATO_MODIFICADO"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_CONTRATO_INICIO",$id_contrato);
								$tpl->assign("CONTRATO_INICIO",$nombreCorto_contrato);
							}
							else{
								$tpl->newBlock("DESCRIPCION_CONTRATO"); 
								$tpl->assign("ID_CONTRATO",$id_contrato);
								$tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);						
							}
						}
						
						//Buscamos los informes asociados al contrato seleccionado				
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_TERMINO", ""); 						
						$tpl->assign("INFORME_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
						$consulta2 = "select informe.codigo_informe, informe.nombre_informe from informe inner join informeContrato on ".
						"informe.codigo_informe = informeContrato.informe_informeContrato and informe.estado_informe = 1 and ".
						"informeContrato.contrato_informeContrato = ".$_SESSION["ID_CONTRATO_MODIFICADO"]; 
						$resultado2 = $conexion_db->prepare($consulta2);
						$resultado2->execute();
						$resultado2->store_result();
						$resultado2->bind_result($codigo_informe, $nombre_informe);
						while($resultado2->fetch()){
							if($codigo_informe == $_GET["id"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_INFORME_INICIO",$codigo_informe);
								$tpl->assign("INFORME_INICIO",$nombre_informe);
							}
							else{
								$tpl->newBlock("DESCRIPCION_INFORME"); 
								$tpl->assign("ID_INFORME",$codigo_informe);
								$tpl->assign("NOMBRE_INFORME",$nombre_informe);						
							}
						}
						
						//SubInformes asociados al informe												
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_SUBINFORME_INICIO", ""); 
						$tpl->assign("SUBINFORME_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
						$consulta3 = "select subInforme.codigo_subInforme, subInforme.nombre_subInforme from subInforme inner join subInformeInforme on ".
						"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme ".
						"and subInforme.estado_subInforme = 1 and subInformeInforme.contrato_subInformeInforme = ".$_SESSION["ID_CONTRATO_MODIFICADO"].
						" and informe_subInformeInforme = ".$_GET["id"];
						$resultado3 = $conexion_db->prepare($consulta3);
						$resultado3->execute();
						$resultado3->store_result();						
						$resultado3->bind_result($codigo_subInforme, $nombre_subInforme);
						while($resultado3->fetch()){
							$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
							$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
							$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);						
						}
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_SUBINFORME_TERMINO", -1); 
						$tpl->assign("SUBINFORME_TERMINO", "NO APLICA");
						
						//Agregamos el numero del documento
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_NUMERO_INICIO", "");
						$tpl->assign("NUMERO_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
						for($i=1;$i<200;$i++){
							$tpl->newBlock("DESCRIPCION_NUMERO");
							$tpl->assign("ID_NUMERO",$i);
							$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);							
						}
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_NUMERO_TERMINO", $i);
						$tpl->assign("NUMERO_TERMINO", "DOCUMENTO N°".$i);
						
						//Liberamos los resultados
						$resultado->close();
						$resultado2->close();						
						$resultado3->close();
					}
					//Sub-Informe
					else if($_GET["campo"] == 3){
						//ID contrato modificado
						//$_SESSION["ID_CONTRATO_MODIFICADO"]
						//ID informe modificado
						//$_SESSION["ID_INFORME_MODIFICADO"]
						if(!isset($_SESSION["ID_CONTRATO_MODIFICADO"])){
							$_SESSION["ID_CONTRATO_MODIFICADO"] = $_SESSION["ID_CONTRATO_DOCUMENTO_ORIGINAL"];
						}
						if(!isset($_SESSION["ID_INFORME_MODIFICADO"])){
							$_SESSION["ID_INFORME_MODIFICADO"] = $_SESSION["ID_INFORME_DOCUMENTO_ORIGINAL"];
						}
						$_GET["ID_SUBINFORME_MODIFICADO"] = $_GET["id"];
						//Buscamos los contratos que esten asociados al usuario
						$tpl->assign("VALOR_CONTRATO_TERMINO", "");
						$tpl->assign("CONTRATO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
			
						$consulta = "select contrato.id_contrato, contrato.nombreCorto_contrato from contrato inner join usuarioContrato on ".
						"contrato.id_contrato = usuarioContrato.contrato_usuarioContrato and contrato.habilitado_contrato = 1 and ".
						"usuario_usuarioContrato = ".$_SESSION["IDENTIFICADOR_USUARIO"];
						$resultado = $conexion_db->prepare($consulta);
						$resultado->execute();
						$resultado->store_result();
						$resultado->bind_result($id_contrato, $nombreCorto_contrato);
						while($resultado->fetch()){
							if($id_contrato == $_SESSION["ID_CONTRATO_MODIFICADO"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_CONTRATO_INICIO",$id_contrato);
								$tpl->assign("CONTRATO_INICIO",$nombreCorto_contrato);
							}
							else{
								$tpl->newBlock("DESCRIPCION_CONTRATO"); 
								$tpl->assign("ID_CONTRATO",$id_contrato);
								$tpl->assign("NOMBRE_CONTRATO",$nombreCorto_contrato);						
							}
						}
						
						//Buscamos los informes asociados al contrato seleccionado				
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_TERMINO", ""); 						
						$tpl->assign("INFORME_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
						$consulta2 = "select informe.codigo_informe, informe.nombre_informe from informe inner join informeContrato on ".
						"informe.codigo_informe = informeContrato.informe_informeContrato and informe.estado_informe = 1 and ".
						"informeContrato.contrato_informeContrato = ".$_SESSION["ID_CONTRATO_MODIFICADO"]; 
						$resultado2 = $conexion_db->prepare($consulta2);
						$resultado2->execute();
						$resultado2->store_result();
						$resultado2->bind_result($codigo_informe, $nombre_informe);
						while($resultado2->fetch()){
							if($codigo_informe == $_SESSION["ID_INFORME_MODIFICADO"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_INFORME_INICIO",$codigo_informe);
								$tpl->assign("INFORME_INICIO",$nombre_informe);
							}
							else{
								$tpl->newBlock("DESCRIPCION_INFORME"); 
								$tpl->assign("ID_INFORME",$codigo_informe);
								$tpl->assign("NOMBRE_INFORME",$nombre_informe);						
							}
						}
						
						//SubInformes asociados al informe																		
						$j = 0;
						$consulta3 = "select subInforme.codigo_subInforme, subInforme.nombre_subInforme from subInforme inner join subInformeInforme on ".
						"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme ".
						"and subInformeInforme.contrato_subInformeInforme = ".$_SESSION["ID_CONTRATO_MODIFICADO"].
						" and subInforme.estado_subInforme = 1 and informe_subInformeInforme = ".$_SESSION["ID_INFORME_MODIFICADO"];
						$resultado3 = $conexion_db->prepare($consulta3);
						$resultado3->execute();
						$resultado3->store_result();						
						$resultado3->bind_result($codigo_subInforme, $nombre_subInforme);
						while($resultado3->fetch()){
							if($codigo_subInforme == $_GET["id"]){
								$tpl->gotoBlock("_ROOT");
								$tpl->assign("VALOR_SUBINFORME_INICIO",$codigo_subInforme);
								$tpl->assign("SUBINFORME_INICIO",$nombre_subInforme);
								$j=1;
							}
							else{
								$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
								$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
								$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);				
							}						
						}
						if($j == 0){
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_SUBINFORME_INICIO",-1);
							$tpl->assign("SUBINFORME_INICIO","NO APLICA");
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_SUBINFORME_TERMINO",""); 
							$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR OPCI&Oacute;N --");
						}
						else{
							$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
							$tpl->assign("ID_SUBINFORME",-1);
							$tpl->assign("NOMBRE_SUBINFORME","NO APLICA");											
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_SUBINFORME_TERMINO",""); 
							$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR OPCI&Oacute;N --");						
						}
						
						//Buscamos los numero de documentos disponibles	
						//Buscamos los número ya usados				
						$consulta4 = "SELECT numero_documento FROM documento WHERE contrato_documento=".$_SESSION["ID_CONTRATO_MODIFICADO"].
						" and informe_documento=".$_SESSION["ID_INFORME_MODIFICADO"]." and subInforme_documento=".$_GET["id"];				
						$resultado4 = $conexion_db->prepare($consulta4);
						$resultado4->execute();
						$resultado4->store_result();
						//Si no hay documentos de ese sub-directorio
						if($resultado4->num_rows == 0){
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_NUMERO_INICIO", "");
							$tpl->assign("NUMERO_INICIO", "--- SELECCIONAR OPCI&Oacute;N --");
							//Presentamos los datos
							for($i=1;$i<200;$i++){						
								$tpl->newBlock("DESCRIPCION_NUMERO");
								$tpl->assign("ID_NUMERO",$i);
								$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
							}
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_NUMERO_TERMINO",$i);
							$tpl->assign("NUMERO_TERMINO","DOCUMENTO N°".$i);				
						}
						//Si hay documentos en ese sub-directorio
						else{
							//Creamos el array
							for($i=0;$i<200;$i++){
								$numero[$i] = $i+1;
							}
							
							//Asignamos el resultado a las variables		
							$resultado4->bind_result($numero_documento);				
							//Recorremos las variable
							while($resultado4->fetch()){
								//Eliminamos los indices del arreglo
								unset($numero[$numero_documento-1]);
							}
						
							//regeneramos el índice y lo mostramos
							$numero_arreglado = array_merge($numero);	
							//Presentamos los datos
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_NUMERO_INICIO", $_SESSION["ID_NUMERO_DOCUMENTO_ORIGINAL"]);
							$tpl->assign("NUMERO_INICIO", "DOCUMENTO N°".$_SESSION["ID_NUMERO_DOCUMENTO_ORIGINAL"]);
							for($i=0;$i<count($numero_arreglado);$i++){
								$tpl->newBlock("DESCRIPCION_NUMERO");
								$tpl->assign("ID_NUMERO",$numero_arreglado[$i]);
								$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$numero_arreglado[$i]);
							}
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_NUMERO_TERMINO", "");
							$tpl->assign("NUMERO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
						}
						
						//Liberamos los resultados
						$resultado->close();
						$resultado2->close();						
						$resultado3->close();
						$resultado4->close();
					}
				}			
			}
			//Insertamos el resto de los controles
			
			//Agregamos la fecha del documento
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("FECHA_DOCUMENTO", $_SESSION["FECHA_DOCUMENTO_ORIGINAL"]);	
							
			//Agregamos la hoja del documento
			$na = 0;
			$lc = 0;
			if($_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"] == -1){		
				$tpl->assign("VALOR_HOJA_INICIO", -1);
				$tpl->assign("NUMERO_HOJA_INICIO", "NO APLICA");
				$na = 1;
			}	
			if($_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"] == 0){		
				$tpl->assign("VALOR_HOJA_INICIO", 0);
				$tpl->assign("NUMERO_HOJA_INICIO", "LIBRO COMPLETO");
				$lc = 1;
			}	
			for($i=1;$i<=60;$i++){
				if($i == $_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"]){
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_HOJA_INICIO", $_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"]);
					$tpl->assign("NUMERO_HOJA_INICIO", "HOJA N°".$_SESSION["ID_HOJA_DOCUMENTO_ORIGINAL"]);
				}
				else if($i == 60){
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",$i);
					$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
					
					if($na == 0 and $lc == 1){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_HOJA_TERMINO",-1);
						$tpl->assign("NUMERO_HOJA_TERMINO","NO APLICA");					
					}
					if($na == 1 and $lc == 0){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_HOJA_TERMINO",0);
						$tpl->assign("NUMERO_HOJA_TERMINO","LIBRO COMPLETO");				
					}
					if($na == 0 and $lc == 0){
						$tpl->newBlock("DESCRIPCION_HOJA");
						$tpl->assign("ID_HOJA",0);
						$tpl->assign("NOMBRE_HOJA","LIBRO COMPLETO");
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_HOJA_TERMINO",-1);
						$tpl->assign("NUMERO_HOJA_TERMINO","NO APLICA");				
					}
				}
				else{
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",$i);
					$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
				}
			}
			
			//Agregamos el destinatarios
			$consulta5 = "select id_destinatario, nombre_destinatario from destinatario";
			$resultado5 = $conexion_db->prepare($consulta5);		
			$resultado5->execute();
			$resultado5->store_result();
			$resultado5->bind_result($id_destinatario, $nombre_destinatario);
			while($resultado5->fetch()){
				if($id_destinatario == $_SESSION["ID_DESTINATARIO_DOCUMENTO_ORIGINAL"]){
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_DESTINATARIO_INICIO", $id_destinatario);
					$tpl->assign("NUMERO_DESTINATARIO_INICIO", $nombre_destinatario);
				}
				else{
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
					$tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $nombre_destinatario);
				}
			}
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALOR_DESTINATARIO_TERMINO", "");
			$tpl->assign("NUMERO_DESTINATARIO_TERMINO", "--- SELECCIONAR OPCI&Oacute;N --");
			//Agregamos la descripción
			$tpl->assign("CONTENIDO_DESCRIPCION", $_SESSION["DESCRIPCION_DOCUMENTO_ORIGINAL"]);
			
			//Liberamos consultas y conexion	
			$resultado5->close();		
		}
		$conexion_db->close();
		$tpl->printToScreen();							
	}	
	//Modificacion del formulario
	else{
		//Informacion del formulario				
		$identificador_contrato = $_POST["contrato"];	//int
		$identificador_informe = $_POST["informe"];		//int
		$identificador_subInforme = $_POST["subInforme"];	//int
		$identificador_numero = $_POST["numeroDocumento"];	//int
		$fecha_documento = $_POST["fechaDocumento"];	//date
		$numero_hoja = $_POST["hoja"];	//int
		$identificador_destinatario = $_POST["destinatario"];	//int
		$comentario_documento = htmlentities(mb_strtoupper(trim($_POST["descripcion"]),'UTF-8'));	//char						
		
		//Verficamos si hay archivo a modificar o no		
		/*if(empty($_FILES["archivo"]["name"])){
			//Valor ruta
			$ruta = $_SESSION["RUTA_DOCUMENTO_ORIGINAL"];
			$nombre_documento_original = basename($ruta);			
		}*/
		//ruta_base
		$ruta_base = "../../documentos/";			
						
		//Generamos el nombre del documento
			
		//NombreContrato
		$consulta = "select nombreCorto_contrato from contrato where id_contrato = ".$identificador_contrato;
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($nombreCorto_contrato);
		$resultado->fetch();				
				
		//NombreInforme
		$consulta2 = "select nombre_informe from informe where codigo_informe = ".$identificador_informe;
		$resultado2 = $conexion_db->prepare($consulta2);
		$resultado2->execute();
		$resultado2->store_result();
		$resultado2->bind_result($nombre_informe);
		$resultado2->fetch();
				
		//NombreSubInforme
		if($identificador_subInforme != -1){
			$consulta3 = "select nombre_subInforme from subInforme where codigo_subInforme = ".$identificador_subInforme;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($nombre_subInforme);
			$resultado3->fetch();
			//Generamos la ruta donde se almacenara el informe subido
			$ruta_final = $ruta_base.sanear_string(html_entity_decode($nombreCorto_contrato))."/".sanear_string(html_entity_decode($nombre_informe)).
			"/".sanear_string(html_entity_decode($nombre_subInforme))."/";
		
			//Generamos el nombre del documento a guardar
			if($numero_hoja == -1){
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_subInforme))."_N".$identificador_numero;	
			}
			else if($numero_hoja == 0){
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_subInforme))."_LIBRO";			
			}
			else{
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_subInforme))."_HOJA_".$numero_hoja;			
			}
		}
		else{
			//Generamos la ruta donde se almacenara el informe subido
			$ruta_final = $ruta_base.sanear_string(html_entity_decode($nombreCorto_contrato))."/".sanear_string(html_entity_decode($nombre_informe))."/";
			
			//Generamos el nombre del documento a guardar
			if($numero_hoja == -1){
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_informe))."_N".$identificador_numero;	
			}
			else if($numero_hoja == 0){
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_informe))."_LIBRO";			
			}
			else{
				$nombre_documento = $identificador_numero.".".sanear_string(html_entity_decode($nombre_informe))."_HOJA_".$numero_hoja;			
			}
		}
		
		//Revisamos el archivo y agregamos la extensión al nombre documento.
		if(!empty($_FILES["archivo"]["name"])){
			if($_FILES["archivo"]["error"] > 0){
				//Cerramos las conexiones
				if(isset($resultado)){ $resultado->close(); } 
				if(isset($resultado2)){ $resultado2->close(); } 
				if(isset($resultado3)){ $resultado3->close(); } 
				$conexion_db->close();
				//Variables de sesión
				$_SESSION["TEMP_ERROR_FILE"] = $_FILES["archivo"]["error"];						
				$_SESSION["TEMP_ERROR_ARCHIVO"] = "SI";
			//Redireccionar
				header("Location: trabajarDocumento.php");
			}		
			else{
				if(strcmp($_FILES["archivo"]["type"],"application/pdf") == 0){ $nombre_documento = $nombre_documento.".pdf"; }
				else if(strcmp($_FILES["archivo"]["type"],"application/vnd.ms-excel") == 0){ $nombre_documento = $nombre_documento.".xls"; }
				else if(strcmp($_FILES["archivo"]["type"],"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") == 0){ 
					$nombre_documento = $nombre_documento.".xlsx"; 
				}
				else if(strcmp($_FILES["archivo"]["type"],"application/msword") == 0){ $nombre_documento = $nombre_documento.".doc"; }
				else if(strcmp($_FILES["archivo"]["type"],"application/vnd.openxmlformats-officedocument.wordprocessingml.document") == 0){ 
					$nombre_documento = $nombre_documento.".docx"; 
				}
				else if(strcmp($_FILES["archivo"]["type"],"application/vnd.ms-powerpoint") == 0){ $nombre_documento = $nombre_documento.".ppt"; }
				else if(strcmp($_FILES["archivo"]["type"],"application/vnd.openxmlformats-officedocument.presentationml.presentation") == 0){ 
					$nombre_documento = $nombre_documento.".pptx"; 
				}
				else if(strcmp($_FILES["archivo"]["type"],"image/jpeg") == 0){ $nombre_documento = $nombre_documento.".jpg"; }
				else if(strcmp($_FILES["archivo"]["type"],"image/bmp") == 0){ $nombre_documento = $nombre_documento.".bmp"; }
				else if(strcmp($_FILES["archivo"]["type"],"image/png") == 0){ $nombre_documento = $nombre_documento.".png"; }
				else{
					//Cerramos las conexiones
					if(isset($resultado)){ $resultado->close(); } 
					if(isset($resultado2)){ $resultado2->close(); } 
					if(isset($resultado3)){ $resultado3->close(); } 
					$conexion_db->close();
					//Variables de sesión				
					$_SESSION["TEMP_ERROR_TIPO"] = "SI";
					//Redireccionar
					header("Location: trabajarDocumento.php");
				}
			}
		}
		else{
			$parte_ruta = pathinfo($_SESSION["RUTA_DOCUMENTO_ORIGINAL"]);
			$nombre_documento = $nombre_documento.".".$parte_ruta["extension"];
		}
			
		//Liberamos los resultados
		if(isset($resultado)){ $resultado->close(); } 
		if(isset($resultado2)){ $resultado2->close(); } 
		if(isset($resultado3)){ $resultado3->close(); } 
				
		//Valor ruta generada
		$ruta = $ruta_final.$nombre_documento;	
		
		//Si se reemplaza el archivo
		if(!empty($_FILES["archivo"]["name"])){			
			//Se elimina el archivo antiguo
			if(file_exists($_SESSION["RUTA_DOCUMENTO_ORIGINAL"])){
				unlink($_SESSION["RUTA_DOCUMENTO_ORIGINAL"]);
			}
		
			//Se sube el archivo actual			
			if(file_exists($ruta)){
				//Cerramos las conexiones
				if(isset($resultado)){ $resultado->close(); } 
				if(isset($resultado2)){ $resultado2->close(); } 
				if(isset($resultado3)){ $resultado3->close(); } 
				$conexion_db->close();
				//Variables de sesión				
				$_SESSION["TEMP_ERROR_NOMBRE"] = "SI";
				//Redireccionar
				header("Location: trabajarDocumento.php");
			}
			else{
				//Se mueve el documento
				move_uploaded_file($_FILES["archivo"]["tmp_name"],$ruta_final.$nombre_documento);
			}
		}
		//No hay reemplazo de archivo solo cambio de directorio y/o renombre
		else{
			//Si las rutas son iguales no se hace nada
			$parte_ruta_original = pathinfo($_SESSION["RUTA_DOCUMENTO_ORIGINAL"]);
			$parte_ruta_generada = pathinfo($ruta);
			
			//El directorio es igual
			if(strcmp($parte_ruta_original["dirname"], $parte_ruta_generada["dirname"]) == 0){
				//El nombre es diferente
				if(strcmp($parte_ruta_original["basename"], $parte_ruta_generada["basename"])!= 0){
					rename($_SESSION["RUTA_DOCUMENTO_ORIGINAL"], $ruta);
				}	
			}
			//El directorio no es igual
			else{
				rename($_SESSION["RUTA_DOCUMENTO_ORIGINAL"], $ruta);
			}			
		}
		
	
		//Actualizamos la Base de Datos
		$consulta4 = "update documento set contrato_documento = ".$identificador_contrato.", informe_documento = ".$identificador_informe.
		", subInforme_documento = ".$identificador_subInforme.", numero_documento = ".$identificador_numero.", fecha_documento = '".$fecha_documento.
		"', fechaSubida_documento = current_date(), horaSubida_documento = current_time(), hoja_documento = ".$numero_hoja.
		", destinatario_documento = ".$identificador_destinatario.", descripcion_documento = '".$comentario_documento.
		"', nombre_documento = '".$nombre_documento."', ruta_documento = '".$ruta."' where codigo_documento = ".$_SESSION["ID_DOCUMENTO_ORIGINAL"];
		$resultado4 = $conexion_db->prepare($consulta4);
		$resultado4->execute();
		
		//Obtenemos fecha subida y hora subida
		$consulta5 = "select fechaSubida_documento, horaSubida_documento from documento where codigo_documento = ".$_SESSION["ID_DOCUMENTO_ORIGINAL"];
		$resultado5 = $conexion_db->prepare($consulta5);
		$resultado5->execute();
		$resultado5->store_result();
		$resultado5->bind_result($fecha_subida,$hora_subida);
		$resultado5->fetch();
		
		//Guardamos en la tabla historial		
		$mensaje = strval("Documento modificado por el usuario ".$_SESSION['NOMBRE']." ".$_SESSION['APELLIDO']." (".$_SESSION['USUARIO'].
		"), el d&iacute;a ".fecha($fecha_subida)." a las ".$hora_subida." horas.");			
		$consulta6 = "insert into historial (documento_historial, mensaje_historial) values (".$_SESSION["ID_DOCUMENTO_ORIGINAL"].", '".$mensaje."')";		
		$resultado6 = $conexion_db->prepare($consulta6);
		$resultado6->execute();			
		
		//Cerramos las conexiones
		if(isset($resultado)){ $resultado->close(); } 
		if(isset($resultado2)){ $resultado2->close(); } 
		if(isset($resultado3)){ $resultado3->close(); } 
		if(isset($resultado4)){ $resultado4->close(); } 
		if(isset($resultado5)){ $resultado5->close(); } 
		if(isset($resultado6)){ $resultado6->close(); } 
		$conexion_db->close();
		
		//Variables de sesión				
		$_SESSION["TEMP_OK_FILE"] = "SI";
		//Redireccionar
		header("Location: trabajarDocumento.php");
		/*echo "Nombre: ".$_FILES["archivo"]["name"]."<br/>";
		echo "Tipo: ".$_FILES["archivo"]["type"]."<br/>";
		echo "Tama&ntilde;o: ".($_FILES["archivo"]["size"]/1024)."KB<br/>";
		echo "Carpeta temporal: ".$_FILES["archivo"]["tmp_name"]."<br/>";*/
	}
	
	//Sanear un string
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

