<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: BalHack
|  Copyright (c) 2008-2009, BalHack All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
| Url To Video - Transforme les url youtube, dailymotion, ... en vidéo
| Créé par BalHack - http://www.balhack.com
|------------------------------------------------------------------------------------------------------------
*/

if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
// Affichage final avec la vidéo incluse.
$habillage = '<div style=\"text-align: center;\">$urltovid</div>'; 

// Youtube 
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.|[a-z]{2,3}\.|)?youtube\.com\/watch\?v=(.*)(?:&)?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://www.youtube.com/v/\'.$matches[1].\'" type="application/x-shockwave-flash" width="480" height="385" wmode="transparent"></embed></object>\'; $name=\'youtube\';return "'.$habillage.'";');

// DailyMotion
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.|)dailymotion\.com\/(?:.*?)video\/(.*?)\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://www.dailymotion.com/swf/\'.$matches[1].\'" type="application/x-shockwave-flash" width="520" height="406" allowfullscreen="true"></embed></object>\'; $name=\'DailyMotion\';return "'.$habillage.'";');

// Metacafe
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?metacafe.com\/watch\/([0-9]*\/[A-Za-z0-9_]*)(?:[^"]+)?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://www.metacafe.com/fplayer/\'.$matches[1].\'.swf" width="520" height="406" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object>\'; $name=\'MetaCafe\';return "'.$habillage.'";');     

// MySpace
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/vids.myspace.com\/index.cfm\?fuseaction=vids\.individual&amp;videoid=(.*)?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid = \'<embed src="http://lads.myspace.com/videos/vplayer.swf" wmode="opaque" flashvars="m=\'.$matches[1].\'&v=2&type=video" type="application/x-shockwave-flash" width="430" height="346"></embed>\'; $name=\'MySpace\';return "'.$habillage.'";');

// AlloCiné
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?allocine.fr\/video\/player_gen_cmedia=(.*)&amp;cfilm=(.*).html\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://a69.g.akamai.net/n/69/10688/v1/img5.allocine.fr/acmedia/skin/allocinev5/acvision2/player/Acvision2/player_AcVision2.swf?HD=&Lang=5001&Media=\'.$matches[1].\'&RPath=www.allocine.fr&SeuilBD=900&Rld=1&SauveBP=30&Ref=\'.$matches[2].\'&TypeRef=film" type="application/x-shockwave-flash" wmode="opaque" width="442" allowfullscreen="true" height="370"></embed></object>\'; $name=\'AlloCin&eacute;\';return "'.$habillage.'";'); 

// Wideo
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?wideo.fr\/video\/(.*).html\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed type="application/x-shockwave-flash" src="http://sa.kewego.com/swf/p3/epix.swf" style="" id="flvplayer" name="flvplayer" quality="high" allowfullscreen="true" allowscriptaccess="always" wmode="opaque" swliveconnect="true" flashvars="playerKey=eae5c1a95e2f&amp;skinKey=&amp;language_code=fr&amp;stat=internal&amp;autoStart=true&amp;sig=\'.$matches[1].\'" width="400" height="300"></embed></object>\'; $name=\'Wideo\';return "'.$habillage.'";'); 
	
// MegaVideo
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?megavideo.com\/\?v=(.*)?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://wwwstatic.megavideo.com/mv_player.swf?v=\'.$matches[1].\'" type="application/x-shockwave-flash" allowfullscreen="true" width="484" height="418"></embed></object>\'; $name=\'MegaVideo\';return "'.$habillage.'";'); 

// Koreus
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?koreus.com\/video\/(.*).html?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed src="http://www.koreus.com/video/\'.$matches[1].\'" type="application/x-shockwave-flash" width="400" height="300"></embed></object>\'; $name=\'Koreus\';return "'.$habillage.'";');

// plusfortquelatele
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?plusfortquelatele.com\/scripts\/lecteur\/PLAYEREXPORT.swf\?video=(.*).flv?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object><embed type="application/x-shockwave-flash" src="http://www.plusfortquelatele.com/scripts/lecteur/PLAYER.swf?video=\'.$matches[1].\'.flv" id="single" name="single" quality="high" allowfullscreen="true" allowscriptaccess="always" width="524" height="440"></embed></object>\'; $name=\'plusfortquelatele\';return "'.$habillage.'";');
// Viemo
$urltovid[] = array (
	'searchurl'		=> '/\[url=http:\/\/(?:www\.)?vimeo.com\/(.*)?\](.*?)\[\/url\]/i',
	'replacevid'	=> '$urltovid =  \'<object class="swf_holder" type="application/x-shockwave-flash" width="506" height="380" data="http://www.vimeo.com/moogaloop_local.swf?clip_id=\'.$matches[1].\'&amp;server=www.vimeo.com&amp;autoplay=0&amp;fullscreen=1&amp;show_portrait=0&amp;show_title=0&amp;show_byline=0&amp;md5=&amp;color="><param name="quality" value="high" /><param name="allowfullscreen" value="true" /><param name="scale" value="showAll" /><param name="movie" value="http://www.vimeo.com/moogaloop_local.swf?clip_id=\'.$matches[1].\'&amp;server=www.vimeo.com&amp;autoplay=0&amp;fullscreen=1&amp;show_portrait=0&amp;show_title=0&amp;show_byline=0&amp;md5=&amp;color=" /></object>\'; $name=\'Vimeo\';return "'.$habillage.'";');

// JW Player flv 
$urltovid[] = array (
	'searchurl'		=> '#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)(.flv|.mp4)\]([^?\n\r\t].*?)\[/url\]#i',
	'replacevid'	=> '$urltovid =  \'<embed src="librairies/JWPlayer/player.swf" width="500" height="300" allowscriptaccess="always" allowfullscreen="true" flashvars="file=\'.$matches[1].$matches[2].\'&image=malleo.jpg&autostart=false"/></embed>\'; $name=\'Player flv\';return "'.$habillage.'";');

// Windows Media Player 
$urltovid[] = array (
	'searchurl'		=> '#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)(.wma|.wmv)\]([^?\n\r\t].*?)\[/url\]#i',
	'replacevid'	=> '$urltovid =  \'<object id="MediaPlayer" width="425" height="350" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Windows Media Player components..." type="application/x-oleobject"> <param name="FileName" value="\'.$matches[1].$matches[2].\'"> <param name="autostart" value="true"> <param name="ShowControls" value="true"> <param name="ShowStatusBar" value="false"> <param name="ShowDisplay" value="false"> <embed type="application/x-mplayer2" src="\'.$matches[1].$matches[2].\'" name="MediaPlayer" width="425" height="350" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="1"></embed> </object>\'; $name=\'Windows Media Player\';return "'.$habillage.'";');

foreach ($urltovid as $k => $v){
	$text = preg_replace_callback($v['searchurl'],create_function('$matches',$v['replacevid']),$text);
}
?>