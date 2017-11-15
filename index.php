<?Php
session_start();
//ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
if(isset($_GET['logout']))
{
	$_SESSION = array();
	//header('Location: index.php');
}
require 'DOMDocument_createElement_simple.php';
$dom=new DOMDocumentCustom;
$dom->formatOutput=true;

if(!empty($_SESSION['username']))
{
	header('Location: brukerliste.php');
	die();
}
elseif(!empty($_POST))
{
	require 'adtools/adtools.class.php';
	$adtools=new adtools('auth');

	$status=$adtools->connect_and_bind($adtools->domain,$_POST['username'].'@'.$adtools->domain,$_POST['password']);

	if($status===false)
		$error=$adtools->error;
	else
	{
		$login_user=$adtools->find_object($_POST['username'],$adtools->dn,'username',array('memberof','mail'));
		if($login_user!==false)
		{
			$_SESSION['mail']=$login_user['mail'][0];
			$_SESSION['memberof']=$login_user['memberof'];
			$_SESSION['username']=$_POST['username'];
			header('Location: brukerliste.php');
			die();
		}
		else
			$error=$adtools->error;
	}
}
if(empty($_POST) || isset($error))
{
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Logg på for å endre passord</title>
<link href="skolepassord.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php

	$form=$dom->createElement_simple('form',false,array('method'=>'POST'));
	$dom->createElement_simple('h1',$form,false,'Endre passord for elever');
	$p=$dom->createElement_simple('p',$form,false,'Logg på med det brukernavnet og passordet du har på PCen for å endre passord for elever.');
	if(isset($error))
	{
		$error='Feil ved pålogging: '.$error;
		$dom->createElement_simple('p',$form,array('class'=>'error'),$error);
	}
	$p=$dom->createElement_simple('p',$form);
	$label=$dom->createElement_simple('label',$p,array('for'=>'username'),'Brukernavn: ');
	$input=$dom->createElement_simple('input',$p,array('type'=>'text','id'=>'username','name'=>'username'));
	$p=$dom->createElement_simple('p',$form);
	$label=$dom->createElement_simple('label',$p,array('for'=>'password'),'Passord: ');
	$input=$dom->createElement_simple('input',$p,array('type'=>'password','id'=>'password','name'=>'password'));
	$p=$dom->createElement_simple('p',$form);
	$input=$dom->createElement_simple('input',$p,array('type'=>'submit','name'=>'submit','value'=>'Logg på'));

	echo $dom->saveXML($form);
?>
</body>
</html>
<?Php
}
