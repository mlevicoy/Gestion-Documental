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
        
           	//Dividimos la cantidad de documentos en parte entera y decimal   
           	$cantidadDocumentoArray = explode(".",$cantidadDocumento/24);           	
			if($cantidadDocumentoArray[1] != 0){ $muebles = $cantidadDocumentoArray[0] + 1; }
           	else{ $muebles = $cantidadDocumentoArray[0]; }
        
		   	//Trabajamos cuando hay documentos
           	if($muebles == 0){
				$tpl->assign("TITULO_CARTEL","NO HAY DOCUMENTOS");
            	for($i=1;$i<=8;$i++){ $tpl->assign("MUEBLE".$i,"none"); }
            }
            else{
               	//Eliminamos los muebles que no tengan documentos
               	for($i=8;$i>$muebles;$i--){ $tpl->assign("MUEBLE".$i,"none"); }
               	for($i=1;$i<=$muebles;$i++){ $tpl->assign("MUEBLE".$i,"inline"); }
               	//Eliminamos los archivadores que no tienen información
               	for($i=224;$i>$cantidadDocumento;$i--){ $tpl->assign("ARCHIVADOR".$i,"none"); }            
               	for($i=1;$i<=$cantidadDocumento;$i++){ $tpl->assign("ARCHIVADOR".$i,"inline"); }
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
               	while($resultado->fetch()){
					//Obtenemos el nombre del usuario
					$consulta3 = "SELECT `nombre_datosUsuario`, `apellidos_datosUsuario` FROM `datosusuario` WHERE `codigo_datosUsuario` = ".$idDueno_documento;
					$resultado3 = $conexion_db->prepare($consulta3);
					$resultado3->execute();
					$resultado3->store_result();
					$resultado3->bind_result($nombre_datosUsuario, $apellidos_datosUsuario);
					$resultado3->fetch();
									
					//Datos modal					
					$tpl->assign("NOMBRE".$i, $nombre_documento);
					$tpl->assign("SUBIDOPOR".$i, strtoupper($nombre_datosUsuario." ".$apellidos_datosUsuario));
					$tpl->assign("FECHASUBIDA".$i, $fechaSubida_documento);
					$tpl->assign("FECHAENTREGA".$i, $fecha_documento);
					$tpl->assign("COMENTARIO".$i, $descripcion_documento); 
					//Ruta documento
               	    $tpl->assign("RUTA".$i,$ruta_documento);
    				$tpl->assign("IDENTIFICADOR".$i,$codigo_documento);
                   
                   	//Fecha documento
                   	$fecha_dividida = explode("-",$fecha_documento);                
                   	$tpl->assign("ANO".$i,$fecha_dividida[0]);
                   	$tpl->assign("MES".$i,fn_fecha($fecha_dividida[1]));
                   	
                   	//Numero del documento                
                   	if($numero_documento < 10){
                   	    $tpl->assign("NUMERO".$i,"0".$numero_documento);
                   	}
                   	else{
                	       $tpl->assign("NUMERO".$i,$numero_documento);
               	   }				
                                  
               	   $tpl->assign("TITULO".$i,$nombre_subinforme." N&deg; ".$numero_documento);					   			   
				   
               	   $i++;
               	}
				//Posicionamos los muebles
				if($muebles == 1){
					$posInicial = 44.25;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 2){
					$posInicial = 38.425;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 3){
					$posInicial = 32.6;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 4){
					$posInicial = 26.775;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);                
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA4",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 5){
					$posInicial = 20.95;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);                
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA4",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA5",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 6){
					$posInicial = 15.125;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);                
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA4",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA5",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA6",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 7){
					$posInicial = 9.3;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);                
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA4",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA5",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA6",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA7",$posInicial);
					$posInicial = 0;
				}
				else if($muebles == 8){
					$posInicial = 3.475;
					$tpl->assign("IZQUIERDA1",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA2",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA3",$posInicial);                
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA4",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA5",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA6",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA7",$posInicial);
					$posInicial = $posInicial + 0.15;
					$tpl->assign("IZQUIERDA8",$posInicial);
					$posInicial = 0;
				}                
				$resultado2->close();                        
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