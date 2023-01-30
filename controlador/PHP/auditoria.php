<?PHP
	/*LIBRERIAS*/
	require_once("../TemplatePower/class.TemplatePower.inc.php");	
	require_once("../fpdf17/fpdf.php");
	require_once("conexion.php");
	require_once("sesiones.php");
	
	//Funciones validación session
	validarAdministrador();
	validaTiempo();
	
	//Funciones para PDF
	class PDF extends FPDF{
		var $widths;
		var $aligns;
		public $direccion_oficina;
		public $fono_oficina;
		public $web_oficina;
		public $mail_oficina;
		public $ciudad_oficina;
			
		function Header(){
			//Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])			
			$this->Image('../../interfaz/Imagenes/logo.jpg',1,1,5);			
			//Salto de linea
			$this->Ln(2);
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);			
			$this->Line(1,31.9,20.5,31.9);			
			$this->Cell(0,10,strtoupper(utf8_decode(html_entity_decode($this->direccion_oficina).', '.html_entity_decode($this->ciudad_oficina).
			' , Chile - FONO: '.html_entity_decode($this->fono_oficina).' - EMAIL: '.html_entity_decode($this->mail_oficina).
			' / '.html_entity_decode($this->web_oficina))),0,0,'C',false);		
		}				
		function SetWidths($w){
	    	//Set the array of column widths
    		$this->widths=$w;
		}
		function SetAligns($a){
    		//Set the array of column alignments
		    $this->aligns=$a;
		}
		function Row($data){
    		//Calculate the height of the row
    		$nb=0;
    		for($i=0;$i<count($data);$i++)
        	$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    		$h=0.5*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];
        		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        		//Save the current position
		        $x=$this->GetX();
		        $y=$this->GetY();
		        //Draw the border
		        $this->Rect($x,$y,$w,$h);
		        //Print the text
		        $this->MultiCell($w,0.5,$data[$i],0,$a);
		        //Put the position to the right of the cell
		        $this->SetXY($x+$w,$y);
		    }
		    //Go to the next line
		    $this->Ln($h);
		}
		function Row2($data){
    		//Calculate the height of the row
    		$nb=0;
    		for($i=0;$i<count($data);$i++)
        	$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    		$h=0.5*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];
        		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
        		//Save the current position
		        $x=$this->GetX();
		        $y=$this->GetY();
		        //Draw the border
		        $this->Rect($x,$y,$w,$h);
		        //Print the text
		        $this->MultiCell($w,0.5,$data[$i],0,$a);
		        //Put the position to the right of the cell
		        $this->SetXY($x+$w,$y);
		    }
		    //Go to the next line
		    $this->Ln($h);
		}
		function CheckPageBreak($h){		
			//If the height h would cause an overflow, add a new page immediately
    		if($this->GetY()+$h>$this->PageBreakTrigger)
        		$this->AddPage($this->CurOrientation);	
		}
		function NbLines($w,$txt){
    		//Computes the number of lines a MultiCell of width w will take
    		$cw=&$this->CurrentFont['cw'];
		    if($w==0)
       			$w=$this->w-$this->rMargin-$this->x;
    		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    		$s=str_replace("\r",'',$txt);
    		$nb=strlen($s);
    		if($nb>0 and $s[$nb-1]=="\n")
       			$nb--;
    		$sep=-1;
    		$i=0;
   	 		$j=0;
    		$l=0;
    		$nl=1;
    		while($i<$nb){
        		$c=$s[$i];
        		if($c=="\n"){
					$i++;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
					continue;
        		}
        		if($c==' ')
            		$sep=$i;
        		$l+=$cw[$c];
        		if($l>$wmax){
            		if($sep==-1){
                		if($i==$j)
                    		$i++;
            		}
            		else
                		$i=$sep+1;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
        		}
        		else
            		$i++;
    		}
    		return $nl;
		}
	}
	//Fin funciones para PDF
	
	//Comienza la carga	
	if(!isset($_GET["inf"])){	
		header("Location: administrador.php");
	}
	else{
		$id = $_GET["inf"];
		//Estructura del contrato
		if($id == 1){		
			//Objeto de la clase heredada
			$pdf = new PDF('P','cm',array(21.6,33));	
			
			//Datos cabecera
			$pdf->direccion_oficina = "Avenida Providencia 199, Piso 3, Providencia";
			$pdf->fono_oficina = "22233396";
			$pdf->web_oficina = "www.bagado.cl";
			$pdf->mail_oficina = "info@bogado.cl";
			$pdf->ciudad_oficina = "Santiago";
			
			//Agrega una pagina y su fuente
			$pdf->AddPage('P',array(21.6,33));
			
			//Titulo
			$i=3;			
			$pdf->SetFont('Arial','B',10);
			$pdf->SetXY(6.15,$i);
			$pdf->MultiCell(9.4,.5,utf8_decode('ESTRUCTURA DEL SISTEMA GESTIÓN DOCUMENTAL'),'B','C',false);			
				
			//Obtenemos los contratos
			$i = $i + 1;	
			$m=0;		
			$consulta = "select * from contrato";
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($id_contrato, $nombreCompleto_contrato, $nombreCorto_contrato, $fechaInicio_contrato, $fechaTermino_contrato, $resolucion_contrato, 
			$estado_contrato, $habilitado_contrato, $directorio_contrato);
			while($resultado->fetch()){
				if($m != 0){
					//Agrega una pagina y su fuente
					$pdf->AddPage('P',array(21.6,33));
					$i=3;
				}
				//Mostramos el nombre
				$pdf->SetFont('Arial','B',10);
				$pdf->SetXY(1,$i);
				$pdf->MultiCell(2,.5,strtoupper(utf8_decode('NOMBRE: ')),0,'J',false);
				$pdf->SetXY(3,$i);
				$pdf->MultiCell(0,.5,strtoupper(utf8_decode('"'.html_entity_decode($nombreCompleto_contrato).'"')),0,'J',false);
				$i = $i+2;
				$pdf->SetXY(1,$i);
				$pdf->MultiCell(3,.5,strtoupper(utf8_decode('RESOLUCIÓN: ')),0,'J',false);
				$pdf->SetXY(3.7,$i);
				$pdf->MultiCell(0,.5,strtoupper(utf8_decode('"'.html_entity_decode($resolucion_contrato).'"')),0,'J',false);
				$i = $i+2;	
				//Obtenemos los informes asociados al contrato
				$consulta2 = "SELECT informe.codigo_informe, informe.nombre_informe from informe INNER JOIN informecontrato ON ".
				"informe.codigo_informe = informecontrato.informe_informeContrato and informecontrato.contrato_informeContrato = ".$id_contrato;
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();			
				//Recorremos e imprimos los resultados
				$resultado2->bind_result($codigo_informe, $nombre_informe);				
				while($resultado2->fetch()){					
					$j=0;
					//Obtenemos los sub-informes asociados al contrato y al informe
					$consulta3 = "select subInforme.codigo_subInforme, subInforme.nombre_subInforme from subInforme inner join subInformeInforme on subInforme.codigo_subInforme = ".
					"subInformeInforme.subInforme_subInformeInforme and subInformeInforme.informe_subInformeInforme = ".$codigo_informe.
					" and subInformeInforme.contrato_subInformeInforme = ".$id_contrato;
					$resultado3 = $conexion_db->prepare($consulta3);
					$resultado3->execute();	
					$resultado3->store_result();
					$cantidadElementos = $resultado3->num_rows;
					$resultado3->bind_result($codigo_subinforme, $nombre_subinforme);
					$k=0;
					if($j==0){
						//Validamos el tamaño de la hoja														
						$totalEspacioOcupado = $i + 0.5 + ($cantidadElementos*0.56);							;
						if($totalEspacioOcupado >= 30){
							$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
							$i=3;
							$j=0;
						}						
						$j++;
					}
					while($resultado3->fetch()){
						//Imprimos la información						
						if($k == 0){
							$pdf->SetFont('Arial','B',10);
							$pdf->SetXY(1,$i);
							$pdf->MultiCell(0,.5,strtoupper(utf8_decode('+ '.html_entity_decode($nombre_informe).'')),0,'J',false);
							$i = $i + 0.5;
							$k++;
						}
						$pdf->SetFont('Arial','',9);					
						$pdf->SetXY(2,$i);
						$pdf->MultiCell(0,.5,strtoupper(utf8_decode('|')),0,'J',false);
						$i = $i+0.31;
						$pdf->SetXY(2,$i);
						$pdf->MultiCell(0,.5,strtoupper(utf8_decode('|')),0,'J',false);						
						$pdf->SetXY(2.05,$i);
						$pdf->MultiCell(0,.5,strtoupper(utf8_decode('___')),0,'J',false);
						$i = $i+0.15;
						$pdf->SetXY(3,$i);
						$pdf->MultiCell(0,.5,strtoupper(utf8_decode(''.html_entity_decode($nombre_subinforme).'')),0,'J',false);
						$i = $i+0.1;
					}
					$i = $i+1;					
				}	
				$m++;			
			}			
			if(isset($resultado)){ $resultado->close(); }
			if(isset($resultado2)){ $resultado2->close(); }
			if(isset($resultado3)){ $resultado3->close(); }
			$pdf->Output('Informe_Estructura_Documentos.pdf','D');
			//$pdf->Output();		
		}
		else if($id == 2){
			//Objeto de la clase heredada
			$pdf = new PDF('L','cm',array(21.6,33));	
			
			//Datos cabecera
			$pdf->direccion_oficina = "Avenida Providencia 199, Piso 3, Providencia";
			$pdf->fono_oficina = "22233396";
			$pdf->web_oficina = "www.bagado.cl";
			$pdf->mail_oficina = "info@bogado.cl";
			$pdf->ciudad_oficina = "Santiago";
			
			//Agrega una pagina y su fuente
			$pdf->AddPage('L',array(21.6,33));
			
			//Titulo
			$i=3;			
			$pdf->SetFont('Arial','B',11);
			$pdf->SetXY(10.5,$i);
			$pdf->MultiCell(12.2,.5,utf8_decode('USUARIOS INGRESADOS AL SISTEMA GESTIÓN DOCUMENTAL'),'B','C',false);			
			
			//Descripción
			$i=$i+1.5;
			$pdf->SetFont('Arial','',10);
			$pdf->SetXY(1,$i);			
			$pdf->MultiCell(0,.5,utf8_decode('En este informe se presenta el listado y la descripción de los usuarios que están ingresado en el Sistema, con el fin de verificar su información y su correcta distribución dentro del Software.'),0,'J',false);
			
			//Cabecera tabla
			$i = $i + 1.5;
			$pdf->SetXY(1,$i);
			$pdf->SetFont('Arial','B',10);			
			$pdf->SetWidths(array(6, 6, 6, 6, 6));
				$pdf->Row2(array(
					utf8_decode(html_entity_decode('NOMBRE')), utf8_decode(html_entity_decode('USUARIO')), 
					utf8_decode(html_entity_decode('CARGO EMPRESA')), utf8_decode(html_entity_decode('CONTRATOS')), 
					utf8_decode(html_entity_decode('PRIVILEGIO'))
				));				
						
			//Sacamos la informacion de la BD (nombre y usuario)
			$pdf->SetFont('Arial','',9);
			$consulta = "select datosUsuario.nombre_datosUsuario, datosUsuario.apellidos_datosUsuario, datosUsuario.cargo_datosUsuario, ".
			"usuario.nombre_usuario, usuario.codigo_usuario, usuario.cuenta_usuario from usuario inner join datosUsuario on usuario.codigo_usuario = ".
			"datosUsuario.codigo_datosUsuario";
			$resultado = $conexion_db->prepare($consulta);
			$resultado->execute();
			$resultado->store_result();
			$resultado->bind_result($nombre_datosUsuario, $apellidos_datosUsuario, $cargo_datosUsuario, $nombre_usuario, $codigo_usuario, $cuenta_usuario);			
			while($resultado->fetch()){
				//Se genera el nombre
				$nombreCompleto = $nombre_datosUsuario." ".$apellidos_datosUsuario;				
				
				//Obtenemos el tipo de cuenta
				$consulta2 = "select nombre_tipoCuenta from tipoCuenta where codigo_tipoCuenta = ".$cuenta_usuario;
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombre_tipoCuenta);
				$resultado2->fetch();				
				
				//Obtenemos el contrato
				$consulta2 = "select contrato.nombreCorto_contrato from contrato inner join usuarioContrato on contrato.id_contrato = ".
				"usuarioContrato.contrato_usuarioContrato and usuarioContrato.usuario_usuarioContrato = ".$codigo_usuario;
				$resultado2 = $conexion_db->prepare($consulta2);
				$resultado2->execute();
				$resultado2->store_result();
				$resultado2->bind_result($nombreCorto_contrato);
				$contrato="";
				while($resultado2->fetch()){					
					$contrato = utf8_decode(html_entity_decode($nombreCorto_contrato))."\n".$contrato;											
				}
				
				$pdf->SetX(1);
				$pdf->SetWidths(array(6, 6, 6, 6, 6));
				$pdf->Row(array(
					utf8_decode(html_entity_decode($nombreCompleto)), utf8_decode(html_entity_decode($nombre_usuario)), utf8_decode(html_entity_decode($cargo_datosUsuario)),  
					$contrato, utf8_decode(html_entity_decode($nombre_tipoCuenta))
				));
			}
			$pdf->Output('Informe_Usuarios_Sistema.pdf','D');
			//$pdf->Output();					
		}
		else if($id == 3){
			echo $id;
			exit;
		}
		else if($id == 4){
			echo $id;
			exit;
		}
		else{
			header("Location: administrador.php");
		}		
	}
?>