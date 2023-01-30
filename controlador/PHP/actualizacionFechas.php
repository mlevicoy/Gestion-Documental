<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");
	require_once("conexion.php");
	require_once("sesiones.php");
	
	validaTiempo();
	
	//Carga Inicial
	if(!isset($_POST["actualizar"])){		
		$tpl = new TemplatePower("../../interfaz/HTML/actualizacionFechas.html");
		$tpl->prepare();		
		//Obtenemos los codigo de los informes asociados al contrato
		$consulta = "select informe_informeContrato from informecontrato where contrato_informeContrato = ".$_SESSION["CODIGOCONTRATO"];
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($informe_informeContrato);
		while($resultado->fetch()){
			//Obtenemos el nombre del informe asociado al contrato		
			$consulta2 = "select nombre_informe from informe where codigo_informe = ".$informe_informeContrato;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
			$resultado2->bind_result($nombre_informe);
			$resultado2->fetch();			
			$tpl->newBlock("FECHA_INFORME");
			$tpl->assign("INFORME", $nombre_informe);						
			//Obtenemos los codigos de los sub informes asociados al informe y al contrato
			$consulta3 = "select subInforme_subInformeInforme from subinformeinforme where informe_subInformeInforme = ".$informe_informeContrato.
				" and contrato_subInformeInforme = ".$_SESSION["CODIGOCONTRATO"];
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			$resultado3->bind_result($subInforme_subInformeInforme);
			while($resultado3->fetch()){
				//obtenemos el nombre del sub informe asociado al informe y contrato
				$consulta4 = "select nombre_subInforme from subinforme where codigo_subInforme = ".$subInforme_subInformeInforme;
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($nombre_subInforme);
				$resultado4->fetch();
				$tpl->newBlock("FECHA_SUBINFORME");
				$tpl->assign("SUBINFORME", "&nbsp;&nbsp;&nbsp;/--- ".$nombre_subInforme);	
				$tpl->assign("CODINFORME", $informe_informeContrato); 
				$tpl->assign("CODSUBINFORME", $subInforme_subInformeInforme); 					
			}		
		}
		if(isset($resultado)){$resultado->close();}
		if(isset($resultado2)){$resultado2->close();}
		if(isset($resultado3)){$resultado3->close();}
		if(isset($resultado4)){$resultado4->close();}
		$tpl->printToScreen();
	}
	else{
		$fecha_primer_envio = $_POST["fecha_envio"];
		$periodicidad = $_POST["periodicidad"];
		$codigo_informe = $_POST["codInforme"];
		$codigo_subinforme = $_POST["codSubInforme"];
		
		//Actualizamos la Base de Datos
		for($i=0;$i<count($fecha_primer_envio);$i++){
			$consulta = "update subinformeinforme set fecha_primerDocumento = '".$fecha_primer_envio[$i]."', periodicidad_documento = ".
			$periodicidad[$i]." where subInforme_subInformeInforme = ".$codigo_subinforme[$i]." and informe_subInformeInforme = ".
			$codigo_informe[$i]." and contrato_subInformeInforme = ".$_SESSION["CODIGOCONTRATO"];		
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();	
		}		
		
		//Actualizamos la tabla contrato
		$consulta = "update contrato set actualizacion_activa = 0 where id_contrato = ".$_SESSION["CODIGOCONTRATO"];
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		
		if(isset($resultado)){$resultado->close();}	
		
		header("Location: sistema.php");
	}
?>