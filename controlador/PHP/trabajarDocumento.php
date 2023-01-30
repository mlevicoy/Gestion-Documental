<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validaTiempo();
	
	if(!isset($_POST["cargador"])){					
		$tpl = new TemplatePower("../../interfaz/HTML/trabajarDocumento.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		/*if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
		}*/
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
		}
		else{
            $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
		}		
		//Respuesta automática
		if(isset($_SESSION["TEMP_NO_INFORMACION"]) and $_SESSION["TEMP_NO_INFORMACION"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","NO EXISTE INFORMACIÓN PARA ESTA BÚSQUEDA");					
			unset($_SESSION["TEMP_NO_INFORMACION"]);
		}
		if(isset($_SESSION["TEMP_OK_FILE"]) and $_SESSION["TEMP_OK_FILE"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","EL DOCUMENTO SE ACTUALIZ&Oacute; CORRECTAMENTE");					
			unset($_SESSION["TEMP_NO_INFORMACION"]);
		}
		if(isset($_SESSION["TEMP_ERROR_ARCHIVO"]) and $_SESSION["TEMP_ERROR_ARCHIVO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","ERROR: ".$_SESSION["TEMP_ERROR_FILE"]);		
			unset($_SESSION["TEMP_ERROR_ARCHIVO"]);
			unset($_SESSION["TEMP_ERROR_FILE"]);
		}		
		else if(isset($_SESSION["TEMP_ERROR_TIPO"]) and $_SESSION["TEMP_ERROR_TIPO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","ERROR DE TIPO (PDF, DOC, DOCX, XLS, XLSX, JPG, BMP, PNG)");		
			unset($_SESSION["TEMP_ERROR_TIPO"]);			
		}
		else if(isset($_SESSION["TEMP_OK_FILE"]) and $_SESSION["TEMP_OK_FILE"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","EL DOCUMENTO SE ACTUALIZ&Oacute; CORRECTAMENTE");					
			unset($_SESSION["TEMP_OK_FILE"]);
		}
		else if(isset($_SESSION["TEMP_ERROR_NOMBRE"]) and $_SESSION["TEMP_ERROR_NOMBRE"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","ERROR: EL DOCUMENTO YA EXISTE");					
			unset($_SESSION["TEMP_ERROR_NOMBRE"]);
		}
		else if(isset($_SESSION["TEMP_ELIMINADO"]) and $_SESSION["TEMP_ELIMINADO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");		
			$tpl->assign("MENSAJE","EL DOCUMENTO SE ELIMINO CORRECTAMENTE");					
			unset($_SESSION["TEMP_ELIMINADO"]);
		}
		if(isset($_SESSION["TEMP_ENVIO_CORRECTO"]) and $_SESSION["TEMP_ENVIO_CORRECTO"] == "SI"){
			$tpl->assign("DISPLAY_MENSAJE","inline");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","CORREO ENVIADO CORRECTAMENTE");
			unset($_SESSION["TEMP_ENVIO_CORRECTO"]);
		}
		if(isset($_SESSION["TEMP_ENVIO_INCORRECTO"])){
			$tpl->assign("DISPLAY_MENSAJE","inline");
			$tpl->assign("SALTO_LINEA","<br/><br/><br/>");
			$tpl->assign("MENSAJE","ERROR: ".$_SESSION["TEMP_ENVIO_INCORRECTO"]);
			unset($_SESSION["TEMP_ENVIO_INCORRECTO"]);
		}	
		/*Redireccionamiento*/		
		if(isset($_GET["id"]) and isset($_GET["campo"])){
			//Contrato
			if($_GET["campo"] == 1){				
				//Contrato asociado al usuario
				$_SESSION["IDENTIFICADOR_CONTRATO"] = $_GET["id"];								
				$tpl->assign("VALOR_CONTRATO_TERMINO","");
				$tpl->assign("CONTRATO_TERMINO","--- SELECCIONAR CONTRATO ---");				
				$consulta = "select contrato.nombreCorto_contrato, contrato.id_contrato from contrato inner join usuarioContrato on contrato.id_contrato = ".
				"usuarioContrato.contrato_usuarioContrato and usuarioContrato.usuario_usuarioContrato= ".$_SESSION["IDENTIFICADOR_USUARIO"].
				" and contrato.estado_contrato = 1 and contrato.habilitado_contrato = 1";
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();							
				$resultado->bind_result($nombreContrato, $idContrato);
				while($resultado->fetch()){					
					if($idContrato == $_GET["id"]){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_CONTRATO_INICIO",$idContrato);
						$tpl->assign("CONTRATO_INICIO",$nombreContrato);
					}
					else{
						$tpl->newBlock("DESCRIPCION_CONTRATO"); 
						$tpl->assign("ID_CONTRATO",$idContrato);
						$tpl->assign("NOMBRE_CONTRATO",$nombreContrato);						
					}
				}
				//Informes que estan asociados al contrato
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("VALOR_INFORME_INICIO","");
				$tpl->assign("INFORME_INICIO","--- SELECCIONAR DOCUMENTO ---");							
				$i=0;
				$consulta2 = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_GET["id"]." and informe.estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$x = $resultado2->num_rows;				
				$resultado2->bind_result($nombre_informe,$codigo_informe);
				while($resultado2->fetch()){	
					if($i == $x-1){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_TERMINO",$codigo_informe);
						$tpl->assign("INFORME_TERMINO",$nombre_informe);									
					}
					else{				
						$tpl->newBlock("DESCRIPCION_INFORME"); 
						$tpl->assign("ID_INFORME",$codigo_informe);
						$tpl->assign("NOMBRE_INFORME",$nombre_informe);					
						$i++;
					}
				}				
				//SubInforme asociado al contrato
				$tpl->assign("VALOR_SUBINFORME_INICIO","");
				$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");							
				$tpl->assign("VALOR_SUBINFORME_TERMINO",-1);
				$tpl->assign("SUBINFORME_TERMINO","NO APLICA");							
				//Numero
				for($i=1;$i<=200;$i++){
					$tpl->newBlock("DESCRIPCION_NUMERO");
					$tpl->assign("ID_NUMERO",$i);
					$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
				}	
				//Hojas				
				for($i=1;$i<=60;$i++){
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",$i);
					$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
					if($i==60){
						$tpl->newBlock("DESCRIPCION_HOJA");
						$tpl->assign("ID_HOJA",0);
						$tpl->assign("NOMBRE_HOJA","LIBRO COMPLETO");
						$tpl->newBlock("DESCRIPCION_HOJA");
						$tpl->assign("ID_HOJA",-1);
						$tpl->assign("NOMBRE_HOJA","NO APLICA");
					}
				}
				//Destinatario
				$consulta3 = "select id_destinatario, nombre_destinatario, apellido_destinatario from destinatario";
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$resultado3->bind_result($id_destinatario, $nombre_destinatario, $apellido_destinatario);
				while($resultado3->fetch()){
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
	                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $nombre_destinatario." ".$apellido_destinatario);				
				}
				$resultado->close();
				$resultado2->close();
				$resultado3->close();
			}			
			//Informe
			else if($_GET["campo"] == 2){
				//Variable con la id del informe							
				$_SESSION["IDENTIFICADOR_INFORME"] = $_GET["id"];
				//Contrato asociado al usuario
				$tpl->assign("VALOR_CONTRATO_TERMINO","");
				$tpl->assign("CONTRATO_TERMINO","--- SELECCIONAR CONTRATO");						
				$consulta = "select contrato.nombreCorto_contrato, contrato.id_contrato from contrato inner join usuarioContrato on contrato.id_contrato = ".
				"usuarioContrato.contrato_usuarioContrato and usuarioContrato.usuario_usuarioContrato= ".$_SESSION["IDENTIFICADOR_USUARIO"].
				" and contrato.estado_contrato = 1 and contrato.habilitado_contrato = 1";
				$resultado = $conexion_db->prepare($consulta);
				$resultado->execute();
				$resultado->store_result();							
				$resultado->bind_result($nombreContrato, $idContrato);
				while($resultado->fetch()){					
					if($idContrato == $_SESSION["IDENTIFICADOR_CONTRATO"]){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_CONTRATO_INICIO",$idContrato);
						$tpl->assign("CONTRATO_INICIO",$nombreContrato);
					}
					else{
						$tpl->newBlock("DESCRIPCION_CONTRATO"); 
						$tpl->assign("ID_CONTRATO",$idContrato);
						$tpl->assign("NOMBRE_CONTRATO",$nombreContrato);
					}
				}
				//Informes que estan asociados al contrato
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("VALOR_INFORME_TERMINO","");
				$tpl->assign("INFORME_TERMINO","--- SELECCIONAR INFORME ---");							
				$consulta2 = "select informe.nombre_informe, informe.codigo_informe from informe inner join informeContrato on informe.codigo_informe = ".
				"informeContrato.informe_informeContrato and informeContrato.contrato_informeContrato = ".$_SESSION["IDENTIFICADOR_CONTRATO"].
				" and informe.estado_informe = 1";
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombre_informe,$codigo_informe);
				while($resultado2->fetch()){					
					if($codigo_informe == $_SESSION["IDENTIFICADOR_INFORME"]){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("VALOR_INFORME_INICIO",$codigo_informe);
						$tpl->assign("INFORME_INICIO",$nombre_informe);						
					}
					else{
						$tpl->newBlock("DESCRIPCION_INFORME"); 
						$tpl->assign("ID_INFORME",$codigo_informe);
						$tpl->assign("NOMBRE_INFORME",$nombre_informe);
					}
				}				
				//SubInforme asociado al informe		
				$j=0;	
				$tpl->gotoBlock("_ROOT");	
				$tpl->assign("VALOR_SUBINFORME_INICIO","");
				$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-INFORME ---");					
				$consulta3 = "select subInforme.nombre_subInforme, subInforme.codigo_subInforme from subInforme inner join subInformeInforme on ".
				"subInforme.codigo_subInforme = subInformeInforme.subInforme_subInformeInforme and subInformeInforme.informe_subInformeInforme = ".
				$_SESSION["IDENTIFICADOR_INFORME"]." and subInforme.estado_subInforme = 1 and subInformeInforme.contrato_subInformeInforme = ".
				$_SESSION["IDENTIFICADOR_CONTRATO"];
				$resultado3 = $conexion_db->prepare($consulta3);
				$resultado3->execute();
				$resultado3->store_result();
				$k = $resultado3->num_rows;
				if($k == 0){
					$tpl->assign("VALOR_SUBINFORME_INICIO","");
					$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-INFORME ---");							
					$tpl->assign("VALOR_SUBINFORME_TERMINO",-1);
					$tpl->assign("SUBINFORME_TERMINO","NO APLICA");						
				}
				else{
					$resultado3->bind_result($nombre_subInforme, $codigo_subInforme);
					while($resultado3->fetch()){
						if($j == $k-1){
							$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
							$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
							$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);							
							$tpl->gotoBlock("_ROOT");
							$tpl->assign("VALOR_SUBINFORME_TERMINO",-1);
							$tpl->assign("SUBINFORME_TERMINO","NO APLICA");						
						}
						else{					
							$tpl->newBlock("DESCRIPCION_SUBINFORME"); 
							$tpl->assign("ID_SUBINFORME",$codigo_subInforme);
							$tpl->assign("NOMBRE_SUBINFORME",$nombre_subInforme);
							$j++;
						}
					}
				}
														
				//Numero
				for($i=1;$i<=200;$i++){
					$tpl->newBlock("DESCRIPCION_NUMERO");
					$tpl->assign("ID_NUMERO",$i);
					$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
				}	
				//Hojas				
				for($i=1;$i<=60;$i++){
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",$i);
					$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
					if($i==60){
						$tpl->newBlock("DESCRIPCION_HOJA");
						$tpl->assign("ID_HOJA",0);
						$tpl->assign("NOMBRE_HOJA","LIBRO COMPLETO");
						$tpl->newBlock("DESCRIPCION_HOJA");
						$tpl->assign("ID_HOJA",-1);
						$tpl->assign("NOMBRE_HOJA","NO APLICA");
					}
				}
				//Destinatario
				$consulta4 = "select id_destinatario, nombre_destinatario, apellido_destinatario from destinatario";
				$resultado4 = $conexion_db->prepare($consulta4);
				$resultado4->execute();
				$resultado4->store_result();
				$resultado4->bind_result($id_destinatario, $nombre_destinatario, $apellido_destinatario);
				while($resultado4->fetch()){
					$tpl->newBlock("DESCRIPCION_DESTINATARIO");
	                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
					$tpl->assign("NOMBRE_DESTINATARIO", $nombre_destinatario." ".$apellido_destinatario);				
				}
				$resultado->close();
				$resultado2->close();
				$resultado3->close();				
				$resultado4->close();
			}
		}
		//Carga inicial
		else{
			//Contrato
			$i=1;
			$tpl->assign("VALOR_CONTRATO_INICIO","");
			$tpl->assign("CONTRATO_INICIO","--- SELECCIONAR CONTRATO ---");						
			$consulta = "select contrato.nombreCorto_contrato, contrato.id_contrato from contrato inner join usuarioContrato on contrato.id_contrato = ".
			"usuarioContrato.contrato_usuarioContrato and usuarioContrato.usuario_usuarioContrato= ".$_SESSION["IDENTIFICADOR_USUARIO"].
			" and contrato.estado_contrato = 1 and contrato.habilitado_contrato = 1";
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();			
			$x = $resultado->num_rows;
			$resultado->bind_result($nombreContrato, $idContrato);
			while($resultado->fetch()){
				if($i == $x){
					$tpl->gotoBlock("_ROOT");
					$tpl->assign("VALOR_CONTRATO_TERMINO",$idContrato);
					$tpl->assign("CONTRATO_TERMINO",$nombreContrato);
				}
				else{
					$tpl->newBlock("DESCRIPCION_CONTRATO"); 
					$tpl->assign("ID_CONTRATO",$idContrato);
					$tpl->assign("NOMBRE_CONTRATO",$nombreContrato);
					$i++;
				}
			}
			//Informe
			$tpl->assign("VALOR_INFORME_INICIO","");
			$tpl->assign("INFORME_INICIO","--- SELECCIONAR DOCUMENTO ---");							
			//SubInforme
			$tpl->assign("VALOR_SUBINFORME_INICIO","");
			$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");	
			$tpl->assign("VALOR_SUBINFORME_TERMINO",-1);
			$tpl->assign("SUBINFORME_TERMINO","NO APLICA");	
			//Numero
			for($i=1;$i<=200;$i++){
				$tpl->newBlock("DESCRIPCION_NUMERO");
				$tpl->assign("ID_NUMERO",$i);
				$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
			}	
			//Hojas				
			for($i=1;$i<=60;$i++){
				$tpl->newBlock("DESCRIPCION_HOJA");
				$tpl->assign("ID_HOJA",$i);
				$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
				if($i==60){
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",0);
					$tpl->assign("NOMBRE_HOJA","LIBRO COMPLETO");
					$tpl->newBlock("DESCRIPCION_HOJA");
					$tpl->assign("ID_HOJA",-1);
					$tpl->assign("NOMBRE_HOJA","NO APLICA");
				}
			}
			//Destinatario
			$consulta2 = "select id_destinatario, nombre_destinatario, apellido_destinatario from destinatario";
			$resultado2 = $conexion_db->prepare($consulta2);
			$resultado2->execute();
			$resultado2->store_result();
			$resultado2->bind_result($id_destinatario, $nombre_destinatario, $apellido_destinatario);
			while($resultado2->fetch()){
				$tpl->newBlock("DESCRIPCION_DESTINATARIO");
                $tpl->assign("ID_DESTINATARIO", $id_destinatario);
				$tpl->assign("NOMBRE_DESTINATARIO", $nombre_destinatario." ".$apellido_destinatario);				
			}
			
			$resultado->close();
			$resultado2->close();
		}
		
		if(isset($_SESSION["TEMP_ERROR_ARCHIVO"]) and $_SESSION["TEMP_ERROR_ARCHIVO"] == "SI"){
			$tpl->assign("MENSAJE","ERROR AL TRATAR DE SUBIR EL ARCHIVO");
			unset($_SESSION["TEMP_ERROR_ARCHIVO"]);
		}		
		if(isset($_SESSION["TEMP_ERROR_TIPO"]) and $_SESSION["TEMP_ERROR_TIPO"] == "SI"){
			$tpl->assign("MENSAJE","FORMATO NO PERMITIDO, SUBIR ARCHIVOS PDF, WORD, EXCEL, JPG, BMP");
			unset($_SESSION["TEMP_ERROR_TIPO"]);
		}
		if(isset($_SESSION["TEMP_OK_FILE"]) and $_SESSION["TEMP_OK_FILE"] == "SI"){
			$tpl->assign("MENSAJE","ARCHIVO SUBIDO CORRECTAMENTE");
			unset($_SESSION["TEMP_OK_FILE"]);
		}		
		$conexion_db->close();
		$tpl->printToScreen();
	}
	//Comienza la búsqueda
	else{
	   	//Generamos la página
		$tpl = new TemplatePower("../../interfaz/HTML/trabajarDocumento.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",$_SESSION["NOMBRE"]);
		$tpl->assign("APELLIDO",$_SESSION["APELLIDO"]);
		$tpl->assign("CARGO",$_SESSION["CARGO"]);
		$tpl->assign("TIPOCUENTA",$_SESSION["TIPOCUENTA"]);
		$tpl->assign("CORREO",$_SESSION["CORREO"]);
		$tpl->assign("DISPLAY_MENSAJE","none");
		$tpl->assign("SALTO_LINEA","");
		/*if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="administrador.php">Administrar</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
		}
		else{
			$tpl->assign("MENU_CABEZA",'&nbsp;&nbsp;&nbsp;&nbsp;<a href="salir.php">Cerrar Sesión</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="sistema.php">Regresar</a>');
		}*/
        if(strcmp($_SESSION["TIPOCUENTA"], "Administrador") == 0){
			$tpl->assign("DISPLAY_MENU",'');
		}
		else{
            $tpl->assign("DISPLAY_MENU",'pointer-events: none;cursor: default;');			
		}		
		//Contrato
		$i=1;
		$tpl->assign("VALOR_CONTRATO_INICIO","");
		$tpl->assign("CONTRATO_INICIO","--- SELECCIONAR CONTRATO ---");						
		$consulta = "select contrato.nombreCorto_contrato, contrato.id_contrato from contrato inner join usuarioContrato on contrato.id_contrato = ".
		"usuarioContrato.contrato_usuarioContrato and usuarioContrato.usuario_usuarioContrato= ".$_SESSION["IDENTIFICADOR_USUARIO"].
		" and contrato.estado_contrato = 1 and contrato.habilitado_contrato = 1";
		$resultado = $conexion_db->prepare($consulta);
		$resultado->execute();
		$resultado->store_result();			
		$x = $resultado->num_rows;
		$resultado->bind_result($nombreContrato, $idContrato);
		while($resultado->fetch()){
			if($i == $x){
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("VALOR_CONTRATO_TERMINO",$idContrato);
				$tpl->assign("CONTRATO_TERMINO",$nombreContrato);
			}
			else{
				$tpl->newBlock("DESCRIPCION_CONTRATO"); 
				$tpl->assign("ID_CONTRATO",$idContrato);
				$tpl->assign("NOMBRE_CONTRATO",$nombreContrato);
				$i++;
			}
		}
		//Informe
		$tpl->assign("VALOR_INFORME_INICIO","");
		$tpl->assign("INFORME_INICIO","--- SELECCIONAR DOCUMENTO ---");							
		//SubInforme
		$tpl->assign("VALOR_SUBINFORME_INICIO","");
		$tpl->assign("SUBINFORME_INICIO","--- SELECCIONAR SUB-DOCUMENTO ---");	
		//Numero
		for($i=1;$i<=100;$i++){
			$tpl->newBlock("DESCRIPCION_NUMERO");
			$tpl->assign("ID_NUMERO",$i);
			$tpl->assign("NOMBRE_NUMERO","DOCUMENTO N°".$i);
		}	
		//Hojas				
		for($i=1;$i<=60;$i++){
			$tpl->newBlock("DESCRIPCION_HOJA");
			$tpl->assign("ID_HOJA",$i);
			$tpl->assign("NOMBRE_HOJA","HOJA N°".$i);
			if($i==60){
				$tpl->newBlock("DESCRIPCION_HOJA");
				$tpl->assign("ID_HOJA",0);
				$tpl->assign("NOMBRE_HOJA","LIBRO COMPLETO");
				$tpl->newBlock("DESCRIPCION_HOJA");
				$tpl->assign("ID_HOJA",-1);
				$tpl->assign("NOMBRE_HOJA","NO APLICA");
			}
		}
		//Destinatario
		$consulta2 = "select id_destinatario, nombre_destinatario, apellido_destinatario from destinatario";
		$resultado2 = $conexion_db->prepare($consulta2);
		$resultado2->execute();
		$resultado2->store_result();
		$resultado2->bind_result($id_destinatario, $nombre_destinatario, $apellido_destinatario);
		while($resultado2->fetch()){
			$tpl->newBlock("DESCRIPCION_DESTINATARIO");
            $tpl->assign("ID_DESTINATARIO", $id_destinatario);
			$tpl->assign("NOMBRE_DESTINATARIO", $nombre_destinatario." ".$apellido_destinatario);				
		}

		//IF BOTONES
		if(strcmp($_POST["cambiar"],"BUSCAR") == 0){
			//Informacion del formulario		
			$identificador_contrato = $_POST["contrato"];	//int
			$identificador_informe = $_POST["informe"];		//int
			$identificador_subInforme = $_POST["subInforme"];	//int
			$identificador_numero = $_POST["numeroDocumento"];	//int
			$fecha_documento = $_POST["fechaDocumento"];	//date
			$numero_hoja = $_POST["hoja"];	//int
			$identificador_destinatario = $_POST["destinatario"];	//int	
			//Creamos el array asociativo	
			if(!empty($identificador_contrato)){ $filtro['contrato_documento'] = $identificador_contrato; }
			if(!empty($identificador_informe)){ $filtro['informe_documento'] = $identificador_informe; }
			if(!empty($identificador_subInforme)){ $filtro['subInforme_documento'] = $identificador_subInforme; }
			if(!empty($identificador_numero)){ $filtro['numero_documento'] = $identificador_numero; }
			if(!empty($fecha_documento)){ $filtro['fecha_documento'] = $fecha_documento; }
			if(!empty($numero_hoja)){ $filtro['hoja_documento'] = $numero_hoja; }
			if(!empty($identificador_destinatario)){ $filtro['destinatario_documento'] = $identificador_destinatario; }
			//Primera parte de la consulta
			$consulta_aux = "select codigo_documento,contrato_documento,informe_documento,subInforme_documento,numero_documento,fecha_documento,".
			"hoja_documento,destinatario_documento,descripcion_documento,nombre_documento,ruta_documento,idDueno_documento from documento where ";		
			//Segunda parte de la consulta
			$consulta_aux2 = "";		
			$cantidad = count($filtro);	
			$i=0;	
			foreach($filtro as $item => $value){		
				if($i == ($cantidad-1)){
					if(strcmp($item,"fecha_documento") == 0){ $consulta_aux2 = strval($consulta_aux2.($item." = '".$value."'")); }
					else{ $consulta_aux2 = strval($consulta_aux2.($item." = ".$value)); }				
				}
				else{
					if(strcmp($item,"fecha_documento") == 0){ $consulta_aux2 = strval($consulta_aux2.($item." = '".$value."' and ")); }
					else{ $consulta_aux2 = strval($consulta_aux2.($item." = ".$value." and ")); }				
				}
				$i++;						
			}
			//Se realiza la consulta		
			$consulta3 = strval($consulta_aux.$consulta_aux2);	
			$resultado3 = $conexion_db->prepare($consulta3);
			$resultado3->execute();
			$resultado3->store_result();
			if($resultado3->num_rows == 0){
				header("Location: trabajarDocumento.php");
				$_SESSION["TEMP_NO_INFORMACION"] = "SI";
			}
			$resultado3->bind_result($codigo_documento,$contrato_documento,$informe_documento,$subInforme_documento,$numero_documento,
			$fecha_documento,$hoja_documento,$destinatario_documento,$descripcion_documento,$nombre_documento,$ruta_documento,$idDueno_documento);
			while($resultado3->fetch()){
				//Obtenemos el nombre del sub-informe
				$consulta5 = "select nombre_subInforme from subInforme where codigo_subInforme = ".$subInforme_documento;
				$resultado5 = $conexion_db->prepare($consulta5);
				$resultado5->execute();
				$resultado5->store_result();
				$resultado5->bind_result($nombre_subInforme);
				$resultado5->fetch();
							
				$tpl->newBlock("RESULTADO_BUSQUEDA");
				$tpl->assign("IDENTIFICADOR",$codigo_documento);
				$tpl->assign("SUB-DOCUMENTO",$nombre_subInforme);
				$tpl->assign("DOCUMENTO_N",$numero_documento);
				$tpl->assign("NOMBRE",$nombre_documento);
				$tpl->assign("COMENTARIO",$descripcion_documento);			
				$tpl->assign("FECHA",$fecha_documento);	
				$tpl->assign("RUTA_DOCUMENTO",$ruta_documento);
				if($_SESSION["IDENTIFICADOR_USUARIO"] == $idDueno_documento || strcmp($_SESSION["TIPOCUENTA"],"Administrador") == 0 ||
				strcmp($_SESSION["TIPOCUENTA"],"Usuario Avanzado") == 0){ $tpl->assign("OPT_DISPLAY","inline"); }
				else{ $tpl->assign("OPT_DISPLAY","none"); $tpl->assign("OPT_DISPLAY2","inline"); }
			}			
			$resultado3->close();
			$resultado5->close();
			$conexion_db->close();
		}
        if(strcmp($_POST["cambiar"],"REVISAR") == 0){
            //header("Location: revisarDocumento.php?id=".$_POST["identificador"]);
            header("Location: revisarDocumento.php?id=".$_POST["variable_identificador"]);
		}
		if(strcmp($_POST["cambiar"],"MODIFICAR") == 0){
		  	//header("Location: modificarDocumento.php?iddoc=".$_POST["identificador"]);
            header("Location: modificarDocumento.php?iddoc=".$_POST["variable_identificador"]);
		}
		if(strcmp($_POST["cambiar"],"ELIMINAR") == 0){	
			//Obtenemos la ruta del documento
            $consulta6 = "select ruta_documento from documento where codigo_documento = ".$_POST["variable_identificador"];
			$resultado6 = $conexion_db->prepare($consulta6);
			$resultado6->execute();
			$resultado6->store_result();
			$resultado6->bind_result($ruta_documento);
			$resultado6->fetch();
			if(file_exists($ruta_documento)){
				//Eliminamos el documento
				unlink($ruta_documento);
				//Eliminamos el registro de la tabla documento
				$consulta7 = "delete from documento where codigo_documento = ".$_POST["variable_identificador"];
				$resultado7 = $conexion_db->prepare($consulta7);
				$resultado7->execute();
				//Eliminamos los registros de la tabla historial
				$consulta8 = "delete from historial where documento_historial = ".$_POST["variable_identificador"];
				$resultado8 = $conexion_db->prepare($consulta8);
				$resultado8->execute();
				//Cerramos conexion y redireccionamos
				$resultado6->close();
				$conexion_db->close();
				$_SESSION["TEMP_ELIMINADO"] = "SI";
				header("Location: trabajarDocumento.php");
			}
			else{
				//Cerramos conexion y redireccionamos
				$resultado6->close();
				$conexion_db->close();
				$_SESSION["TEMP_NO_ELIMINADO"] = "SI";
				header("Location: trabajarDocumento.php");	
			}
		}
		$resultado->close();
		$resultado2->close();	
		$tpl->printToScreen();
	}
	//Sanear un string
	function sanear_string($string){
		
		$string = trim($string);	//Elimina espacios en blanco al principio y al final
		
		$string = str_replace(array('á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);		
    	$string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
		$string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
	    $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
	    $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
    	$string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);				
		$string = str_replace(array("\\", "¨", "º", "°", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", 
									"¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", "."), '', $string);
		$string = str_replace(" ", "_", $string);
	   	return $string;
	}	
?>
