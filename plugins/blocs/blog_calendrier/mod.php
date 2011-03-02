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
global $session;
require_once($root.'plugins/modules/blog/prerequis.php');

$tpl->set_filenames(array(
	  'blog_calendrier' => $root.'plugins/blocs/blog_calendrier/html/bloc_blog_calendrier.html'
));
if (!isset($module))$module='blog';
$tpl->assign_vars(array(
	'CHAINE'		=> 'module='.$module.'amp;date='.((isset($_GET['date']))? $_GET['date']:date('j/m/Y',$session->time))
));
?>