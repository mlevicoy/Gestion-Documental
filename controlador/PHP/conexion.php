<?PHP
	//Variables que se necesita para conectarse a la base de datos
	require_once("variables_conexion.php");
	
	//Se realiza la conexión
	$conexion_db = new mysqli(SERVIDOR_DB,USUARIO_DB,CONTRASENA_DB,NOMBRE_DB);

	//Verifica si la conexión es correcta
	if($conexion_db->connect_errno){
		echo "Error al tratar de conectarse a la base de datos: (".$conexion_db->connect_errno.") ".$conexion_db->connect_error;
		exit;
	}	
?>