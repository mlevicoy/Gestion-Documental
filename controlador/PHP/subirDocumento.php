<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
		
	if(!isset($_POST["cargador"])){				
		$tpl = new TemplatePower("../../interfaz/HTML/subirDocumento.html");
		$tpl->prepare();
        $tpl->assign("NOMBRECONTRATO",$_SESSION["NOMBRECONTRATO"]);
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU",''); }
		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }
		//Obtenemos el código + 1
		$consulta = "SELECT codigo_documento from documento order by codigo_documento DESC LIMIT 0,1";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($codigo_documento);
		$resultado->fetch();
		$codigo_documento = $codigo_documento + 1;
		$tpl->assign("ficha", $codigo_documento);		
		
		//Respuestas automáticas		
		if(isset($_SESSION["TEMP_ERROR_ARCHIVO"]) and $_SESSION["TEMP_ERROR_ARCHIVO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","display");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","ERROR: ".$_SESSION["TEMP_ERROR_FILE"]);		
			unset($_SESSION["TEMP_ERROR_ARCHIVO"]);
			unset($_SESSION["TEMP_ERROR_FILE"]);
		}		
		if(isset($_SESSION["TEMP_ERROR_TIPO"]) and $_SESSION["TEMP_ERROR_TIPO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","display");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			//$tpl->assign("MENSAJE","ERROR DE TIPO (PDF, DOC, DOCX, XLS, XLSX, JPG, BMP, PNG)");		
            $tpl->assign("MENSAJE","ERROR DE TIPO, SOLO SE ACEPTAN PDF");
			unset($_SESSION["TEMP_ERROR_TIPO"]);			
		}
		if(isset($_SESSION["TEMP_OK_FILE"]) and $_SESSION["TEMP_OK_FILE"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","display");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","EL DOCUMENTO SE SUBIO CORRECTAMENTE");					
			unset($_SESSION["TEMP_OK_FILE"]);
		}
		if(isset($_SESSION["TEMP_ERROR_NOMBRE"]) and $_SESSION["TEMP_ERROR_NOMBRE"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","display");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","ERROR: EL DOCUMENTO YA EXISTE");					
			unset($_SESSION["TEMP_ERROR_NOMBRE"]);
		}
        if(isset($_SESSION["TEMP_ERROR_VARIOSSUBINFORMES"]) and $_SESSION["TEMP_ERROR_VARIOSSUBINFORMES"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","display");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","ERROR: YA EXISTE EL NOMBRE DE CARPETA");					
			unset($_SESSION["TEMP_ERROR_VARIOSSUBINFORMES"]);
		}
        
		/*Redireccionamiento*/		
		if(isset($_GET["id"]) and isset($_GET["campo"])){
			//Contrato
			if($_GET["campo"] == 1){ /*NO SE VA A OCUPAR CAMPO = 1*/ }			
			//Informe
			else if($_GET["campo"] == 2){				
			    //Variable con la id del informe							
				$_SESSION["IDENTIFICADOR_INFORME"] = $_GET["id"];
                $tpl->assign("NOMBRE_CONTRATO",$_SESSION["NOMBRECONTRATO"]);				
				//Informes que estan asociados al contrato                
				$tpl->assign("VALOR_INFORME_TERMINO","");
				$tpl->assign("INFORME_TERMINO","--- SELECCIONAR DOCUMENTO ---");							
				$consulta2 = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"].
				" and informe.estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombre_informe,$codigo_informe);
				while($resultado2->fetch()){					
					if($codigo_informe == $_SESSION["IDENTIFICADOR_INFORME"]){
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
                //SubInforme asociado al contrato y al informe		
				$j=0;	
				$tpl->gotoBlock("_ROOT");	
				$tpl->assign("VALOR_SUBINFORME_INICIO","");
				$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");	
				$consulta3 = "select subInforme.nombre_subInforme, subInforme.codigo_subInforme from subInforme inner join subInformeInforme on ".
				"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme and subInformeInforme.informe_subInformeInforme = ".
				$_SESSION["IDENTIFICADOR_INFORME"]." and subInforme.estado_subInforme = 1 and subInformeInforme.contrato_subInformeInforme = ".
				$_SESSION["CODIGOCONTRATO"];
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$k = $resultado3->num_rows;
				if($k == 0){
					$tpl->assign("VALOR_SUBINFORME_INICIO","");
					$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");
					$tpl->assign("VALOR_SUBINFORME_TERMINO",-1);
					$tpl->assign("SUBINFORME_TERMINO","SIN SUB-DOCUMENTO");		
				}
				else{
					$resultado3->bind_result($nombre_subInforme, $codigo_subInforme);
					while($resultado3->fetch()){
						if($j == $k-1){
						    $tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_SUBINFORME_TERMINO",$codigo_subInforme);
							$tpl->assign("SUBINFORME_TERMINO",$nombre_subInforme);													
						}
						else{					
							$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
							$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
							$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);
							$j++;
						}
					}
				}
				//Carpeta				
				$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
				$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR CARPETA ---");	
				$tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");
				$tpl->assign("USARVARIOSSUBINFORME", "disabled");
                //Numero
				for($i=1;$i<=500;$i++){
					$tpl->newBlock("DESCRIPCION_NUMERO");
					$tpl->assign("ID_NUMERO",$i);
					$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
				}	
				//Destinatario
				$consulta4 = "select id_destinatario, cargo_destinatario from destinatario";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($id_destinatario, $cargo_destinatario);
				while($resultado4->fetch()){
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
	                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $cargo_destinatario);				
				}
				$resultado2->close();
				$resultado3->close();				
				$resultado4->close();
			}
			//Sub-Informe
			else if($_GET["campo"] == 3){
				//Variable con la id del informe							
				$_SESSION["IDENTIFICADOR_SUBINFORME"] = $_GET["id"];
                $tpl->assign("NOMBRE_CONTRATO",$_SESSION["NOMBRECONTRATO"]);				
				//Informes que estan asociados al contrato
				$tpl->assign("VALOR_INFORME_TERMINO","");
				$tpl->assign("INFORME_TERMINO","--- SELECCIONAR DOCUMENTO ---");							
				$consulta2 = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"].
				" and informe.estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombre_informe,$codigo_informe);
				while($resultado2->fetch()){					
					if($codigo_informe == $_SESSION["IDENTIFICADOR_INFORME"]){
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
				//SubInforme asociado al contrato y al informe		
				$j=0;
                $tpl->gotoBlock("_ROOT");					
				$consulta3 = "select subInforme.nombre_subInforme, subInforme.codigo_subInforme from subInforme inner join subInformeInforme on ".
				"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme and subInformeInforme.informe_subInformeInforme = ".
				$_SESSION["IDENTIFICADOR_INFORME"]." and subInforme.estado_subInforme = 1 and subInformeInforme.contrato_subInformeInforme = ".
				$_SESSION["CODIGOCONTRATO"];
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$k = $resultado3->num_rows;
                if($k == 0){
					//Sub-Documento
					$tpl->assign("VALOR_SUBINFORME_INICIO",-1);
					$tpl->assign("SUBINFORME_INICIO","SIN SUB-DOCUMENTO");
					$tpl->assign("VALOR_SUBINFORME_TERMINO","");
					$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR SUB-DOCUMENTO ---");		
					//Carpetas
					$tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");
					$tpl->assign("USARVARIOSSUBINFORME", "enabled");
					//Buscamos los nombre de las carpetas
					$consulta7 = "select id_variosSubInformes, nombre_variosSubInformes from variosSubInformes";
					$resultado7 = $conexion_db->prepare($consulta7);
					$resultado7->execute();
					$resultado7->store_result();					
					$l = $resultado7->num_rows;
					if($l == 0){
						$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
						$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR CARPETA ---");
						$tpl->assign("VALOR_VARIOSSUBINFORME_TERMINO","-1");
						$tpl->assign("VARIOSSUBINFORME_TERMINO","NO EXISTE CARPETA");		
					}
					else{						
						$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
						$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");		
						$tpl->assign("VALOR_VARIOSSUBINFORME_TERMINO","-1");
						$tpl->assign("VARIOSSUBINFORME_TERMINO","NO EXISTE CARPETA");		
						
						$resultado7->bind_result($id_variosSubInformes, $nombre_variosSubInformes);
						while($resultado7->fetch()){
							$tpl->newBlock("DESCRIPCION_VARIOSSUBINFORME");
							$tpl->assign("ID_VARIOSSUBINFORME",$id_variosSubInformes);
							$tpl->assign("NOMBRE_VARIOSSUBINFORME",$nombre_variosSubInformes);
						}						
					}	
					$resultado7->close();			
				}
				else{					
					$resultado3->bind_result($nombre_subInforme, $codigo_subInforme);
					while($resultado3->fetch()){
						if($codigo_subInforme == $_SESSION["IDENTIFICADOR_SUBINFORME"]){
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
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_SUBINFORME_TERMINO","");
					$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR SUB-DOCUMENTO ---");		
					//Carpetas		
					$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
					$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR CARPETA ---");	
					$tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");
					$tpl->assign("USARVARIOSSUBINFORME", "disabled");				
				}
				
				//Numero				
				//Buscamos los número ya usados
				$consulta4 = "SELECT numero_documento FROM documento WHERE contrato_documento=".$_SESSION["CODIGOCONTRATO"].
				" and informe_documento=".$_SESSION["IDENTIFICADOR_INFORME"]." and subInforme_documento=".$_SESSION["IDENTIFICADOR_SUBINFORME"];				
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				//Si no hay documentos de ese sub-directorio
				if($resultado4->num_rows == 0){
					//Presentamos los datos
					for($i=1;$i<=500;$i++){
						$tpl->newBlock("DESCRIPCION_NUMERO");
						$tpl->assign("ID_NUMERO",$i);
						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
					}
				}
				//Si hay documentos en ese sub-directorio
				else{
					//Creamos el array
					for($i=0;$i<500;$i++){
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
					for($i=0;$i<count($numero_arreglado);$i++){
						$tpl->newBlock("DESCRIPCION_NUMERO");
						$tpl->assign("ID_NUMERO",$numero_arreglado[$i]);
						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$numero_arreglado[$i]);
					}
				}				
				//Destinatario
				$consulta4 = "select id_destinatario, cargo_destinatario from destinatario";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($id_destinatario, $cargo_destinatario);
				while($resultado4->fetch()){
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
	                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $cargo_destinatario);				
				}
				$resultado2->close();
				$resultado3->close();				
				$resultado4->close();					
			}
			else if($_GET["campo"] == 4){
				//Variable con la id del informe							
				$_SESSION["IDENTIFICADOR_VARIOSSUBINFORME"] = $_GET["id"];
				//Nombre del contrato
                $tpl->assign("NOMBRE_CONTRATO",$_SESSION["NOMBRECONTRATO"]);
				//Informes que estan asociados al contrato
				$tpl->assign("VALOR_INFORME_TERMINO","");
				$tpl->assign("INFORME_TERMINO","--- SELECCIONAR DOCUMENTO ---");							
				$consulta2 = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"].
				" and informe.estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombre_informe,$codigo_informe);
				while($resultado2->fetch()){					
					if($codigo_informe == $_SESSION["IDENTIFICADOR_INFORME"]){
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
				//SubInforme asociado al contrato y al informe		
				$j=0;
                $tpl->gotoBlock("_ROOT");					
				$consulta3 = "select subInforme.nombre_subInforme, subInforme.codigo_subInforme from subInforme inner join subInformeInforme on ".
				"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme and subInformeInforme.informe_subInformeInforme = ".
				$_SESSION["IDENTIFICADOR_INFORME"]." and subInforme.estado_subInforme = 1 and subInformeInforme.contrato_subInformeInforme = ".
				$_SESSION["CODIGOCONTRATO"];
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$k = $resultado3->num_rows;
                if($k == 0){
					//Sub-Documento
					$tpl->assign("VALOR_SUBINFORME_INICIO",-1);
					$tpl->assign("SUBINFORME_INICIO","SIN SUB-DOCUMENTO");
					$tpl->assign("VALOR_SUBINFORME_TERMINO","");
					$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR SUB-DOCUMENTO ---");		
					//Carpetas					
					$tpl->assign("USARVARIOSSUBINFORME", "enabled");
					//Buscamos los nombre de las carpetas
					$consulta7 = "select id_variosSubInformes, nombre_variosSubInformes from variosSubInformes";
					$resultado7 = $conexion_db->prepare($consulta7);
					$resultado7->execute();
					$resultado7->store_result();					
					$l = $resultado7->num_rows;
					if($l == 0){   
                        $tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO",-1);
						$tpl->assign("VARIOSSUBINFORME_INICIO","NO EXISTE CARPETA");
						$tpl->assign("VALOR_VARIOSSUBINFORME_TERMINO","");
						$tpl->assign("VARIOSSUBINFORME_TERMINO","--- SELECCIONAR CARPETA ---");
                        $tpl->assign("USARNOMBREVARIOSUBINFORMES","enabled");		
					}
					else{						
					    $tpl->assign("VALOR_VARIOSSUBINFORME_TERMINO","");
						$tpl->assign("VARIOSSUBINFORME_TERMINO","--- SELECCIONAR CARPETA ---");                                               
                        
						$resultado7->bind_result($id_variosSubInformes, $nombre_variosSubInformes);
						while($resultado7->fetch()){
						    if($_SESSION["IDENTIFICADOR_VARIOSSUBINFORME"] == $id_variosSubInformes){
                                $tpl->gotoBlock("_ROOT");
                                $tpl->assign("VALOR_VARIOSSUBINFORME_INICIO",$id_variosSubInformes);
                                $tpl->assign("VARIOSSUBINFORME_INICIO",$nombre_variosSubInformes);                                
                                $tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");                                                                
                            }
                            else{
                                $tpl->newBlock("DESCRIPCION_VARIOSSUBINFORME");
							    $tpl->assign("ID_VARIOSSUBINFORME",$id_variosSubInformes);
							    $tpl->assign("NOMBRE_VARIOSSUBINFORME",$nombre_variosSubInformes);
                            }							
						}
                        if($_SESSION["IDENTIFICADOR_VARIOSSUBINFORME"] == -1){
                            $tpl->gotoBlock("_ROOT");
                            $tpl->assign("VALOR_VARIOSSUBINFORME_INICIO",-1);
                            $tpl->assign("VARIOSSUBINFORME_INICIO","NO EXISTE CARPETA");                        
                        }	                        					
                        else{
                            $tpl->newBlock("DESCRIPCION_VARIOSSUBINFORME");
                            $tpl->assign("ID_VARIOSSUBINFORME",-1);
                            $tpl->assign("NOMBRE_VARIOSSUBINFORME","NO EXISTE CARPETA");                            
                        }                        
					}	
					$resultado7->close();
				}
				else{					
					$resultado3->bind_result($nombre_subInforme, $codigo_subInforme);
					while($resultado3->fetch()){
						if($codigo_subInforme == $_SESSION["IDENTIFICADOR_SUBINFORME"]){
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
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_SUBINFORME_TERMINO","");
					$tpl->assign("SUBINFORME_TERMINO","--- SELECCIONAR SUB-DOCUMENTO ---");		
					//Carpetas		
					$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
					$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR CARPETA ---");	
					$tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");
					$tpl->assign("USARVARIOSSUBINFORME", "disabled");				
				}
				
								
				//Buscamos los número ya usados
                //Numero (caso 1 : no existe carpeta)
                if($_SESSION["IDENTIFICADOR_VARIOSSUBINFORME"] != -1){
                    $consulta4 = "SELECT numero_documento FROM documento WHERE contrato_documento=".$_SESSION["CODIGOCONTRATO"].
				    " and informe_documento=".$_SESSION["IDENTIFICADOR_INFORME"]." and variosSub_documento=".$_SESSION["IDENTIFICADOR_VARIOSSUBINFORME"];				
    				$resultado4 = $conexion_db->prepare($consulta4);
    				$resultado4->execute();
    				$resultado4->store_result();
    				//Si no hay documentos de ese sub-directorio
    				if($resultado4->num_rows == 0){
    					//Presentamos los datos
    					for($i=1;$i<=500;$i++){
    						$tpl->newBlock("DESCRIPCION_NUMERO");
    						$tpl->assign("ID_NUMERO",$i);
    						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
    					}
    				}
    				//Si hay documentos en ese sub-directorio
    				else{
    					//Creamos el array
    					for($i=0;$i<500;$i++){
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
    					for($i=0;$i<count($numero_arreglado);$i++){
    						$tpl->newBlock("DESCRIPCION_NUMERO");
    						$tpl->assign("ID_NUMERO",$numero_arreglado[$i]);
    						$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$numero_arreglado[$i]);
    					}
    				}    
                }
                else{
                    for($i=1;$i<=500;$i++){
    					$tpl->newBlock("DESCRIPCION_NUMERO");
    					$tpl->assign("ID_NUMERO",$i);
    					$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
   					}
                }                
								
				//Destinatario
				$consulta4 = "select id_destinatario, cargo_destinatario from destinatario";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($id_destinatario, $cargo_destinatario);
				while($resultado4->fetch()){
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
	                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $cargo_destinatario);				
				}
				$resultado2->close();
				$resultado3->close();				
				$resultado4->close();					
			}
		}
		//Carga inicial
		else{			
			//Nombre de contrato
		    $tpl->assign("NOMBRE_CONTRATO",$_SESSION["NOMBRECONTRATO"]);            
            //Informe asociados al contrato			
            $tpl->gotoBlock("_ROOT");
			$tpl->assign("VALOR_INFORME_INICIO","");
			$tpl->assign("INFORME_INICIO","--- SELECCIONAR DOCUMENTO ---");							
			$i=0;
			$consulta = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
			"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"].
            " and informe.estado_informe = 1";
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$x = $resultado->num_rows;		
			$resultado->bind_result($nombre_informe,$codigo_informe);
			while($resultado->fetch()){	
				if($i == $x-1){
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_INFORME_TERMINO",$codigo_informe);
					$tpl->assign("INFORME_TERMINO",$nombre_informe);									
				}
				else{				
					$tpl->newBlock("DESCRIPCION_INFORME"); 
					$tpl->assign("ID_INFORME",$codigo_informe);
					$tpl->assign("NOMBRE_INFORME",$nombre_informe);					
					$i++;
				}
			}
            //SubInforme
			$tpl->assign("VALOR_SUBINFORME_INICIO","");
			$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");	
			//Carpeta
			$tpl->assign("VALOR_VARIOSSUBINFORME_INICIO","");
			$tpl->assign("VARIOSSUBINFORME_INICIO","--- SELECCIONAR CARPETA ---");
			$tpl->assign("USARNOMBREVARIOSUBINFORMES","disabled");
			$tpl->assign("USARVARIOSSUBINFORME", "disabled");
			//Numero
			for($i=1;$i<=500;$i++){
				$tpl->newBlock("DESCRIPCION_NUMERO");
				$tpl->assign("ID_NUMERO",$i);
				$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
			}			
			//Destinatario
			$consulta2 = "select id_destinatario, cargo_destinatario from destinatario";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
			$resultado2->bind_result($id_destinatario, $cargo_destinatario);
			while($resultado2->fetch()){
				$tpl->newBlock("DESCRIPCION_DESTINATARIO");
                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
				$tpl->assign("NOMBRE_DESTINATARIO", $cargo_destinatario);				
			}
            
			
			$resultado->close();
			$resultado2->close();            
		}
		
		if(isset($_SESSION["TEMP_ERROR_ARCHIVO"]) and $_SESSION["TEMP_ERROR_ARCHIVO"] == "SI"){
			$tpl->assign("MENSAJE","ERROR AL TRATAR DE SUBIR EL ARCHIVO");
			unset($_SESSION["TEMP_ERROR_ARCHIVO"]);
		}		
		if(isset($_SESSION["TEMP_ERROR_TIPO"]) and $_SESSION["TEMP_ERROR_TIPO"] == "SI"){
			$tpl->assign("MENSAJE","FORMATO NO PERMITIDO, SUBIR ARCHIVOS PDF, WORD, EXCEL, JPG, BMP");
			unset($_SESSION["TEMP_ERROR_TIPO"]);
		}
		if(isset($_SESSION["TEMP_OK_FILE"]) and $_SESSION["TEMP_OK_FILE"] == "SI"){
			$tpl->assign("MENSAJE","ARCHIVO SUBIDO CORRECTAMENTE");
			unset($_SESSION["TEMP_OK_FILE"]);
		}	
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//ruta_base
        $ruta_base = "../../documentos/";        
            
		//Informacion del formulario		
		$identificador_contrato = $_SESSION["CODIGOCONTRATO"];	//int
		$identificador_informe = $_POST["informe"];		//int
		$identificador_subInforme = $_POST["subInforme"];	//int                  
		$identificador_numero = $_POST["numeroDocumento"];	//int
		$fecha_documento = $_POST["fechaDocumento"];	//date		
		$identificador_destinatario = $_POST["destinatario"];	//int
		$comentario_documento = strtoupper(sanear_string_espacio(trim($_POST["descripcion"])));	//char		

        //Buscamos el nombre de varios SubInformes
        if(!isset($_POST["variosSubInforme"]) and !isset($_POST["nombreVariosSubInformes"])){
            $variosSub_documento = -1;
        }
		//Creamos un variossubInforme - EXISTE NOMBREVARIOSSUBINFORMES Y VARIOSSUINFORMES = -1 - NO EXISTE EL EL VARIOS SUB INFORMES
        else if(isset($_POST["nombreVariosSubInformes"])){
            $nombre_variosSubInformes = $_POST["nombreVariosSubInformes"];  //char
            //Vemos si existe el nombre y sino se guarda
            $consulta9 = "select count(*) as ctdad_variosSubInformes from variossubinformes where nombre_variosSubInformes = '".
            mb_strtoupper(trim($_POST["nombreVariosSubInformes"]),'UTF-8')."'";
            $resultado9 = $conexion_db->prepare($consulta9);
            $resultado9->execute();
            $resultado9->store_result();
            $resultado9->bind_result($ctdad_variosSubInformes);
            $resultado9->fetch();
            //Comparamos la consulta
            if($ctdad_variosSubInformes == 1){
                $resultado9->close();
                $conexion_db->close();
                $_SESSION["TEMP_ERROR_VARIOSSUBINFORMES"] = "SI";
                //Redireccionar
                header("Location: subirDocumento.php");
            }
            else{
                //Generamos el directorio
                //buscamos el nombre corto saneado del contrato
                //$consulta12 = "select nombreCorto_contrato from contrato where id_contrato = ".$identificador_contrato;
                $consulta12 = "select nombreCortoSaneado_contrato from contrato where id_contrato = ".$identificador_contrato;
                $resultado12 = $conexion_db->prepare($consulta12);
                $resultado12->execute();
                $resultado12->store_result();
                $resultado12->bind_result($nombreCortoSaneado_contrato);
                $resultado12->fetch();                
                
                //buscamos el nombre corto saneado de informe                
                //$consulta13 = "select nombre_informe from informe where codigo_informe = ".$identificador_informe;
                $consulta13 = "select nombreSaneado_informe from informe where codigo_informe = ".$identificador_informe;
                $resultado13 = $conexion_db->prepare($consulta13);
                $resultado13->execute();
                $resultado13->store_result();
                $resultado13->bind_result($nombreSaneado_informe);
                $resultado13->fetch();
                
                //Generamos la ruta y creamos el directorio
                /*$ruta_variosSubDocumentos = "../../documentos/".sanear_string(html_entity_decode())."/".
                sanear_string(html_entity_decode())."/".sanear_string(html_entity_decode());*/
                
                /*$ruta_variosSubDocumentos = "../../documentos/".$nombreCortoSaneado_contrato."/".
                mb_strtoupper(sanear_string($nombre_informe),'UTF-8')."/".mb_strtoupper(sanear_string($nombre_variosSubInformes),'UTF-8');*/
                $ruta_variosSubDocumentos = "../../documentos/".$nombreCortoSaneado_contrato."/".$nombreSaneado_informe."/".
                mb_strtoupper(sanear_string(trim($_POST["nombreVariosSubInformes"])),'UTF-8');
                
                if(!mkdir($ruta_variosSubDocumentos, 0777, true)){
				    if(isset($resultado9)){$resultado9->close();} 
                    if(isset($resultado10)){$resultado10->close();}
                    if(isset($resultado11)){$resultado11->close();}
                    if(isset($resultado12)){$resultado12->close();}
                    if(isset($resultado13)){$resultado13->close();}
				    $conexion_db->close();
				    $_SESSION["TEMP_ERROR_DIRECTORIO"] = "SI";
				    header('Location: subirDocumento.php');				
                }
                else{
                    //Almacenamos la información en la BD si el directorio es creado correctamente
                    $consulta10 = "insert into variossubinformes (nombre_variosSubInformes, nombreSaneado_variosSubInformes) values ('".
                    mb_strtoupper(trim($_POST["nombreVariosSubInformes"]),'UTF-8')."', '".
                    mb_strtoupper(sanear_string(trim($_POST["nombreVariosSubInformes"])),'UTF-8')."')";
                    $resultado10 = $conexion_db->prepare($consulta10);
                    $resultado10->execute();
                
                    $consulta11 = "select id_variosSubInformes from variosSubinformes where nombre_variosSubInformes = '".
                    mb_strtoupper($_POST["nombreVariosSubInformes"],'UTF-8')."'";
                    $resultado11 = $conexion_db->prepare($consulta11);
                    $resultado11->execute();
                    $resultado11->store_result();
                    $resultado11->bind_result($variosSub_documento);
                    $resultado11->fetch();
                }            
            }
        }
		//Cuando el nombrevariossubinforme ya esta en la base de datos, se consulta si existe el directorio
        else{
            //Generamos el directorio
            //buscamos el nombre corto saneado del contrato
            //$consulta12 = "select nombreCorto_contrato from contrato where id_contrato = ".$identificador_contrato;
            $consulta12 = "select nombreCortoSaneado_contrato from contrato where id_contrato = ".$identificador_contrato;
            $resultado12 = $conexion_db->prepare($consulta12);
            $resultado12->execute();
            $resultado12->store_result();
            $resultado12->bind_result($nombreCortoSaneado_contrato);
            $resultado12->fetch();                
                
            //buscamos el nombre de informe                
            $consulta13 = "select nombreSaneado_informe from informe where codigo_informe = ".$identificador_informe;
            $resultado13 = $conexion_db->prepare($consulta13);
            $resultado13->execute();
            $resultado13->store_result();
            $resultado13->bind_result($nombreSaneado_informe);
            $resultado13->fetch();
            
            //Obtenemos el nombre del directorio
            $consulta14 = "select nombreSaneado_variosSubInformes from variossubinformes where id_variosSubInformes = ".$_POST["variosSubInforme"];
            $resultado14 = $conexion_db->prepare($consulta14);
            $resultado14->execute();
            $resultado14->store_result();
            $resultado14->bind_result($nombreSaneado_variosSubInformes);
            $resultado14->fetch();
                
            //Generamos la ruta y creamos el directorio
            /*$ruta_variosSubDocumentos = "../../documentos/".mb_strtoupper(sanear_string($nombreCorto_contrato),'UTF-8')."/".
            mb_strtoupper(sanear_string($nombre_informe),'UTF-8')."/".mb_strtoupper(sanear_string($nombre_variosSubInformes),'UTF-8');*/
            $ruta_variosSubDocumentos = $ruta_base.$nombreCortoSaneado_contrato."/".$nombreSaneado_informe."/".$nombreSaneado_variosSubInformes;
            
            //Verificamos si existe o no el directorio
            if(!file_exists($ruta_variosSubDocumentos)){
                if(!mkdir($ruta_variosSubDocumentos, 0777, true)){
				    if(isset($resultado9)){$resultado9->close();} 
                    if(isset($resultado10)){$resultado10->close();}
                    if(isset($resultado11)){$resultado11->close();}
                    if(isset($resultado12)){$resultado12->close();}
                    if(isset($resultado13)){$resultado13->close();}
                    if(isset($resultado14)){$resultado14->close();}
				    $conexion_db->close();
				    $_SESSION["TEMP_ERROR_DIRECTORIO"] = "SI";
				    header('Location: subirDocumento.php');				
                }                
            }
            $consulta8 = "select id_variosSubInformes, nombre_variosSubInformes, nombreSaneado_variosSubInformes from variossubinformes ".
            "where id_variosSubInformes = ".$_POST["variosSubInforme"];
            $resultado8 = $conexion_db->prepare($consulta8);
            $resultado8->execute();
            $resultado8->store_result();
            $resultado8->bind_result($variosSub_documento, $nombre_variosSubInformes, $nombreSaneado_variosSubInformes);
            $resultado8->fetch();
        }
        		
		//Generamos el nombre del documento		
		//NombreContrato
		$consulta = "select nombreCortoSaneado_contrato from contrato where id_contrato = ".$identificador_contrato;
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($nombreCortoSaneado_contrato);
		$resultado->fetch();		
        
		//NombreInforme
		$consulta2 = "select nombreSaneado_informe from informe where codigo_informe = ".$identificador_informe;
		$resultado2 = $conexion_db->prepare($consulta2);
		$resultado2->execute();
		$resultado2->store_result();
		$resultado2->bind_result($nombreSaneado_informe);
		$resultado2->fetch();
        
        //NombreSubInforme
		if($identificador_subInforme != -1){
			$consulta3 = "select nombreSaneado_subInforme from subInforme where codigo_subInforme = ".$identificador_subInforme;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($nombreSaneado_subInforme);
			$resultado3->fetch();
		
        	//Generamos la ruta donde se almacenara el informe subido
			/*$ruta_final = $ruta_base.html_entity_decode(sanear_string($nombreCorto_contrato))."/".html_entity_decode(sanear_string($nombre_informe))."/".
			html_entity_decode(sanear_string($nombre_subInforme))."/";*/
            $ruta_final = $ruta_base.$nombreCortoSaneado_contrato."/".$nombreSaneado_informe."/".$nombreSaneado_subInforme."/";
			
			//Generamos el nombre del documento a guardar
			//$nombre_documento = $identificador_numero.".".html_entity_decode(sanear_string($nombre_subInforme))."_N".$identificador_numero;						
            $nombre_documento = $identificador_numero.".".$nombreSaneado_subInforme."_N".$identificador_numero;
            
		}
		else{
		    //Obtenemos el nombre del directorio
            $consulta14 = "select nombreSaneado_variosSubInformes from variossubinformes where id_variosSubInformes = ".$variosSub_documento;;
            $resultado14 = $conexion_db->prepare($consulta14);
            $resultado14->execute();
            $resultado14->store_result();
            $resultado14->bind_result($nombreSaneado_variosSubInformes);
            $resultado14->fetch();
			
            //Generamos la ruta donde se almacenara el informe subido
			/*$ruta_final = $ruta_base.html_entity_decode(sanear_string($nombreCorto_contrato))."/".html_entity_decode(sanear_string($nombre_informe))."/".
            html_entity_decode(sanear_string($nombre_variosSubInformes))."/";*/
            $ruta_final = $ruta_base.$nombreCortoSaneado_contrato."/".$nombreSaneado_informe."/".$nombreSaneado_variosSubInformes."/";
			
            //Generamos el mombre del documento a guardar
			$nombre_documento = $identificador_numero.".".$nombreSaneado_variosSubInformes."_N".$identificador_numero;
            
        }		
		
		//Almacenamos el documento
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
			header("Location: subirDocumento.php");
		}		
		else{
			if(strcmp($_FILES["archivo"]["type"],"application/pdf") == 0){ 
                $nombre_documento = $nombre_documento.".pdf"; 
            }
			else{
				//Cerramos las conexiones
				if(isset($resultado)){ $resultado->close(); } 
				if(isset($resultado2)){ $resultado2->close(); } 
				if(isset($resultado3)){ $resultado3->close(); } 
				$conexion_db->close();
				//Variables de sesión				
				$_SESSION["TEMP_ERROR_TIPO"] = "SI";
				//Redireccionar
				header("Location: subirDocumento.php");
			}
			//Verificamos si existe el documento
			if(file_exists($ruta_final.$nombre_documento)){
				//Cerramos las conexiones
				if(isset($resultado)){ $resultado->close(); } 
				if(isset($resultado2)){ $resultado2->close(); } 
				if(isset($resultado3)){ $resultado3->close(); } 
				$conexion_db->close();
				//Variables de sesión				
				$_SESSION["TEMP_ERROR_NOMBRE"] = "SI";
				//Redireccionar
				header("Location: subirDocumento.php");				
			}
			else{
				//Se mueve el documento
                move_uploaded_file($_FILES["archivo"]["tmp_name"],$ruta_final.$nombre_documento);
			}			
			//geneneramos el ID
			$consulta4 = "select codigo_documento from documento order by codigo_documento desc limit 0,1";
			$resultado4 = $conexion_db->prepare($consulta4);
			$resultado4->execute();
			$resultado4->store_result();
			$ctdadDocumentos = $resultado4->num_rows;
			if($ctdadDocumentos == 0){
				$id = 1;	
			}
			else{
				$resultado4->bind_result($codigo_documento);
				$resultado4->fetch();
				$id = $codigo_documento + 1;	
			}
			
			//LLenamos el array de destinatarios
			for($i=count($identificador_destinatario);$i<10;$i++){
				$identificador_destinatario[$i] = 0;
            }
            
            //Guardamos en tabla documentos
			$consulta5 = "insert into documento (codigo_documento, contrato_documento, informe_documento, subInforme_documento, variosSub_documento, ".
            "numero_documento, fecha_documento, fechaSubida_documento, horaSubida_documento, destinatario_documento1, destinatario_documento2, ".
            "destinatario_documento3, destinatario_documento4, destinatario_documento5, destinatario_documento6, destinatario_documento7, ".
            "destinatario_documento8, destinatario_documento9, destinatario_documento10, descripcion_documento, nombre_documento, ruta_documento, ".
            "idDueno_documento) values (".$id.", ".$identificador_contrato.", ".$identificador_informe.", ".$identificador_subInforme.", ".$variosSub_documento.
            ", ".$identificador_numero.", '".$fecha_documento."', current_date(), current_time(), ".$identificador_destinatario[0].", ".
            $identificador_destinatario[1].", ".$identificador_destinatario[2].", ".$identificador_destinatario[3].", ".$identificador_destinatario[4].", ".
            $identificador_destinatario[5].", ".$identificador_destinatario[6].", ".$identificador_destinatario[7].", ".$identificador_destinatario[8].", ".
            $identificador_destinatario[9].", '".$comentario_documento."', '".$nombre_documento."', '".($ruta_final.$nombre_documento)."', ".
            $_SESSION["IDENTIFICADOR_USUARIO"].")";
            
			$resultado5 = $conexion_db->prepare($consulta5);
			$resultado5->execute();
			
            //Obtenemos fecha subida y hora subida
			$consulta6 = "select fechaSubida_documento, horaSubida_documento from documento where codigo_documento = ".$id;
			$resultado6 = $conexion_db->prepare($consulta6);
			$resultado6->execute();
			$resultado6->store_result();
			$resultado6->bind_result($fecha_subida,$hora_subida);
			$resultado6->fetch();
			
            //Guardamos en la tabla historial		
			$mensaje = strval("Documento subido por el usuario ".$_SESSION['NOMBRE']." ".$_SESSION['APELLIDO']." (".$_SESSION['USUARIO'].
			"), el d&iacute;a ".fecha($fecha_subida)." a las ".$hora_subida." horas.");		
            	
			$consulta6 = "insert into historial (documento_historial, mensaje_historial) values (".$id.", '".$mensaje."')";		
			$resultado6 = $conexion_db->prepare($consulta6);
			$resultado6->execute();			
			
            //Cerramos las conexiones
			if(isset($resultado)){ $resultado->close(); } 
			if(isset($resultado2)){ $resultado2->close(); } 
			if(isset($resultado3)){ $resultado3->close(); } 
			if(isset($resultado4)){ $resultado4->close(); } 
			if(isset($resultado5)){ $resultado5->close(); } 
			if(isset($resultado6)){ $resultado6->close(); } 
            if(isset($resultado7)){ $resultado7->close(); }
            if(isset($resultado8)){ $resultado8->close(); }
            if(isset($resultado9)){ $resultado9->close(); }
            if(isset($resultado10)){ $resultado10->close(); }
            if(isset($resultado11)){ $resultado11->close(); }
            if(isset($resultado12)){ $resultado12->close(); }
            if(isset($resultado13)){ $resultado13->close(); }
			$conexion_db->close();
			
            //Variables de sesión				
			$_SESSION["TEMP_OK_FILE"] = "SI";
			
            //Redireccionar
			header("Location: subirDocumento.php");
		}		
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
		$string = str_replace("  ", " ", $string);
		$string = str_replace(" ", "_", $string);
	   	return $string;
	}
	//Sanear un string
	function sanear_string_espacio($string){
		
		$string = trim($string);	//Elimina espacios en blanco al principio y al final
		
		$string = str_replace(array('á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);		
    	$string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
		$string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
	    $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
	    $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
    	$string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);				
		$string = str_replace(array("\\", "¨", "º", "°", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", 
									"¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", "."), '', $string);
		//$string = str_replace(" ", "_", $string);
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
