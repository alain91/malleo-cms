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
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
require($root.'plugins/blocs/html/prerequis.php');
$action =  '';
$hidden = '<input type="hidden" name="action" value="ajouter" />';
if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
			
	// controles
	if (($action == 'ajouter' || $action == 'editer') && 
		($_POST['titre']=='' || trim($_POST['texte'])=='')){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
				'TEXTE'=>isset($_POST['texte'])?stripslashes($_POST['texte']):''));
		if ($action == 'ajouter') $action = '';
		if ($action == 'editer') $action = 'edit';
		$_GET = $_POST;
	}
	
	switch ($action)
	{
		case 'ajouter':
			$titre = protection_chaine($_POST['titre']);
			$texte = $_POST['texte'];
			//$texte = protection_chaine($_POST['texte']);
			//$texte = str_replace(array('&lt;','&gt;'),array('<','>'),$texte);
			$sql  =  'INSERT INTO '.TABLE_HTML.' (titre, texte) 
						VALUES (\''.str_replace("\'","''",$titre).'\',\''.str_replace("\'","''",$texte).'\')';
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
			break;
		case 'editer':
			$titre = protection_chaine($_POST['titre']);
			$texte = $_POST['texte'];
			//$texte = protection_chaine($_POST['texte']);
			//$texte = str_replace(array('&lt;','&gt;'),array('<','>'),$texte);
			$sql  =  'UPDATE '.TABLE_HTML.' SET
						titre=\''.str_replace("\'","''",$titre).'\',
						texte=\''.str_replace("\'","''",$texte).'\'
						WHERE id='.intval($_POST['id']);
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
			break;
		case 'supprimer':
			$sql  =  'DELETE FROM '.TABLE_HTML.' WHERE id='.intval($_GET['id']);
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
			break;
		case 'edit':
				$sql  =  'SELECT id,titre,texte FROM '.TABLE_HTML.' WHERE id='.intval($_GET['id']);
				if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
				$row = $c->sql_fetchrow($resultat);
				$tpl->assign_vars(array(
					'TITRE'	=> $row['titre'],
					'TEXTE'	=> $row['texte']					
				));
				$hidden = '<input type="hidden" name="action" value="editer" /><input type="hidden" name="id" value="'.$row['id'].'" />';
	}
	$cache->appel_cache('listing_blocs_html',true);
}

$tpl->set_filenames(array(
	'body_admin' => $root.'plugins/blocs/html/html/bloc_admin.html'
));
$sql = 'SELECT id,titre FROM '.TABLE_HTML;
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1020,__FILE__,__LINE__,$sql);
if ($c->sql_numrows($resultat) == 0){
	$tpl->assign_block_vars('noliste_blocs', array());
}else{
	while ($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('liste_blocs', array(
			'TITRE'		=> $row['titre'],
			'APERCU'	=> 'HTML_'.$row['id'],
			'EFFACER'	=> formate_url('action=supprimer&id='.$row['id'],true),
			'EDITER'	=> formate_url('action=edit&id='.$row['id'],true),
		));
	}
}

// On charge le wysiwyg
$WYSIWYG_METHODE = 'html';
$WYSIWYG_LOAD = 'unload';
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');



$tpl->assign_vars(array(
	'I_EDITER'					=>	$img['editer'],
	'I_EFFACER'					=>	$img['effacer'],
	'I_APERCU'					=>	$img['apercu'],
	'STYLE'						=>	$style_name,
	'HIDDEN'					=>	$hidden
));
?>