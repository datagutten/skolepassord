<?php
$starttime=microtime(true);
session_start();
if(empty($_SESSION['mail']))
{
	header('Location: index.php');
	die();
}
?>
<!doctype html>
<?php
//ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
//ini_set('display_errors',1);
require 'DOMDocument_createElement_simple.php';
$dom=new DOMDocumentCustom;

$html=$dom->createElement_simple('html');


require 'pifu-php/pifu_parser_cache.class.php';
//printf("%3f sekund(er) linje %d\n",microtime(true)-$starttime,__LINE__);
$pifu=new pifu_parser_cache;
//printf("%3f sekund(er) linje %d\n",microtime(true)-$starttime,__LINE__);
require 'adtools/adtools.class.php';
$adtools=new adtools('reset');
//printf("%3f sekund(er) linje %d\n",microtime(true)-$starttime,__LINE__);
$dom->formatOutput=true;
$head=$dom->createElement_simple('head',$html);
$dom->createElement_simple('meta',$head,array('charset'=>'utf-8'));
$title=$dom->createElement_simple('title',$head,false,'Brukere');

$dom->createElement_simple('link',$head,array('href'=>'skolepassord.css','rel'=>'stylesheet','type'=>'text/css'));
$dom->createElement_simple('link',$head,array('href'=>'brukerliste.css','rel'=>'stylesheet','type'=>'text/css'));
$body=$dom->createElement_simple('body',$html);
$dom->createElement_simple('script',$body,array('type'=>'text/javascript','src'=>'change_password.js'),'');
$dom->createElement_simple('h1',$body,false,'Endre passord for elever');
$p=$dom->createElement_simple('p',$body,false,sprintf('Du er pålogget som %s. Endrede passord sendes til %s',$_SESSION['username'],$_SESSION['mail']));

require 'school_groups.php';
$user_units=array_keys(array_intersect($school_groups,$_SESSION['memberof']));
if(array_search('all',$user_units)!==false)
	$user_units=array_keys($school_groups);
if(empty($user_units))
	$dom->createElement_simple('p',$body,false,'Du har ikke tilgang til noen skoler');
elseif(count($user_units)===1) //User has only one school
	$school_key=$user_units[0];
elseif(isset($argv[1])) //School is specified on command line
	$school_key=$argv[1];
elseif(!isset($_GET['school'])) //Allow user to select school
{
	$dom->createElement_simple('h2',$body,false,'Velg skole');
	$ul=$dom->createElement_simple('ul',$body);

	foreach($pifu->schools() as $school)
	{
		$key=(string)$school->sourcedid->id;
		$schools_ordered[$key]=(string)$school->description->long;
	}
	asort($schools_ordered);
	foreach($schools_ordered as $key=>$school_name)
	{
		if(array_search($key,$user_units)===false) //Skip schools where the user has not access
			continue;
		//$skole=$school->description->long;
		$li=$dom->createElement_simple('li',$ul);
		$dom->createElement_simple('a',$li,array('href'=>'?school='.$key),$school_name);
	}
	//$dom->createElement_simple('p',$body,false,sprintf('%3f sekund(er) linje %d',microtime(true)-$starttime,__LINE__));
}
else
	$school_key=$_GET['school'];
if(isset($school_key))
{
	$groups=$pifu->ordered_groups($school_key);
	if($groups===false)
		die("Ingen klasser funnet. Ugyldig skole?");
	
	foreach($groups as $group)
	{
		$members=$pifu->ordered_members($group->sourcedid->id);
		if(empty($members))
			continue;
		$dom->createElement_simple('h2',$body,false,$groupname=$group->relationship->label.' '.$group->description->short);
		$table=$dom->createElement_simple('table',$body,array('border'=>1,'data-groupname'=>$groupname));		
		$header_row=$dom->createElement_simple('tr',$table);
		foreach(array('Navn','Brukernavn','Passord sist endret','Endre passord') as $text)
		{
			$dom->createElement_simple('th',$header_row,false,$text);
		}

		foreach($members as $member)
		{
			//var_Dump($member->role->{'@attributes'}->roletype!='01');

			if(!empty($member->role->{'@attributes'}) && $member->role->{'@attributes'}->roletype!='01') //Skip teachers
				continue;

			$person=$pifu->person($key=(string)$member->sourcedid->id);
			$tr=$dom->createElement_simple('tr',$table);
			$td=$dom->createElement_simple('td',$tr,array('class'=>'name'),(string)$person->name->fn);
			$guid=str_replace('person_','',$person->sourcedid->id);
			if(!isset($user_cache[$guid])) //User is not cached, lookup in AD
			{
				$result=$adtools->query(sprintf('(primaryTelexNumber=%s)',$guid),$adtools->config['dn'],array('samaccountname','pwdlastset','userAccountControl'));
				if($result===false) //First member is not found
					$user_cache[$guid]=$adtools->error;
				else
				{
					//Fill the cache with other users in the same OU
					$ou=substr($result['dn'],strpos($result['dn'],'OU='));
					$search=ldap_search($adtools->ad,$ou,'(objectClass=user)',array('samaccountname','primaryTelexNumber','pwdlastset','userAccountControl'));
					if($search===false)
					{
						trigger_error($ou);
						continue;
					}

					$users=ldap_get_entries($adtools->ad,$search);

					unset($users['count']);
					foreach($users as $user)
					{
						if(!isset($user['primarytelexnumber']))
							continue;
						$user_cache[$user['primarytelexnumber'][0]]=$user;
					}
				}
			}
			else
				$result=$user_cache[$guid];
			if(!is_array($user_cache[$guid]))
				$td=$dom->createElement_simple('td',$tr,array('class'=>'error','colspan'=>'3'),'Bruker ikke funnet');
			else
			{
				$usernames[$key]=$result['samaccountname'][0];
				//$dom->createElement_simple('data-dn',$tr,false,$result['dn']);
				$tr->setAttribute('data-dn',$result['dn']);
				$td=$dom->createElement_simple('td',$tr,array('class'=>'username'),$result['samaccountname'][0]);
				if($result['pwdlastset'][0]==0)
					$dom->createElement_simple('td',$tr,array('class'=>'pwdlastset'),'Må endres');
				else
				{
					$pwdlastset=$adtools->microsoft_timestamp_to_unix($result['pwdlastset'][0]);
					$td=$dom->createElement_simple('td',$tr,array('class'=>'pwdlastset'),date('d.m.Y H:i',$pwdlastset));
				}
				$td=$dom->createElement_simple('td',$tr,array('class'=>'reset_link reset_link_width','onclick'=>sprintf("change_password(this,'%s','%s','%s')",$result['dn'],$result['samaccountname'][0],(string)$person->name->fn)),'Endre passord');
			}
		}
		$tr=$dom->createElement_simple('tr',$table);
		$td=$dom->createElement_simple('td',$tr,array('colspan'=>4,'class'=>'reset_link','onclick'=>'reset_all(this)'),'Endre passord for hele klassen');
	}
}
$p=$dom->createElement_simple('p',$body);
$dom->createElement_simple('a',$p,array('href'=>'index.php?logout'),'Logg ut');
$runtime=microtime(true)-$starttime;
$dom->createElement_simple('p',$body,false,sprintf('Siden ble lastet på %3f sekund(er)',$runtime));
echo $dom->saveXML($html);
?>