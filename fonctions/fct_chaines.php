<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2012, Alain GANDON All Rights Reserved
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

//
// supprime les entites HTML incompletes
function clean_amp($chaine){
	return str_replace('amp;','',$chaine);
}

//
// Protection des quotes si la propriete magic_quotes n'est pas activee
// Nettoyage des clefs si des &amp; apparaissent

function __stripslashes_deep($value)
{
    $value = is_array($value)
                ? array_map('stripslashes_deep', $value)
                : stripslashes($value);
    return $value;
}


function protection_variables()
{
    if((function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
    || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!='off')) )
    {
        __stripslashes_deep($_GET);
        __stripslashes_deep($_POST);
        __stripslashes_deep($_COOKIE);
    }
}

//
// Fonction nettoyant les saisies de tous les caracteres non imprimables et des codes pouvant
// Porter atteinte à l'intégrité du code.
// SOURCE de la fonction : http://ha.ckers.org/xss.html
function RemoveXSS($val) {

	return $val;
	/*return htmlspecialchars($val);
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs

  // $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:,?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++){
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
      // &#x0040 @ search for the hex values
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // &#00064 @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }
    return $val;*/
}

//
// REMPLACEMENT des caracteres accentues par leur equivalent HTML
function str_to_html($chaine,$blocage_conversion=false)
{
	global $cf;
	if ($blocage_conversion == false){
		$chaine = mb_convert_encoding($chaine, $cf->config['charset'], mb_detect_encoding($chaine));
	}
	return str_replace(array('á','à','â','ã','ä','é','è','ê','ë','ç','ñ','ó','ò','ô','õ','ö','ì','í','î','ï','ú','ù','û','ü'),
						array(	'&aacute;','&agrave;','&acirc;','&atilde;','&auml;','&eacute;','&egrave;','&ecirc;','&euml;','&ccedil;','&ntilde;',
				'&oacute;','&ograve;','&ocirc;','&otilde;','&ouml;','&iacute;','&igrave;','&icirc;','&iuml;','&uacute;','&ugrave;','&ucirc;','&uuml;'),$chaine);
}
// fonction inverse a la precedente
function html_to_str($chaine,$blocage_conversion=false)
{
	global $cf;
	if ($blocage_conversion == true){
		$chaine = mb_convert_encoding($chaine, $cf->config['charset'], mb_detect_encoding($chaine));
	}
	return str_replace(array('&aacute;','&agrave;','&acirc;','&atilde;','&auml;','&eacute;','&egrave;','&ecirc;','&euml;','&ccedil;','&ntilde;',
				'&oacute;','&ograve;','&ocirc;','&otilde;','&ouml;','&iacute;','&igrave;','&icirc;','&iuml;','&uacute;','&ugrave;','&ucirc;','&uuml;'),
				array('á','à','â','ã','ä','é','è','ê','ë','ç','ñ','ó','ò','ô','õ','ö','ì','í','î','ï','ú','ù','û','ü'),$chaine);
}

function conversion_charset($chaine){
	global $cf;
	$chaine = mb_convert_encoding($chaine, $cf->config['charset'], "auto");
	return $chaine;
}
//
// REMPLACEMENT des caracteres Accentues par leur equivalent sans accent
function supprimer_accents($chaine)
{
	global $cf;
 	$chaine = strtr($chaine,array(
	'¥'=>'Y','µ'=>'s','Æ'=>'A','æ'=>'a','Å'=>'A','å'=>'a','ð'=>'o','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','ç'=>'c',
	'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o',
	'ö'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y','À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Ç'=>'C',
	'È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O',
	'Ö'=>'O','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y'));
	//$chaine = strtr($chaine,	'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	return $chaine;
}

//
// fonctions de nettoyage de saisies(
function nettoyage_nom($username){
 	return protection_chaine(substr(trim($username), 0, 30));
}
function nettoyage_mail($mail){ 	return htmlentities(trim($mail));}
function nettoyage_pass($pass){ 	return htmlspecialchars(trim($pass));}
function protection_chaine($chaine){
	global $cf;
	if (!is_string($chaine)) return $chaine;
	if (!isset($cf->config)) $cf->config['charset'] = 'UTF-8';
	//if (mb_detect_encoding($chaine) != "ISO-8859-1"){ $chaine = mb_convert_encoding($chaine,"ISO-8859-1","auto"); }
	$chaine = htmlentities($chaine,ENT_QUOTES,$cf->config['charset']);
	//$chaine =  mb_convert_encoding($chaine,$cf->config['charset'],"ISO-8859-1");
	return $chaine;
}

//
// Rend les URL cliquables
function url_cliquable($chaine){	return preg_replace("/([[:alnum:]]+):\/\/([^[:space:]]*)([[:alnum:]#?\/&=])/i",'<a href="\\1://\\2\\3">\\2\\3</a>',$chaine);}
