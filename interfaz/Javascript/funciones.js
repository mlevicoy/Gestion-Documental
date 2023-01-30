// JavaScript Document
function redireccionar(id, nombre_formulario){     				
	if(id == 0){	
		limpiarFormulario(nombre_formulario);
		bloquearDesbloquear(nombre_formulario, 1, 1);		
		document.getElementsByName('usuarioBuscar').item(0).focus();
		return;
	}
	else{	
		location.href = '../../controlador/PHP/modificarUsuario.php?id='+id;
	}
}  

function redireccionar2(id, nombre_formulario, pagina){     				
	if(id == 0){	
		limpiarFormulario(nombre_formulario);
		bloquearDesbloquear(nombre_formulario, 1, 1);		
		document.getElementsByName('contratoBuscar').item(0).focus();
		return;
	}
	else{	
		location.href = '../../controlador/PHP/'+pagina+'?id='+id;
	}
} 

function redireccionar3(id,pagina){     
	if(id == ""){			
		return;
	}
	else{	
		location.href = '../../controlador/PHP/'+pagina+'&id='+id;
	}
} 

function redireccionar4(id,pagina){     						
	if(id == 0){
		location.href = '../../controlador/PHP/'+pagina;	
	}	
} 

function bloquearDesbloquear(id, valor, indice){
	var formulario = document.getElementById(id);
	var cantidad_elemento = document.getElementById(id).elements.length;
	
	for(var i=indice;i<cantidad_elemento;i++){		
		if(formulario.elements[i].type == "text" || formulario.elements[i].type == "email" || formulario.elements[i].type == "password" || 
		formulario.elements[i].type == "select-one" || formulario.elements[i].type == "select-multiple" || formulario.elements[i].type == "submit" || 
		formulario.elements[i].type == "textarea" || formulario.elements[i].type == "checkbox" || formulario.elements[i].type == "date"){
			formulario.elements.item(i).disabled = valor;		
		}		
	}	
	return;	
}
function limpiarFormulario(nombre_formulario){
	var formulario = document.getElementById(nombre_formulario);
	var cantidad_elemento = document.getElementById(nombre_formulario).elements.length;
	//"select-one", "text", "email", "password", "submit", "hidden"
	
	for(var i=1;i<cantidad_elemento;i++){
		if(formulario.elements[i].type == "text" || formulario.elements[i].type == "email" || formulario.elements[i].type == "password" || 
		formulario.elements[i].type == "textarea" || formulario.elements[i].type == "date"){
			formulario.elements.item(i).value = "";
		}		
		if(formulario.elements[i].type == "select-one"){
			formulario.elements.item(i).selectedIndex = 3;
		}
		if(formulario.elements[i].type == "checkbox"){
			formulario.elements.item(i).checked = false;
		}
	}	
	return;		
}