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
if ( !defined('PROTECT'))
{
	die("Tentative de Hacking");
}
include_once($root.'plugins/blocs/html/prerequis.php');


$tpl->set_filenames(array(
		'HTML_'.$id_bloc_html => $root.'plugins/blocs/html/html/bloc_html.html'
));

$liste_blocs_html = $cache->appel_cache('listing_blocs_html');

if ($id_bloc_html>0 &&  array_key_exists($id_bloc_html,$liste_blocs_html))
{
		if (ereg('<?php',$liste_blocs_html[$id_bloc_html]['texte']))
		{
			$texte = '?>'."\n ".$liste_blocs_html[$id_bloc_html]['texte'].'<?php'."\n ";
			$code = exe_code($texte);
		}else{
			$code = $liste_blocs_html[$id_bloc_html]['texte'];
		}
		
		$tpl->assign_vars( array(
			'TITRE_BLOC'	=>	$liste_blocs_html[$id_bloc_html]['titre'],
			'CONTENU'		=>	$code
		));
}
?>