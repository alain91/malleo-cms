<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Annonces
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2012, Alain GANDON All Rights Reserved
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

defined('RECHERCHE_PATH') OR die("Tentative de Hacking");

class action_lister extends Action
{
	function init()
	{
		global $droits,$module,$lang;

		if (!$droits->check($module,0,'lire'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $tpl,$jeton;
		
		// Creation du jeton de securite
		$jeton = $this->creer_jeton();

		$tpl->set_filenames(array(
			'recherche' => RECHERCHE_PATH.'/html/liste.html',
		));

		// Titre de page
		$tpl->titre_navigateur = $lang['L_TITRE_PAGE'];
		$tpl->titre_page = $lang['L_TITRE_PAGE'];

		$this->lister();
	}

	function lister()
	{
		global $session,$tpl,$droits,$module,$img,$lang,$user,$jeton;
		
		$recherche = Recherche::instance();

		$target = formate_url('action=lister'.(empty($id_cat)?'':'&id_cat='.$id_cat),true);
		$target = str_replace('&amp;','&',$target);

    }

	function render_view($recherche, $row)
	{
		global $tpl,$user,$lang,$droits,$module,$jeton,$type_options;

		$type = empty($row['type']) ? '' : $this->type_options[intval($row['type'])];

		$tpl->assign_block_vars('liste.item',array(
			'ID' 		=> $row['id'],
			'TYPE'	 	=> $type,
			'TITRE' 	=> htmlentities($row['title']),
			'CONTENU' 	=> htmlentities($row['contents']),
			'PRIX'		=> empty($row['price'])?'-':(int)$row['price'],
			'DATE_APPROBATION' => empty($row['approved_date'])?'-':date('d/m/Y',$row['approved_date']),
			'IMAGE'	 	=> $row['picture'],
		));

	}

}
?>