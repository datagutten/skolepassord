<?php
//Configfil med innstillinger for PHPmailer

//Server settings
//$mail->SMTPDebug = 2;                                 // Enable verbose debug output
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = '10.0.0.0';  // Specify main and backup SMTP servers
/*$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'user@example.com';                 // SMTP username
$mail->Password = 'secret';                           // SMTP password*/
//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 25; // TCP port to connect to
$mail->setFrom('skolepassord@as.kommune.no', 'Skolepassord');
$mail->addReplyTo('helpdesk@as.kommune.no', 'Helpdesk');
$mail->SMTPAutoTLS = false;
