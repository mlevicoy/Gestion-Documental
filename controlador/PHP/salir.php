<?php
	//Libreria
	require_once("conexion.php");	
	
	//Iniciar la sesión
	session_start();

	//Cambiarmos al usuario como no conectado
	$consulta = "update usuario set conectado_usuario = 0 where nombre_usuario = '".$_SESSION["USUARIO"]."'";
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();
	$resultado->close();
	$conexion_db->close();
	
	//Matamos la session y las variables
	$_SESSION = array();
	if(ini_get("session.use_cookies")){
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 4200, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}
	session_destroy();
	
	//Retornamos a index.php
	header("Location: ../../index.php");
?>