<?php
require("class.phpmailer.php");
require("class.smtp.php");

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Host = "smtp.gmail.com";
$mail->Port = 465;
$mail->Username = "tucorreo@gmail.com";
$mail->Password = "tupassword";

$mail->From = "tucorreo@gmail.com";
	$mail->FromName = "Tu Nombre";
	$mail->Subject = "Enviar Mail con PHPMailer";
	$mail->AltBody = "";
	$mail->MsgHTML("<h1>Hola Mundo!</h1>");
$mail->AddAttachment("adjunto.txt");

$mail->AddAddress("destinatario@hotmail.com", "Nombre Destinatario");
$mail->IsHTML(true);
$mail->Send();


?>