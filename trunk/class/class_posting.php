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
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $cache;
load_lang('posting');
require_once($root.'librairies/geshi/geshi.php');
$id_code = 0;
// cache recurent
global $listing_smileys;
$listing_smileys = $cache->appel_cache('listing_smileys');

//
// Transforme une chaine en HTML. 
// Le code respecte la coloration syntaxique officielle du langage. 
// Certains mots clefs sont cliquables pour en obtenir la definition
function formate_geshi($source)
{
	global $id_code,$lang,$img,$root;
	$id_code++;
	// Choix du langage
	$type_demande = eregi_replace('[^a-z0-9-]','',$source[1]);
	$type_demande= (file_exists($root.'librairies/geshi/geshi/'.$type_demande.'.php'))? $type_demande:'html4strict';
	// Formatage du langage
	$code = str_replace('\t\t','\t',utf8_encode(html_entity_decode($source[2])));
	$geshi = new GeSHi($code, $type_demande);
	$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
	$icone = (isset($img['code_'.$type_demande]))? $img['code_'.$type_demande]:$img['code_html4strict'];
	$retour = '<div class="codeStyle">';
	if (isset($img['code_'.$type_demande])) $retour .= '<img src="'.$img['code_'.$type_demande].'" alt="" style="float:right;" />';
	$retour .='&nbsp;<a href="javascript:void(0);" id="zone_'.$id_code.'" onclick="afficher_code(\''.$id_code.'\',\''.$lang['L_CODE_TOUT_VOIR'].'\',\''.$lang['L_CODE_TOUT_CACHER'].'\');">'.$lang['L_CODE_TOUT_VOIR'].'</a>'
			.'<div class="code" id="'.$id_code.'">'.$geshi->parse_code().'</div></div>';
	return $retour;
}
//
// Transforme les mots clefs WIKI en code correct.
// Les accents sont supprimes
// Les caracteres non alphanumeriques sont remplaces par un underscore.
function formate_wiki($source){
	$val = html_to_str($source[1]);
	$val = supprimer_accents($val);
	$val = eregi_replace('[^a-z0-9]','_',$val);
	$val = ereg_replace('[_]{2,}','_',$val);
	return '<a href="'.formate_url('t='.$val,true).'" class="wiki">'.$source[1].'</a>';
}

//
// Trie, redimensionne et sécurise les images
function formate_image($source){
	global $listing_smileys,$cf;
	$align='';
	if (array_key_exists(1,$source)){
		switch($source[1]){
			case '=left': $align=' align="left"';break;
			case '=right': $align=' align="right"';break;
			case '=justify': $align=' align="justify"';break;
			case '=center': $align=' align="center"';break;
		}
	}
	
	// Affichage optimise des smileys
	if (is_array($listing_smileys) && array_key_exists($source[2],$listing_smileys)){
		return '<img src="'.$source[2].'" alt="'.$listing_smileys[$source[2]].'" title="'.$listing_smileys[$source[2]].'"'.$align.' />';
	// Affichage direct des images locales
	}elseif (eregi('data/',$source[2])
		|| eregi('http://'.$cf->config['adresse_site'].$cf->config['path'],$source[2])){
		$url = ereg_replace('http://'.$cf->config['adresse_site'].$cf->config['path'],'',$source[2]);
		
		// Si l'image est trop grande on la reduit et on active lightbox
		if (file_exists($url)){
			$infos=@getimagesize($url);
			$taille = '';
			if($infos[0]>=$infos[1] && $infos[0]>400){
				$taille = 'width="300"';
			}elseif($infos[1]>$infos[0] && $infos[1]>400){
				$taille = 'height="300"';	
			}
			if ($taille != '' && $cf->config['activer_lightbox'] == 1){
				return '<a href="'.$source[2].'" rel="lightbox[1]">
							<img src="'.$source[2].'" alt="'.$source[2].'" '.$taille.' '.$align.' /></a>';		
			}else{
				return '<img src="'.$source[2].'" alt="'.$source[2].'" '.$align.' />';
			}
		}else return '';
	// PROTECTION contre les failles CSRF
	}elseif (verifie_existance_image($source[2])){
		return '<img src="'.$source[2].'" alt="'.$source[2].'" '.$align.' />';
	// Image remplacee
	}else{
		return '<img src="data/images/image_bloquee.jpg" alt="Hacking !"'.$align.' />';
	}
}

//
// Protection des emails
include_once($root.'fonctions/fct_profil.php');
function formate_email($email){
	return formate_info_user('email',$email[1]);
}

class posting
{
	var $text;
	
