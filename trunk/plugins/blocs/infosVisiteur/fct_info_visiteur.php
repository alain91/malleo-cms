<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
function info_visiteur_get_ip() 
{
	if (isset($_SERVER['HTTP_CLIENT_IP'])){ 
		return $_SERVER['HTTP_CLIENT_IP']; 
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ 
		return $_SERVER['HTTP_X_FORWARDED_FOR']; 
	}else{ 
		return $_SERVER['REMOTE_ADDR']; 
	}
} 

function info_visiteur_get_browser($browser)
{
	if (preg_match("/MSIE/", $browser)) {
	   return 'Internet explorer';
	} else if (preg_match("/Firefox\/2./", $browser)) {
	   return 'Firefox 2.x';
	} else if (preg_match("/Firefox\/1./", $browser)) {
	   return 'Firefox 1.x';
	} else if (preg_match("/^Mozilla\//", $browser)) {
	   return 'Mozilla';
	} else if (preg_match("/^Opera\//", $browser)) {
	   return 'Opera';
	} else {
	   return 'Inconnu';
	}
}

function info_visiteur_get_langue($langue)
{
	$langs=explode(",",$langue);
	return $langs[0];
}

function info_visiteur_get_os($os)
{
	if (preg_match("/Linux/", $os)) {    								return "linux";
	} else if (preg_match("/WinNT/", $os)||preg_match("/Windows NT/", $os)) {	return "Windows XP/NT/2000";
	} else if (preg_match("/Windows 98/", $os)||preg_match("/Win98/", $os)) {	return "Windows 98";
	} else if (preg_match("/Windows 95/", $os)||preg_match("/Win95/", $os)) {	return "Windows 95";
	} else if (preg_match("/Macintosh/", $os)||preg_match("/Mac_PowerPC/", $os)){return "Mac OS X / Tiger>";
	} else return "Inconnu";
}
?>
