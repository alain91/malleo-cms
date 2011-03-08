<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Smallads
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
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

class Smallads
{
	var $t=null;
	var $id_version=0;
	var $id_tag=0;
	var $titre='';
	var $texte='';
	var $infos='';
	var $verrouiller='false';
	var $terminer='false';
	
	//
	// Nettoie les valeurs passees en parametre
	
	function cat()
	{
		global $c,$module,$user,$tpl,$lang,$start,$cf,$num,$verif,$mode,$root,$style_name,$img,$droits;
		$tpl->set_filenames(array(
				'down' => $root.'plugins/modules/down/html/cat.html')
				);
		$sql='SELECT chan1,chan2,... FROM '.TABLE_SMALLADS_<TABLE_MODULE>.' ORDER BY champ1 ASC';
		$verif = $c->sql_query($sql) or die(message_die(E_ERROR,702,__FILE__,__LINE__,$sql));
		
		if ($c->sql_numrows($verif) == 0 )
		{
			$tpl->assign_block_vars('aucune_page',array('AUCUN' => "Pas De Cat&eacute;gories Enregistr&eacute;es"));
		}
		else
		{
			while ($row = $c->sql_fetchrow($verif))
			{
				$tpl->assign_block_vars('liste',array(
					'FICH'	=> $row['chan1'],
					'URL'	=> $row['chan1'],
					));
			}
		}
		
		if ($droits->check($module,3,'ecrire'))
		{
		$tpl->assign_vars(array(
		'TITREL'			=>  $lang['L_TITRE'],
		'TITREL2'			=>  $lang['L_DESC'],
		
		));}else{
		$tpl->assign_vars(array(
		'TITREL'			=>  "non",
		'TITREL2'			=>  "c mort",
		));}
	}
	
	function files()
	{
		global $c,$module,$droits,$img,$user,$tpl,$lang,$start,$cf,$num,$verif,$mode,$root,$style_name,$urlfile;
	}
	
	function idcat()
	{
		global $c,$module,$droits,$img,$user,$tpl,$lang,$start,$cf,$num,$verif,$mode,$root,$style_name,$urlfile;
	}
	
	function idfile()
	{
		global $c,$module,$user,$tpl,$lang,$start,$cf,$num,$verif,$mode,$root,$style_name,$urlcat;
	
	}
	
}
?>