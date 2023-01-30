<?PHP
	/*LIBRERIAS*/
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	if(!isset($_GET["id"]) and !isset($_GET["idvar"])){
		header("Location: sistema.php");
	}
    else if(isset($_GET["idvar"])){
        $idvarios = htmlentities(mb_strtolower(trim($_GET["idvar"]),'UTF-8')); 
        $rutavarios = htmlentities(mb_strtolower(trim($_GET["rutavar"]),'UTF-8'));
        $contratovar = htmlentities(mb_strtolower(trim($_GET["contratovar"]),'UTF-8'));
        $informevar = htmlentities(mb_strtolower(trim($_GET["informevar"]),'UTF-8'));
        $subinformevar = htmlentities(mb_strtolower(trim($_GET["subinformevar"]),'UTF-8'));
        
        if(file_exists($rutavarios)){
			//Eliminamos el documento
			unlink($rutavarios);
			//Eliminamos el registro de la tabla documento
			$consulta2 = "delete from documento where codigo_documento = ".$idvarios;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			//Eliminamos los registros de la tabla historial
			$consulta3 = "delete from historial where documento_historial = ".$idvarios;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			//Cerramos conexion y redireccionamos
			$conexion_db->close();
			$_SESSION["TEMP_ELIMINADO"] = "SI";
            header("Location: estantes_modal.php?OPT1=".$contratovar."&OPT2=".$informevar."&OPT3=".$subinformevar."&PG=".$_SESSION["GUARDAR_PAGINA_ACTUAL"]);            
		}
		else{
			//Cerramos conexion y redireccionamos
			$resultado->close();
			$conexion_db->close();
			$_SESSION["TEMP_NO_ELIMINADO"] = "SI";
			header("Location: estantes_modal.php?OPT1=".$contratovar."&OPT2=".$informevar."&OPT3=".$subinformevar."&PG=".$_SESSION["GUARDAR_PAGINA_ACTUAL"]);            
		}
    }
	else{		
		$identificador = htmlentities(mb_strtolower(trim($_GET["id"]),'UTF-8'));
		$contrato = htmlentities(mb_strtolower(trim($_GET["cn"]),'UTF-8'));
		$informe = htmlentities(mb_strtolower(trim($_GET["in"]),'UTF-8'));
		$subInforme = htmlentities(mb_strtolower(trim($_GET["sin"]),'UTF-8'));
		
		//Obtenemos la ruta del documento
        $consulta = "select ruta_documento from documento where codigo_documento = ".$identificador;
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();
		$resultado->bind_result($ruta_documento);
		$resultado->fetch();
		if(file_exists($ruta_documento)){
			//Eliminamos el documento
			unlink($ruta_documento);
			//Eliminamos el registro de la tabla documento
			$consulta2 = "delete from documento where codigo_documento = ".$identificador;
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			//Eliminamos los registros de la tabla historial
			$consulta3 = "delete from historial where documento_historial = ".$identificador;
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			//Cerramos conexion y redireccionamos
			$resultado->close();
			$conexion_db->close();
			$_SESSION["TEMP_ELIMINADO"] = "SI";
			header("Location: estantes.php?OPT1=".$contrato."&OPT2=".$informe."&OPT3=".$subInforme."&PG=".$_SESSION["GUARDAR_PAGINA_ACTUAL"]);            
		}
		else{
			//Cerramos conexion y redireccionamos
			$resultado->close();
			$conexion_db->close();
			$_SESSION["TEMP_NO_ELIMINADO"] = "SI";
			header("Location: estantes.php?OPT1=".$contrato."&OPT2=".$informe."&OPT3=".$subInforme."&PG=".$_SESSION["GUARDAR_PAGINA_ACTUAL"]);            
		}	
	}
?>