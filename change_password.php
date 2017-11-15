<?php
session_start();
if(empty($_SESSION['mail']))
	die('Du er ikke pålogget, oppdater siden');
require 'adtools/adtools.class.php';
$adtools=new adtools('reset');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);                         // Passing `true` enables exceptions
require 'config_mail.php';
$mail->addAddress($_SESSION['mail']); //Add the logged in user as recipient

$passwords=array('Skorpion','Flaggermus','Edderkopp','Grevling','Moskus','Leopard','Tiger'); //Ordliste for pasord (minst 5 tegn)
$adtools->error='Demo';
if($_SERVER['REQUEST_METHOD']=='POST')
{
	require 'DOMDocument_createElement_simple.php';
	$dom=new DOMDocumentCustom;
	$mail->isHTML(true);
	$users=json_decode(file_get_contents('php://input'),true);
	$table=$dom->createElement_simple('table',false,array('border'=>1));
	$tr=$dom->createElement_simple('tr',$table);
	$th=$dom->createElement_simple('th',$table,array('colspan'=>3),sprintf('Passord for %s er satt som følger:',$_GET['group']));
	$tr=$dom->createElement_simple('tr',$table);
	$th=$dom->createElement_simple('th',$tr,false,'Navn');
	$th=$dom->createElement_simple('th',$tr,false,'Brukernavn');
	$th=$dom->createElement_simple('th',$tr,false,'Passord');
	foreach($users as $user)
	{
		$password=$passwords[mt_rand(0,count($passwords)-1)].mt_rand(100,999); //Lag tilfeldig passord med ordliste og tall
		$tr=$dom->createElement_simple('tr',$table);
		$dom->createElement_simple('td',$table,false,$user[0]);
		$dom->createElement_simple('td',$table,false,$user[1]);

		$status=true;
		//$status=$adtools->change_passord($dn,$password,true);

		if($status!==false)
			$dom->createElement_simple('td',$table,false,$password);
		else
			$dom->createElement_simple('td',$table,false,'Feil: '.$adtools->error);
	}
	$mail->Subject=utf8_decode('Passord for '.$_GET['group']);
	$mail->Body=utf8_decode($dom->saveXML($table));
	if (!$mail->send())
	{
		trigger_error($mail->ErrorInfo);
	}
	else
		printf('Passord for %s er sendt på mail til %s',$_GET['group'],$_SESSION['mail']);

}
else
{
	$dn=$_GET['dn'];
	//if(preg_match('/^CN=[a-zæøåÆØÅ0-9\-=, ]+$/i',$dn,$matches)===0)
	if(preg_match('/^CN=.+DC=.+$/i',$dn,$matches)===0)
		die('Ugyldig DN');

	$password=$passwords[mt_rand(0,count($passwords)-1)].mt_rand(100,999); //Lag tilfeldig passord med ordliste og tall
	$status=true;
	
	//$status=$adtools->change_passord($dn,$password,true);
	if($status!==false)
	{
		printf('Passordet for %s er endret til %s',$_GET['name'],$password);
		
		$mail->Subject = utf8_decode(sprintf('Passord endret for %s',$_GET['name']));
		$mail->Body    = utf8_decode(sprintf('Dette er en bekreftelse på at du har endret passordet for %s med brukernavn %s til %s',$_GET['name'],$_GET['username'],$password));

		if (!$mail->send())
		{
			trigger_error($mail->ErrorInfo);
		}
	}
	else
		printf('Feil ved endring av passord: %s',$adtools->error);

	//printf('Dette er en demo, passordet for %s er ikke endret til %s',$_GET['name'],$password);
}