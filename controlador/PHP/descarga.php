<?php
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	//Ruta e id del documento
	$ruta = $_GET["ruta"];
	$id = $_GET["id"];
	
	//Se comienza el codigo de descarga para el archivo
	$nombre = basename($ruta);		
	set_time_limit(0);
 	ini_set('memory_limit', '1024M');
	ob_clean();
	fflush();
 	// http headers para descargar
 	header("Pragma: public");
 	header("Expires: 0");
 	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 	header("Cache-Control: public");
 	header("Content-Description: File Transfer");
 	header("Content-type: application/octet-stream");
 	header("Content-Disposition: attachment; filename=\"".basename($ruta)."\"");
 	//header("Content-Transfer-Encoding: binary");
	header("Content-Transfer-Encoding: chunked");
 	header("Content-Length: ".filesize($ruta));
 	ob_end_flush();
 	@readfile($ruta);	
	
	//Se actualiza la base de datos: historia
	$fecha_historial = date("Y-m-d");
	$hora_historial = date("H:m:s");		
	$mensaje = strval("Documento descargado por el usuario ".$_SESSION['NOMBRE']." ".$_SESSION['APELLIDO']." (".$_SESSION['USUARIO'].
	"), el d&iacute;a ".fecha($fecha_historial)." a las ".$hora_historial." horas.");	
	$consulta = "insert into historial (documento_historial, mensaje_historial) values (".$id.", '".$mensaje."')";		
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();
	$conexion_db->close();
	
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
	
	
	/*
	//Dividimos el nombre para obtener la extensión
	$dividirNombre = explode(".",$nombre);
	$extension = $dividirNombre[count($dividirNombre)-1];
	$extension = strtolower($extension);	
	//Opcional de aplicación
	if(strcmp($extension,"pdf") == 0){	
		header("Content-type: application/force-download");
		header("Content-disposition: attachment; filename=".$nombre);
		header('Content-Transfer-Encoding: binary');
		//header("Content-type: application/pdf");	
		header ("Content-Length: ".filesize($ruta));		
		readfile($ruta);		
		echo "Entre";
		exit;		
	}
	else if(strcmp($extension,"doc") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/msword");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);
	}
	else if(strcmp($extension,"docx") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);
	}
	else if(strcmp($extension,"xls") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/vnd.ms-excel");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);	
	}
	else if(strcmp($extension,"xlsx") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);	
	}
	else if(strcmp($extension,"ppt") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/vnd.ms-powerpoint");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);		
	}
	else if(strcmp($extension,"pptx") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);
	}
	else if(strcmp($extension,"jpg") == 0 || strcmp($extension,"jpeg") == 0 || strcmp($extension,"jpe") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: image/jpeg");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);
	}
	else if(strcmp($extension,"bmp") == 0){
		header("Content-disposition: attachment; filename=".$nombre);
		header("Content-type: image/bmp");
		header ("Content-Length: ".filesize($ruta));
		readfile($ruta);
	}	
	//Redireccionamos
	header("Location: trabajarDocumento.php");
	*/
?>