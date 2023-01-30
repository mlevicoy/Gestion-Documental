<?php
/*LIBRERIAS*/
require_once("controlador/PHP/conexion.php");

//Obtenemos el codigo_documento  y la descripcion_documento
$consulta = "select codigo_documento, descripcion_documento from documento where contrato_documento = 4";
$resultado = $conexion_db->prepare($consulta);
$resultado->execute();
$resultado->store_result();
$resultado->bind_result($codigo_documento, $descripcion_documento);
while($resultado->fetch()){
	/*$consulta2 = "update documento set descripcion_documento = '".strtoupper(sanear_string_espacio(trim(html_entity_decode($descripcion_documento)))).
	"' where codigo_documento = ".$codigo_documento;	
	$resultado2 = $conexion_db->prepare($consulta2);
	$resultado2->execute();
	echo strtoupper(sanear_string_espacio(trim(html_entity_decode($descripcion_documento))))."<br>";*/
	echo $descripcion_documento."<br>";
	
}
		
			//Sanear un string
function sanear_string_espacio($string){
		
	$string = trim($string);	//Elimina espacios en blanco al principio y al final
	
	$string = str_replace(array('á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);		
   	$string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
	$string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
    $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
    $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
   	$string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);				
	$string = str_replace(array("\\", "¨", "º", "°", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", 
									"¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", "."), '', $string);
	//$string = str_replace(" ", "_", $string);
   	return $string;
}
 ?>