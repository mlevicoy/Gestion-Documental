<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	if(!isset($_GET["OPT1"])){
        header("Location: sistema.php");			
	}
	else{
		//Datos $_GET
		$id_contrato = $_GET["OPT1"];
		$id_informe = $_GET["OPT2"];
		$id_subInforme = $_GET["OPT3"];
		$paginacion = $_GET["PG"];		
		
		$_SESSION["ESTANTE_CONTRATO"] = $id_contrato;
		$_SESSION["ESTANTE_INFORME"] = $id_informe;
		$_SESSION["ESTANTE_SUBINFORME"] = $id_subInforme;
		
        if($id_subInforme == -1){
            header("Location: estantes_modal.php?OPT1=".$id_contrato."&OPT2=".$id_informe."&OPT3=".$id_subInforme."&PG=".$paginacion);
        }
        else{
            $tpl = new TemplatePower("../../interfaz/HTML/estantes.html");
    		$tpl->prepare();
			$tpl->assign("NOMBRECONTRATO",$_SESSION["NOMBRECONTRATO"]);
    		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
    		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
    		$tpl->assign("CARGO",$_SESSION["CARGO"]);
    		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
    		$tpl->assign("CORREO",$_SESSION["CORREO"]);		
			$tpl->assign("CONTRATO",$id_contrato);
			$tpl->assign("INFORME",$id_informe);
			$tpl->assign("SUBINFORME",$id_subInforme);
			if(isset($_SESSION["TEMP_ELIMINADO"]) and strcmp($_SESSION["TEMP_ELIMINADO"],"SI") == 0){
				$tpl->assign("PANTALLA_MENSAJE_AVISO","block");
				$tpl->assign("MENSAJE_AVISO","DOCUMENTO ELIMINADO CORRECTAMENTE");
				unset($_SESSION["TEMP_ELIMINADO"]);	
			}
			else{ $tpl->assign("PANTALLA","none"); }
			if(isset($_SESSION["TEMP_NO_ELIMINADO"]) and strcmp($_SESSION["TEMP_NO_ELIMINADO"],"SI") == 0){
				$tpl->assign("PANTALLA_MENSAJE_AVISO","block");
				$tpl->assign("MENSAJE_AVISO","ERROR: NO SE PUDO ELIMINAR EL DOCUMENTO");
				unset($_SESSION["TEMP_NO_ELIMINADO"]);
			}
			else{ $tpl->assign("PANTALLA","none"); }
            if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU",''); }
    		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }
            if(strcmp($_SESSION["TIPOCUENTA"], "Usuario Avanzado") == 0 || strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU2",''); }
    		else{ $tpl->assign("DISPLAY_MENU2",'pointer-events: none;cursor: default;'); }
    		$tpl->assign("CLASE_IMAGEN","botones_imagen");		
			
            //Obtenemos los informes
    		$consulta = "select codigo_documento, numero_documento, fecha_documento, fechaSubida_documento, horaSubida_documento, descripcion_documento, ".
    		"nombre_documento, ruta_documento, idDueno_documento from documento where contrato_documento = ".$id_contrato." and informe_documento = ".$id_informe.
    		" and subInforme_documento = ".$id_subInforme;
    		$resultado = $conexion_db->prepare($consulta);
    		$resultado->execute();        
    		$resultado->store_result();
            $cantidadDocumento = $resultado->num_rows;			
    		$resultado->bind_result($codigo_documento, $numero_documento, $fecha_documento, $fechaSubida_documento, $horaSubido_documento, $descripcion_documento, 
            $nombre_documento, $ruta_documento, $idDueno_documento);
        
			//Calculamos la cantidad de paginas
			$cantidadPaginasArray = explode(".",$cantidadDocumento/72);	//Ya que son 72 archivos por pagina           	
			if($cantidadPaginasArray[1] != 0){ $paginas = $cantidadPaginasArray[0] + 1; }
           	else{ $paginas = $cantidadPaginasArray[0]; }
			
			//Dividimos la cantidad de documentos en parte entera y decimal	-	Obtenemos la cantidad de documentos   
           	$cantidadDocumentoArray = explode(".",$cantidadDocumento/24);           	
			if($cantidadDocumentoArray[1] != 0){ $muebles = $cantidadDocumentoArray[0] + 1; }
           	else{ $muebles = $cantidadDocumentoArray[0]; }
			
			if($paginacion == 1){				
				$_SESSION["GUARDAR_PAGINA_ACTUAL"] = 1; 				
				$comenzarConArchivador = 1;
				$tpl->assign("NRO_PAGINA_ACTUAL", 1);
				$tpl->assign("NRO_PAGINA_TOTAL", $paginas);
				$tpl->assign("ACTIVO_PRIMERA",'class="active" style="pointer-events: none;cursor: default;"');
				$tpl->assign("ACTIVO_ANTERIOR",'style="pointer-events: none;cursor: default;"');
				
				$tpl->assign("PAGINA_PRIMERA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
				
				$tpl->assign("PAGINA_ANTERIOR",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
				
				$tpl->assign("PAGINA_SIGUIENTE",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
				
				$tpl->assign("PAGINA_ULTIMA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
				//Validamos que hayan más páginas
				if($paginacion == $paginas){
					$tpl->assign("ACTIVO_SIGUIENTE",'style="pointer-events: none;cursor: default;"');
					$tpl->assign("ACTIVO_ULTIMA",'style="pointer-events: none;cursor: default;"');						
				}
				else{
					$tpl->assign("ACTIVO_SIGUIENTE",'');
					$tpl->assign("ACTIVO_ULTIMA",'');				
				}				
			}
			else if(strcmp($paginacion, "ANTERIOR") == 0){						
				//Actualizamos la pagina actual
				$_SESSION["GUARDAR_PAGINA_ACTUAL"] = $_SESSION["GUARDAR_PAGINA_ACTUAL"] - 1;	 								
				$tpl->assign("NRO_PAGINA_ACTUAL", $_SESSION["GUARDAR_PAGINA_ACTUAL"]);
				$tpl->assign("NRO_PAGINA_TOTAL", $paginas);				
				//Calculamos comenzar con archivador
				$comenzarConArchivador = 1;
				for($i=1;$i<$_SESSION["GUARDAR_PAGINA_ACTUAL"];$i++){
					$comenzarConArchivador = $comenzarConArchivador + 72;
				}
				//Vemos si existe pagina anterior
				if($_SESSION["GUARDAR_PAGINA_ACTUAL"] == 1){
					$tpl->assign("ACTIVO_PRIMERA",'class="active" style="pointer-events: none;cursor: default;"');	
					$tpl->assign("ACTIVO_ANTERIOR",'style="pointer-events: none;cursor: default;"');
				}
				else{
					$tpl->assign("ACTIVO_PRIMERA",'');	
					$tpl->assign("ACTIVO_ANTERIOR",'class="active"');
				}
				//Vemos si existe una pagina siguiente
				$tpl->assign("ACTIVO_SIGUIENTE",'');
				$tpl->assign("ACTIVO_ULTIMA",'');								
				//Colocamos las direcciones
				$tpl->assign("PAGINA_PRIMERA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
				$tpl->assign("PAGINA_ANTERIOR",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
				$tpl->assign("PAGINA_SIGUIENTE",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
				$tpl->assign("PAGINA_ULTIMA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');				
			}
			else if(strcmp($paginacion, "SIGUIENTE") == 0){						
				//Actualizamos la pagina actual
				$_SESSION["GUARDAR_PAGINA_ACTUAL"] = $_SESSION["GUARDAR_PAGINA_ACTUAL"] + 1;	 								
				$tpl->assign("NRO_PAGINA_ACTUAL", $_SESSION["GUARDAR_PAGINA_ACTUAL"]);
				$tpl->assign("NRO_PAGINA_TOTAL", $paginas);	
				//Calculamos comenzar con archivador
				$comenzarConArchivador = 1;
				for($i=1;$i<$_SESSION["GUARDAR_PAGINA_ACTUAL"];$i++){
					$comenzarConArchivador = $comenzarConArchivador + 72;
				}				
				//Vemos si existe pagina siguiente
				if($_SESSION["GUARDAR_PAGINA_ACTUAL"] == $paginas){
					$tpl->assign("ACTIVO_SIGUIENTE",'style="pointer-events: none;cursor: default;"');
					$tpl->assign("ACTIVO_ULTIMA",'class="active" style="pointer-events: none;cursor: default;"');	
				}
				else{
					$tpl->assign("ACTIVO_SIGUIENTE",'class="active"');
					$tpl->assign("ACTIVO_ULTIMA",'');													
				}
				//Veamos la primera y pagina siguiente				
				$tpl->assign("ACTIVO_PRIMERA",'');	
				$tpl->assign("ACTIVO_ANTERIOR",'');			
				//Vemos si existe una pagina siguiente
					//Colocamos las direcciones
				$tpl->assign("PAGINA_PRIMERA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
				$tpl->assign("PAGINA_ANTERIOR",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
				$tpl->assign("PAGINA_SIGUIENTE",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
				$tpl->assign("PAGINA_ULTIMA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');				
			}
			else if(strcmp($paginacion, "ULTIMA") == 0){				
				//Actualizamos la pagina actual
				$_SESSION["GUARDAR_PAGINA_ACTUAL"] = $paginas;
				$tpl->assign("NRO_PAGINA_ACTUAL", $_SESSION["GUARDAR_PAGINA_ACTUAL"]);
				$tpl->assign("NRO_PAGINA_TOTAL", $paginas);				
				//Calculamos comenzar con archivador
				$comenzarConArchivador = 1;
				for($i=1;$i<$_SESSION["GUARDAR_PAGINA_ACTUAL"];$i++){
					$comenzarConArchivador = $comenzarConArchivador + 72;
				}
				//Vemos si existe pagina siguiente
				$tpl->assign("ACTIVO_SIGUIENTE",'style="pointer-events: none;cursor: default;"');
				$tpl->assign("ACTIVO_ULTIMA",'class="active" style="pointer-events: none;cursor: default;"');	
				//Veamos la primera y pagina siguiente				
				$tpl->assign("ACTIVO_PRIMERA",'');	
				$tpl->assign("ACTIVO_ANTERIOR",'');			
				//Vemos si existe una pagina siguiente
					//Colocamos las direcciones
				$tpl->assign("PAGINA_PRIMERA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
				$tpl->assign("PAGINA_ANTERIOR",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
				$tpl->assign("PAGINA_SIGUIENTE",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
				$tpl->assign("PAGINA_ULTIMA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
			}
            else{
                //Actualizamos la pagina actual
				$_SESSION["GUARDAR_PAGINA_ACTUAL"] = $paginacion;
				$tpl->assign("NRO_PAGINA_ACTUAL", $_SESSION["GUARDAR_PAGINA_ACTUAL"]);
				$tpl->assign("NRO_PAGINA_TOTAL", $paginas);				
				//Calculamos comenzar con archivador
				$comenzarConArchivador = 1;
				for($i=1;$i<$_SESSION["GUARDAR_PAGINA_ACTUAL"];$i++){
					$comenzarConArchivador = $comenzarConArchivador + 72;
				}				
				$tpl->assign("ACTIVO_SIGUIENTE",'class="active"');
				$tpl->assign("ACTIVO_ULTIMA",'');
				$tpl->assign("ACTIVO_PRIMERA",'');	
				$tpl->assign("ACTIVO_ANTERIOR",'');			
				//Colocamos las direcciones
				$tpl->assign("PAGINA_PRIMERA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
				$tpl->assign("PAGINA_ANTERIOR",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
				$tpl->assign("PAGINA_SIGUIENTE",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
				$tpl->assign("PAGINA_ULTIMA",'estantes.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
            }
            
			//Trabajamos cuando hay documentos
           	if($muebles == 0){
				$tpl->assign("TITULO_CARTEL","NO HAY DOCUMENTOS");
            	for($i=1;$i<=3;$i++){ $tpl->assign("MUEBLE".$i,"none"); }
				$tpl->assign("ACTIVO_PRIMERA",'style="pointer-events: none;cursor: default;"');					
				$tpl->assign("ACTIVO_ANTERIOR",'style="pointer-events: none;cursor: default;"');
				$tpl->assign("ACTIVO_SIGUIENTE",'style="pointer-events: none;cursor: default;"');
				$tpl->assign("ACTIVO_ULTIMA",'style="pointer-events: none;cursor: default;"');	
				$tpl->assign("NRO_PAGINA_ACTUAL", 0);
				$tpl->assign("NRO_PAGINA_TOTAL", 0);
            }
			else{               	               	
				for($i=1;$i<=72;$i++){ $tpl->assign("ARCHIVADOR".$i,"none"); }	//Eliminamos todos los archivadores
				//Cuantos hojas son
				if($_SESSION["GUARDAR_PAGINA_ACTUAL"] == $paginas){
					for($i=1;$i<=($cantidadDocumento-$comenzarConArchivador+1);$i++){ $tpl->assign("ARCHIVADOR".$i,"inline"); }
				}
				else{
					for($i=1;$i<=72;$i++){ $tpl->assign("ARCHIVADOR".$i,"inline"); }	
				}
				
				//Colocamos la información en los archivadores
					//Obtenemos el nombre del documento
               	$consulta2 = "select nombre_subinforme from subinforme where codigo_subinforme = ".$id_subInforme;
               	$resultado2 = $conexion_db->prepare($consulta2);
               	$resultado2->execute();
               	$resultado2->store_result();
               	$resultado2->bind_result($nombre_subinforme);
               	$resultado2->fetch();
				$tpl->assign("TITULO_CARTEL",$nombre_subinforme);  
				$tpl->assign("NOMBRECONTRATO", $_SESSION["NOMBRECONTRATO"]);              
               	$i = 1;
				$x=1;
               	while($resultado->fetch()){
					if($i < $comenzarConArchivador){
						$i++;	
					}
					else{						
						//Obtenemos el nombre del usuario
						$consulta3 = "SELECT `nombre_datosUsuario`, `apellidos_datosUsuario` FROM `datosusuario` WHERE `codigo_datosUsuario` = ".
							$idDueno_documento;
						$resultado3 = $conexion_db->prepare($consulta3);
						$resultado3->execute();
						$resultado3->store_result();
						$resultado3->bind_result($nombre_datosUsuario, $apellidos_datosUsuario);
						$resultado3->fetch();

						//Datos modal					
						$tpl->assign("NOMBRE".$x, $nombre_documento);
						$tpl->assign("SUBIDOPOR".$x, strtoupper($nombre_datosUsuario." ".$apellidos_datosUsuario));
						$tpl->assign("FECHASUBIDA".$x, $fechaSubida_documento);
						$tpl->assign("FECHAENTREGA".$x, $fecha_documento);
						$tpl->assign("COMENTARIO".$x, $descripcion_documento); 
						//Ruta documento
						$tpl->assign("RUTA".$x,$ruta_documento);
						$tpl->assign("IDENTIFICADOR".$x,$codigo_documento);

						//Fecha documento
						$fecha_dividida = explode("-",$fecha_documento);                
						$tpl->assign("ANO".$x,$fecha_dividida[0]);
						$tpl->assign("MES".$x,fn_fecha($fecha_dividida[1]));

						//Numero del documento                
						if($numero_documento < 10){
							$tpl->assign("NUMERO".$x,"0".$numero_documento);
						}
						else{
							   $tpl->assign("NUMERO".$x,$numero_documento);
					   }				

					   $tpl->assign("TITULO".$x,$nombre_subinforme." N&deg; ".$numero_documento);							
						
					   $x++;
					}
					$resultado2->close(); 
				}
			}
			$resultado->close();        
			$tpl->printToScreen();        	
		}
	}
    
    function fn_fecha($fecha_recibida){
        switch($fecha_recibida){
            case 1:
                return "ENE";
                break;
            case 2:
                return "FEB";
                break;
            case 3:
                return "MAR";
                break;
            case 4:
                return "ABR";
                break;
            case 5:
                return "MAY";
                break;
            case 6:
                return "JUN";
                break;
            case 7:
                return "JUL";
                break;
            case 8:
                return "AGO";
                break;
            case 9:
                return "SEP";
                break;
            case 10:
                return "OCT";
                break;
            case 11:
                return "NOV";
                break;
            case 12:
                return "DIC";
                break;
        }
    }
?>