<?PHP
require_once("conexion.php");
/*header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");*/

//Iniciar la sesión
session_cache_limiter("nocache, private");
session_start();

//Revisa si paso el tiempo de conexión
function validaTiempo(){	
	if(strcmp($_SESSION["CONECTADO"],"SI") == 0){	
		$fechaGuardada = $_SESSION["ULTIMO_ACCESO"];
		$fechaActual = date("Y-n-j H:i:s");
		$tiempoTranscurrido = (strtotime($fechaActual)-strtotime($fechaGuardada));
		//Se compara el tiempo (60 minutos)
		if($tiempoTranscurrido >= 3600){
			header("Location: salir.php");
		}
		else{
			$_SESSION["ULTIMO_ACCESO"] = date("Y-n-j H:i:s");
			return;
		}
	}
	else{
		header("Location: salir.php");
	}
}

function validarAdministrador(){
	if(strcmp($_SESSION["CONECTADO"],"SI") == 0 and strcmp($_SESSION["TIPOCUENTA"],"Administrador") == 0){
		return;
	}
	else{
		header("Location: salir.php");
	}
}
?>