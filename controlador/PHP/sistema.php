<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	/*Verificamos la si hay alguna actualizacion activa*/
	$consulta = "select actualizacion_activa from contrato where id_contrato = ".$_SESSION["CODIGOCONTRATO"];
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();
	$resultado->store_result();
	$resultado->bind_result($actualizacion_activa);
	$resultado->fetch();
	if($actualizacion_activa == 1){		
		header("Location: actualizacionFechas.php");		
	}
	
	if(!isset($_GET["OPT1"])){		
		$tpl = new TemplatePower("../../interfaz/HTML/sistema.html");
		$tpl->prepare();		
		$tpl->assign("CONTRATO",$_SESSION["NOMBRECONTRATO"]);
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);		
        $tpl->assign("DISPLAY_MENU3",'none;');
		$tpl->assign("PANTALLA_BUSCADOR","block"); 
		$tpl->assign("CODIGO_CONTRATO", $_SESSION["CODIGOCONTRATO"]);
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU",''); }
		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }	
        if(strcmp($_SESSION["TIPOCUENTA"], "Usuario Avanzado") == 0 || strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU2",''); }
		else{ $tpl->assign("DISPLAY_MENU2",'pointer-events: none;cursor: default;'); }	
		$tpl->assign("ID_SECTION","id_categorias");
		$tpl->assign("CLASE_IMAGEN","botones_imagen");		
		$tpl->assign("ESTILO_CARRUCEL","div_sistema");
			
        /*Obtenemos los codigos informes asociados al contrato*/		
        $consulta = "select informe_informeContrato from informeContrato where contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"];
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($informe_informeContrato);
		while($resultado->fetch()){
			//Obtenemos el nombre del informe asociado al contrato y habilitado
			$consulta2 = "select codigo_informe, nombre_informe from informe where codigo_informe = ".$informe_informeContrato." and estado_informe = 1";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
			$resultado2->bind_result($codigo_informe, $nombre_informe);
			$resultado2->fetch();
			
			//Agregamos las puertas
			$tpl->newBlock("CARRUSEL"); 
			$tpl->assign("DESCRIPCION_PUERTA",$nombre_informe);
			$tpl->assign("PAGINA_REDIRECCION","sistema.php?OPT1=".$_SESSION["CODIGOCONTRATO"]."&OPT2=".$codigo_informe);		
		}
		
		//Buscador
		if(!isset($_GET["doc"])){
			//Cargamos contratos
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALUE_INFORME_SELECTED","");
			$tpl->assign("TEXT_INFORME_SELECTED","--- BUSCAR DOCUMENTO ---");
			$tpl->assign("VALUE_SUBINFORME_SELECTED","");
			$tpl->assign("TEXT_SUBINFORME_SELECTED","--- BUSCAR SUB DOCUMENTO ---");			
			/*Obtenemos los codigos informes asociados al contrato*/		
			$consulta = "select informe_informeContrato from informeContrato where contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"];
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($informe_informeContrato);
			while($resultado->fetch()){
				//Obtenemos el nombre del informe asociado al contrato y habilitado
				$consulta2 = "select codigo_informe, nombre_informe from informe where codigo_informe = ".$informe_informeContrato." and estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($codigo_informe, $nombre_informe);
				$resultado2->fetch();
							
				$tpl->newBlock("INFORME_BUSCAR"); 
				$tpl->assign("VALUE_INFORME_UNSELECT",$codigo_informe);			
				$tpl->assign("TEXT_INFORME_UNSELECT",$nombre_informe);			
			}
		}
		else if(isset($_GET["doc"])){
			//Cargamos contratos
			$tpl->gotoBlock("_ROOT");
			$tpl->assign("VALUE_SUBINFORME_SELECTED","");
			$tpl->assign("TEXT_SUBINFORME_SELECTED","--- BUSCAR SUB DOCUMENTO ---");
			/*Obtenemos los codigos informes asociados al contrato*/		
			$consulta = "select informe_informeContrato from informeContrato where contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"];
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();						
			$resultado->store_result();
			$resultado->bind_result($informe_informeContrato);
			while($resultado->fetch()){
				//Obtenemos el nombre del informe asociado al contrato y habilitado
				$consulta2 = "select codigo_informe, nombre_informe from informe where codigo_informe = ".$informe_informeContrato." and estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($codigo_informe, $nombre_informe);
				$resultado2->fetch();
				
				if($_GET["doc"] == $codigo_informe){
					$tpl->gotoBlock("_ROOT");			
					$tpl->assign("VALUE_INFORME_SELECTED",$codigo_informe);
					$tpl->assign("TEXT_INFORME_SELECTED",$nombre_informe);
				}
				else{
					$tpl->newBlock("INFORME_BUSCAR"); 
					$tpl->assign("VALUE_INFORME_UNSELECT",$codigo_informe);			
					$tpl->assign("TEXT_INFORME_UNSELECT",$nombre_informe);				
				}
			}
			$tpl->newBlock("INFORME_BUSCAR"); 
			$tpl->assign("VALUE_INFORME_UNSELECT","");			
			$tpl->assign("TEXT_INFORME_UNSELECT","--- BUSCAR DOCUMENTO ---");				
						
			//Cargamos los informes
			$consulta3 = "select subInforme_subInformeInforme from subinformeinforme where contrato_subInformeInforme = ".$_SESSION["CODIGOCONTRATO"].
				" and informe_subInformeInforme = ".$_GET["doc"];
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($subInforme_subInformeInforme);			
			while($resultado3->fetch()){			
				//Obtenemos el nombre de los sub informes
				$consulta4 = "select codigo_subInforme, nombre_subInforme from subinforme where codigo_subInforme = ".$subInforme_subInformeInforme.
					" and estado_subInforme = 1";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($codigo_subInforme, $nombre_subInforme);
				$resultado4->fetch();
				
				$tpl->newBlock("SUBINFORME_BUSCAR"); 
				$tpl->assign("VALUE_SUBINFORME_UNSELECT",$codigo_subInforme);			
				$tpl->assign("TEXT_SUBINFORME_UNSELECT",$nombre_subInforme);
			}						
		}
		if(isset($resultado)){ $resultado->close(); }
		if(isset($resultado2)){ $resultado2->close(); }
		if(isset($resultado3)){ $resultado3->close(); }
		if(isset($resultado4)){ $resultado4->close(); }
		
		$tpl->printToScreen();	
	}
	else{
		//Datos $_GET
		$id_contrato = $_GET["OPT1"];
		$id_informe = $_GET["OPT2"];
		
		$tpl = new TemplatePower("../../interfaz/HTML/sistema.html");
		$tpl->prepare();
        $tpl->assign("CONTRATO",$_SESSION["NOMBRECONTRATO"]);
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
        $tpl->assign("DISPLAY_MENU3",'block;');	
		$tpl->assign("PANTALLA_BUSCADOR","none"); 
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU",''); }
		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }
        if(strcmp($_SESSION["TIPOCUENTA"], "Usuario Avanzado") == 0 || strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){ $tpl->assign("DISPLAY_MENU2",''); }
		else{ $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;'); }
		$tpl->assign("ID_SECTION","id_documentos");
		$tpl->assign("CLASE_IMAGEN","botones_imagen2");		
		$tpl->assign("ESTILO_CARRUCEL","div_documento");
		
		//Obtenemos los codigos subinformes asociados al contrato e informe
		$consulta = "select subInforme_subInformeInforme from subInformeInforme where informe_subInformeInforme = ".$id_informe.
        " and contrato_subInformeInforme = ".$id_contrato;
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($subInforme_subInformeInforme);
        if($resultado->num_rows == 0){            
            if(isset($resultado)){$resultado->close();}
            $conexion_db->close();
            header("Location: estantes.php?OPT1=".$id_contrato."&OPT2=".$id_informe."&OPT3=-1&PG=1");            
        }        
        else{
            while($resultado->fetch()){
    			//Obtenemos el nombre del subInforme
    			$consulta2 = "select nombre_subInforme from subInforme where codigo_subInforme = ".$subInforme_subInformeInforme;
    			$resultado2 = $conexion_db->prepare($consulta2);
    			$resultado2->execute();
    			$resultado2->store_result();
    			$resultado2->bind_result($nombre_subInforme);
    			$resultado2->fetch();
			
    			//Agregamos las puertas
    			$tpl->newBlock("CARRUSEL"); 
    			$tpl->assign("DESCRIPCION_PUERTA",$nombre_subInforme);
    			$tpl->assign("PAGINA_REDIRECCION","estantes.php?OPT1=".$id_contrato."&OPT2=".$id_informe."&OPT3=".$subInforme_subInformeInforme."&PG=1");		
            }	
            if(isset($resultado)){$resultado->close();}
            if(isset($resultado2)){$resultado2->close();}
            $conexion_db->close();
            $tpl->printToScreen();
      	}
	}
?>