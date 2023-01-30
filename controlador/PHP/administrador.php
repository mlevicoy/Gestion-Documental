<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validarAdministrador();
	validaTiempo();

	$consulta = "select distinct titulo_menu, icono_menu from menu";
	$resultado = $conexion_db->prepare($consulta);
	$resultado->execute();	
	$resultado->store_result();
	$resultado->bind_result($titulo_menu, $icono_menu);

	$tpl = new TemplatePower("../../interfaz/HTML/administrador.html");
	$tpl->prepare();
	$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
	$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
	$tpl->assign("CARGO",$_SESSION["CARGO"]);
	$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
	$tpl->assign("CORREO",$_SESSION["CORREO"]);
	while($resultado->fetch()){		
		$tpl->newBlock("BLOCK_TITULO");
		$tpl->assign("TITULO", ucwords(strtolower($titulo_menu)));		
		$tpl->assign("PAGINA","#");	
		$tpl->assign("ICONO", $icono_menu);	
		$consulta2 = "select subtitulo_menu, pagina_menu from menu where titulo_menu = '".$titulo_menu."'";
		$resultado2 = $conexion_db->prepare($consulta2);
		$resultado2->execute();
		$resultado2->bind_result($subtitulo_menu,$pagina_menu);		
		while($resultado2->fetch()){									
			if(strcmp($subtitulo_menu,"ZERO") !== 0){
				$tpl->newBlock("BLOCK_SUBTITULO");
				$tpl->assign("SUBTITULO",$subtitulo_menu);
				$tpl->assign("PAGINA2",$pagina_menu);			
			}
			else{
				$tpl->assign("PAGINA",$pagina_menu);			
			}
		}
	}
	$resultado->close();
	$resultado2->close();
	$conexion_db->close();	
	$tpl->printToScreen();
?>