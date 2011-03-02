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
// Constante de table
global $prefixe;
define('TABLE_HTML',	$prefixe.'bloc_html');
// Fichier de langue du bloc
load_lang_bloc('html');

$code='';
if (!function_exists('exe_code'))
{
	function exe_code($texte)
	{
		ob_start();
		eval($texte);
		$code = ob_get_contents();
		ob_end_clean();
		return $code;
	}
	
	function lister_blocs_html(){
		global $c;
		$liste_blocs_html = array();
		$sql = 'SELECT id, titre, texte FROM '.TABLE_HTML;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
		while ($row = $c->sql_fetchrow($resultat))
		{
			$liste_blocs_html[$row['id']] = $row;
		}
		return $liste_blocs_html;
	}
}
?>