	function bbcode2html($text)
	{
		global $root,$listing_smileys;
		// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
		// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
		$text = " " . $text;
		
		$text = str_replace("&amp;","&",$text);
		$text = str_replace("&#039;","'",$text);
		
		// Transformation des urls en vidéos
		include($root.'fonctions/fct_urltovideo.php');
		
		// Coupure de page
		$text = str_replace("[pagebreak]", '<!-- pagebreak -->', $text);
		// Ligne horizontale
		$text = str_replace("[hr]", '<hr />', $text);
		// Hx
		$text = preg_replace("/\[h([1-3]{1})=([0-9]+)\]/si", '<h\\1 style="padding-left: \\2px;">', $text);
		$text = preg_replace("/\[h([1-3]{1})\]/si", '<h\\1>', $text);
		$text = preg_replace("/\[\/h([1-3]{1})\]/si", '</h\\1>', $text);
		// CODE
		$text = preg_replace_callback("#\[code=(.*?)\](.*?)\[/code\]#is", 'formate_geshi', $text);
		// WIKI
		$text = preg_replace_callback("#\[wiki\](.*?)\[/wiki\]#is", 'formate_wiki', $text);
		// Tags smileys
		if (is_array($listing_smileys) && array_key_exists('tags',$listing_smileys) && array_key_exists('urls',$listing_smileys)
			&& is_array($listing_smileys['tags']) && is_array($listing_smileys['urls'])){
			$text = str_replace($listing_smileys['tags'],$listing_smileys['urls'],$text);
		}
		// Images
		$text = preg_replace_callback("#\[img(.*?)\]([^?](?:[^\[]+|\[(?!url))*?)\[/img\]#is", 'formate_image', $text);
		// Emails
		$text = preg_replace_callback("#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#is", 'formate_email', $text);
		$text = preg_replace_callback("#([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#is", 'formate_email', $text);

		// QUOTE
		$text = preg_replace("/\[quote[=]{0,1}(.*?)\]/si", '<div class="quoteStyle"><div class="quoteAuteur">$1</div><div class="quote">', $text);
		$text = str_replace("[quote]", '<div class="quoteStyle"><div class="quoteAuteur">&nbsp;</div><div class="quote">', $text);
		$text = str_replace("[/quote]", '</div></div>', $text);
		// Sup
		$text = str_replace("[sup]", '<sup>', $text);
		$text = str_replace("[/sup]", '</sup>', $text);
		// Sub
		$text = str_replace("[sub]", '<sub>', $text);
		$text = str_replace("[/sub]", '</sub>', $text);
		// listes
		$text = str_replace("[list]", '<ul>', $text);
		$text = str_replace("[list1]", '<ol>', $text);
		$text = str_replace("[*]", '<li>', $text);
		$text = str_replace("[/*]", '</li>', $text);
		$text = str_replace("[/list]", '</ul>', $text);
		$text = str_replace("[/list1]", '</ol>', $text);
		// couleurs d'arriere plan
		$text = preg_replace("/\[bgcolor=(\#[0-9A-F]{6}|[a-z]+)\]/si", '<span style="background-color:\\1;">', $text);
		// couleurs
		$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\]/si", '<span style="color:\\1;">', $text);
		// taille de la font
		$text = preg_replace("/\[size=?([0-9]+)\]/si", '<span style="font-size:\\1px;">', $text);
		// Fermetures de span
		$text = preg_replace("#\[\/(size|color|bgcolor|quote|code)\]#is", '</span>', $text);
		//  gras
		$text = str_replace("[b]", '<b>', $text);
		$text = str_replace("[/b]", '</b>', $text);
		// souligner
		$text = str_replace("[u]", '<u>', $text);
		$text = str_replace("[/u]", '</u>', $text);
		//  italique
		$text = str_replace("[i]", '<i>', $text);
		$text = str_replace("[/i]", '</i>', $text);
		// Adresse
		$text = str_replace("[address]", '<address>', $text);
		$text = str_replace("[/address]", '</address>', $text);
		//  barré
		$text = str_replace("[strike]", '<span style="text-decoration: line-through;">', $text);
		$text = str_replace("[/strike]", '</span>', $text);
		// Alignement
		$text = preg_replace("/\[align=([a-z]+)\]/si", '<p style="text-align:\\1;">', $text);
		$text = preg_replace("/\[align=([0-9]+)\]/si", '<p style="padding-left: \\1px;">', $text);
		$text = str_replace("[/align]", '</p>', $text);
		
		// Patterns and replacements for URL and email tags..
		$patterns = array();
		$replacements = array();
		
