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
		
        $tpl = new TemplatePower("../../interfaz/HTML/estantes_modal.html");
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
        
        //Obtenemos la cantidad de informesVarios
        $consulta = "select distinct variosSub_documento from documento where contrato_documento = ".$id_contrato." and informe_documento = ".$id_informe.
        " and subInforme_documento = ".$id_subInforme;
    	$resultado = $conexion_db->prepare($consulta);
    	$resultado->execute();        
    	$resultado->store_result();
        $cantidadDocumento = $resultado->num_rows;			
    	$resultado->bind_result($variosSub_documento);
		
		//Calculamos la cantidad de paginas
		$cantidadPaginasArray = explode(".",$cantidadDocumento/72);	//Ya que son 72 archivos por pagina           	
		if($cantidadPaginasArray[1] != 0){ $paginas = $cantidadPaginasArray[0] + 1; }
        else{ $paginas = $cantidadPaginasArray[0]; }
		
        //Dividimos la cantidad de documentos en parte entera y decimal   
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
			$tpl->assign("PAGINA_PRIMERA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');				
			$tpl->assign("PAGINA_ANTERIOR",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');				
			$tpl->assign("PAGINA_SIGUIENTE",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');				
			$tpl->assign("PAGINA_ULTIMA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
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
			$tpl->assign("PAGINA_PRIMERA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
			$tpl->assign("PAGINA_ANTERIOR",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
			$tpl->assign("PAGINA_SIGUIENTE",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
			$tpl->assign("PAGINA_ULTIMA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');				
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
			$tpl->assign("PAGINA_PRIMERA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
			$tpl->assign("PAGINA_ANTERIOR",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
			$tpl->assign("PAGINA_SIGUIENTE",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
			$tpl->assign("PAGINA_ULTIMA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');				
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
			$tpl->assign("PAGINA_PRIMERA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
			$tpl->assign("PAGINA_ANTERIOR",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
			$tpl->assign("PAGINA_SIGUIENTE",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
			$tpl->assign("PAGINA_ULTIMA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
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
			$tpl->assign("PAGINA_PRIMERA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=1');
			$tpl->assign("PAGINA_ANTERIOR",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ANTERIOR');
			$tpl->assign("PAGINA_SIGUIENTE",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=SIGUIENTE');
			$tpl->assign("PAGINA_ULTIMA",'estantes_modal.php?OPT1='.$_SESSION["ESTANTE_CONTRATO"].'&OPT2='.$_SESSION["ESTANTE_INFORME"].'&OPT3='.$_SESSION["ESTANTE_SUBINFORME"].'&PG=ULTIMA');
        }
				
        //Trabajamos cuando hay documentos
        if($muebles == 0){
			$tpl->assign("TITULO_CARTEL","NO HAY DOCUMENTOS");
            for($i=1;$i<=8;$i++){ $tpl->assign("MUEBLE".$i,"none"); }
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
			else{ for($i=1;$i<=72;$i++){ $tpl->assign("ARCHIVADOR".$i,"inline"); } }
           	
            //Colocamos la información en los archivadores
            
            //Obtenemos el nombre del documento
           	$consulta2 = "select nombre_informe from informe where codigo_informe = ".$id_informe;
           	$resultado2 = $conexion_db->prepare($consulta2);
            $resultado2->execute();
            $resultado2->store_result();
            $resultado2->bind_result($nombre_informe);
           	$resultado2->fetch();
			$tpl->assign("TITULO_CARTEL",$nombre_informe);  
			$tpl->assign("NOMBRECONTRATO", $_SESSION["NOMBRECONTRATO"]);              
            $i = 1;
            $x=1;
            while($resultado->fetch()){    
				if($i < $comenzarConArchivador){
					$i++;	
				}
				else{
					//Titulo
					$consulta4 = "select nombre_variosSubInformes from variossubinformes where id_variosSubInformes = ".$variosSub_documento;				
					$resultado4 = $conexion_db->prepare($consulta4);
					$resultado4->execute();
					$resultado4->store_result();
					$resultado4->bind_result($nombre_variosSubInformes);
					$resultado4->fetch();
					$tpl->assign("TITULO".$x,$nombre_variosSubInformes);
					//$tpl->assign("NUMERO".$i,"");

					$nombre_iniciales = fn_iniciales($nombre_variosSubInformes);

					//Estas son las inicales, no se cambio el nombre de las variables por cantidad
					$tpl->assign("ANO".$x,$nombre_iniciales[0]);
					$tpl->assign("MES".$x,$nombre_iniciales[1]); 
					$tpl->assign("NUMERO".$x,$nombre_iniciales[2]);
					$tpl->assign("CFINAL".$x,$nombre_iniciales[3]);   

					//Datos a enviar a JS
					//numero_documento, nombre_documento, descripcion_documento, ruta_documento
					$nrodocumento = "";
					$nomdocumento = "";
					$descdocumento = "";
					$rutadocumento = "";
					$coddocumento = "";
					$fechadocumento = "";
					$destdocumento = array();
					$nombredestdocumento = "";       					
					$consulta5 = "select codigo_documento, numero_documento, nombre_documento, descripcion_documento, ruta_documento, fecha_documento, ".
						"destinatario_documento1, destinatario_documento2, destinatario_documento3, destinatario_documento4, destinatario_documento5, ".
						"destinatario_documento6, destinatario_documento7, destinatario_documento8, destinatario_documento9, destinatario_documento10 ".
						"from documento where contrato_documento = ".$id_contrato." and informe_documento = ".$id_informe." and subInforme_documento = ".
						$id_subInforme." and variosSub_documento = ".$variosSub_documento;
					$resultado5 = $conexion_db->prepare($consulta5);
					$resultado5->execute();
					$resultado5->store_result();
					$resultado5->bind_result($codigo_documento, $numero_documento, $nombre_documento, $descripcion_documento, $ruta_documento, $fecha_documento, $destinatario_documento1, $destinatario_documento2, $destinatario_documento3, $destinatario_documento4, $destinatario_documento5, $destinatario_documento6, $destinatario_documento7, $destinatario_documento8, $destinatario_documento9, $destinatario_documento10);
					$m=0; //Contador de descripcion
					while($resultado5->fetch()){
						$k = 0; //Contador de destinatario					
						$coddocumento = $coddocumento.$codigo_documento."&";
						$nrodocumento = $nrodocumento.$numero_documento."&";
						$nomdocumento = $nomdocumento.$nombre_documento."&";
						$descdocumento = $descdocumento.$descripcion_documento."&";
						$rutadocumento = $rutadocumento.$ruta_documento."&";
						$fechadocumento = $fechadocumento.$fecha_documento."&";	
						//echo "DESCRIPCION".$m."<br>";
						//$tpl->assign("DESCRIPCION".$m,$descripcion_documento);					
						$m++;
						if($destinatario_documento1 != 0){
							$destdocumento[$k] = $destinatario_documento1;
							$k++;  
						} 
						if($destinatario_documento2 != 0){
							$destdocumento[$k] = $destinatario_documento2;
							$k++;
						}
						if($destinatario_documento3 != 0){
							$destdocumento[$k] = $destinatario_documento3;
							$k++;
						}
						if($destinatario_documento4 != 0){
							$destdocumento[$k] = $destinatario_documento4;
							$k++;
						}
						if($destinatario_documento5 != 0){
							$destdocumento[$k] = $destinatario_documento5;
							$k++;
						}
						if($destinatario_documento6 != 0){
							$destdocumento[$k] = $destinatario_documento6;
							$k++;
						}
						if($destinatario_documento7 != 0){
							$destdocumento[$k] = $destinatario_documento7;
							$k++;
						} 
						if($destinatario_documento8 != 0){
							$destdocumento[$k] = $destinatario_documento8;
							$k++;
						}
						if($destinatario_documento9 != 0){
							$destdocumento[$k] = $destinatario_documento9;
							$k++;
						}
						if($destinatario_documento10 != 0){
							$destdocumento[$k] = $destinatario_documento10;
							$k++;
						}
						$l=0;
						$cantidadConsulta6 = count($destdocumento);
						for($k=0;$k<count($destdocumento);$k++){
							$consulta6 = "select cargo_destinatario from destinatario where id_destinatario = ".$destdocumento[$k];
							$resultado6 = $conexion_db->prepare($consulta6);
							$resultado6->execute();
							$resultado6->store_result();                                                  
							$resultado6->bind_result($cargo_destinatario);
							$resultado6->fetch();                                                
							if($l == ($cantidadConsulta6 - 1)){
								$nombredestdocumento = $nombredestdocumento.$cargo_destinatario;
								$l++;
							}
							else{
								$nombredestdocumento = $nombredestdocumento.$cargo_destinatario.", ";
								$l++;     
							}                        
						}
						$nombredestdocumento = $nombredestdocumento."&";    
					}					
					//'{CODIGO1}','{NUMERO1}','{NOMBRE1}', '{DESCRIPCION1}', '{RUTA1}'				
					$tpl->assign("CODIGOVARIODOC".$x,$coddocumento);
					$tpl->assign("NUMEROVARIODOC".$x,$nrodocumento);
					$tpl->assign("NOMBREVARIODOC".$x,$nomdocumento);
					//$tpl->assign("DESCRIPCIONVARIODOC".$i,$descdocumento);
					$tpl->assign("COMENTARIOARCHIVADOR".$x,$descdocumento);
					$tpl->assign("RUTAVARIODOC".$x,$rutadocumento);
					$tpl->assign("CONTRATO",$id_contrato);
					$tpl->assign("INFORME",$id_informe);
					$tpl->assign("SUBINFORME", $id_subInforme);
					$tpl->assign("FECHADOC".$x, $fechadocumento);
					$tpl->assign("DESTDOC".$x, $nombredestdocumento);					
					$x++;				
					$resultado2->close();
				}
			}
		}
		if(isset($resultado)){ $resultado->close(); }        
		if(isset($resultado2)){ $resultado2->close(); }        
		if(isset($resultado3)){ $resultado3->close(); }        
		if(isset($resultado4)){ $resultado4->close(); }           
		$tpl->printToScreen();        	
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
    function fn_iniciales($texto_inicial){
        $texto = strtolower(trim($texto_inicial));
        $texto_array = explode(" ",$texto);
        
        for($i=0;$i<count($texto_array);$i++){
            if(strcmp($texto_array[$i],"a") == 0 || strcmp($texto_array[$i],"e") == 0 || strcmp($texto_array[$i],"i") == 0 || strcmp($texto_array[$i],"o") == 0 || 
            strcmp($texto_array[$i],"u") == 0 || strcmp($texto_array[$i],"la") == 0 || strcmp($texto_array[$i],"el") == 0 || strcmp($texto_array[$i],"de") == 0 || 
            strcmp($texto_array[$i],"del") == 0 || strcmp($texto_array[$i],"los") == 0 || strcmp($texto_array[$i],"las") == 0){
                unset($texto_array[$i]);
                $texto_array = array_values($texto_array);
                $i=0;
            }
        }

        for($i=0;$i<count($texto_array);$i++){
            $texto_array[$i] = ucwords(substr($texto_array[$i],0,1));
        }
        for($i=count($texto_array);$i<4;$i++){
            $texto_array[$i] = " ";
        }       
        return implode($texto_array);       
    }
?>