		// Vieux BBcodes pour pas me faire taper dessus
		$patterns[] = "#\[video=youtube\](.*?)::([0-9]{1,4})::([0-9]{1,4})\[/video\]#i";
		$replacements[] = '<object width="\\2" height="\\3"><param name="movie" value="http://www.youtube.com/v/\\1&amp;rel=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/\\1&amp;rel=1" type="application/x-shockwave-flash" allowfullscreen="true" width="\\2" height="\\3"></embed></object>';
		$patterns[] = "#\[video=dailymotion\](.*?)::([0-9]{1,4})::([0-9]{1,4})\[/video\]#i";
		$replacements[] = '<object width="\\2" height="\\3"><param name="movie" value="http://www.dailymotion.com/swf/\\1&amp;v3=1&amp;related=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.dailymotion.com/swf/\\1&amp;v3=1&amp;related=1" type="application/x-shockwave-flash" allowfullscreen="true" width="\\2" height="\\3"></embed></object>';

		// Taille specifiee
		$patterns[] = "#\[youtube width=([0-9]{1,4}) height=([0-9]{1,4})\](.*?)\[/youtube\]#i";
		$replacements[] = '<object width="\\1" height="\\2"><param name="movie" value="http://www.youtube.com/v/\\3&amp;rel=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/\\3&amp;rel=1" type="application/x-shockwave-flash" allowfullscreen="true" width="\\1" height="\\2"></embed></object>';
		// Taille non specifiee
		$patterns[] = "#\[youtube\](.*?)\[/youtube\]#i";
		$replacements[] = '<object width="350" height="300"><param name="movie" value="http://www.youtube.com/v/\\1&amp;rel=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/\\1&amp;rel=1" type="application/x-shockwave-flash" allowfullscreen="true" width="350" height="300"></embed></object>';
		// Taille specifiee
		$patterns[] = "#\[dailymotion width=([0-9]{1,4}) height=([0-9]{1,4})\](.*?)\[/dailymotion\]#i";
		$replacements[] = '<object width="\\1" height="\\2"><param name="movie" value="http://www.dailymotion.com/swf/\\3&amp;v3=1&amp;related=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.dailymotion.com/swf/\\3&amp;v3=1&amp;related=1" type="application/x-shockwave-flash" allowfullscreen="true" width="\\1" height="\\2"></embed></object>';
		// Taille non specifiee
		$patterns[] = "#\[dailymotion\](.*?)\[/dailymotion\]#i";
		$replacements[] = '<object width="350" height="300"><param name="movie" value="http://www.dailymotion.com/swf/\\1&amp;v3=1&amp;related=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.dailymotion.com/swf/\\1&amp;v3=1&amp;related=1" type="application/x-shockwave-flash" allowfullscreen="true" width="350" height="300"></embed></object>';
		// Taille specifiee
		$patterns[] = "#\[flash width=([0-9]{1,4}) height=([0-9]{1,4})\](.*?)\[/flash\]#i";
		$replacements[] = '<object type="application/x-shockwave-flash" data="\\3" width="\\1" height="\\2"><param name="movie" value="\\3"/><param name="type" value="application/x-shockwave-flash" /><param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer/" /><param name="menu" value="false" /><embed src="\\3" menu="false" type="application/x-shockwave-flash" width="\\1" height="\\2" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
		// Taille non specifiee
		$patterns[] = "#\[flash\](.*?)\[/flash\]#i";
		$replacements[] = '<object type="application/x-shockwave-flash" data="\\1" width="350" height="300"><param name="movie" value="\\1"/><param name="type" value="application/x-shockwave-flash" /><param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer/" /><param name="menu" value="false" /><embed src="\\1" menu="false" type="application/x-shockwave-flash" width="350" height="300" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';

		// matches a [url]xxxx://www.phpbb.com[/url] code..
		$patterns[] = "#\[url\]([\w]+?://([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
		$replacements[] = '<a href="\\1" target="_blank">\\1</a>';
		// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
		$patterns[] = "#\[url\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
		$replacements[] = '<a href="\\1" target="_blank">\\1</a>';
		// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
		$patterns[] = "#\[url=(.*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="\\1" target="_blank">\\2</a>';
		// [url=xxxx://www.phpbb.com]phpBB[/url] code..
		$patterns[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="\\1" target="_blank">\\2</a>';
		// pas de balises
		$patterns[] = "#(^|[\s ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#si";
        $replacements[] = '\\1<a href="\\2\\3" target="_blank">\\2\\3</a>';
		$patterns[] = "#(^|[\s ])(www.[\w\#$%&~/.\-;:=,?@\[\]+]*)#si";
		$replacements[] = '\\1<a href="http://\\2" target="_blank">\\2</a>';

		$text = preg_replace($patterns, $replacements, $text);

		// on vire l'espace ajouté au début et on fait du ménage
		$text = substr($text, 1);
		$text = str_replace("\n<li>","<li>",$text);
		$text = str_replace("\n<ul>","<ul>",$text);
		$text = str_replace("\n</ul>","</ul>",$text);
		$text = str_replace("\r\n","<br />",$text);
		$text = str_replace("\n","<br />",$text);
		return $text;
	}
}
?>