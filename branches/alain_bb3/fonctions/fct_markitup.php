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
load_lang('markitup');

// METHODE de fonctionnement: BBcodes ou HTML
$WYSIWYG_METHODE = (isset($WYSIWYG_METHODE) && $WYSIWYG_METHODE=='html') ? 'html':'bbcodes';
$tpl->set_filenames(array('wysiwyg'=>$root.'html/markitup_'.$WYSIWYG_METHODE.'.html'));

// Style personnalise ou par defaut ?
$file_css = (file_exists($root.'styles/'.$style_name.'/style_markitup.css'))? 'style_personnalise':'style_defaut';
$tpl->assign_block_vars($file_css, array());

// Profile d'Affichage : simple ou avance
$WYSIWYG_PROFILE = (defined('IPHONE'))?'iphone':((isset($WYSIWYG_PROFILE) && $WYSIWYG_PROFILE=='simple') ? 'simple':'avance');
$tpl->assign_block_vars($WYSIWYG_PROFILE, array());

// Profile de Chargement : A l'affichage ou a la demande du user ?
$WYSIWYG_LOAD = (isset($WYSIWYG_LOAD) && $WYSIWYG_LOAD=='unload' || defined('IPHONE')) ? 'unload':'load';
$tpl->assign_block_vars($WYSIWYG_LOAD, array());

// Liste des smileys
$sql = 'SELECT titre_smiley, url_smiley  FROM '.TABLE_SMILEYS.' ORDER BY ordre ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
$cols=$lignes=0	;
while($row = $c->sql_fetchrow($resultat))
{
	$tpl->assign_block_vars('liste_smileys', array(
		'TITRE'		=> utf8_encode(str_replace("'","\'",str_replace("&#039;","'",$row['titre_smiley']))),
		'IMAGE'		=> PATH_SMILEYS.$row['url_smiley']
	));
}

// Liste des Langages connus
$liste = array('asp','c','cpp','cpp-qt','css','html4strict','java','javascript','perl','php','sql','xml');
foreach($liste as $langue){
	$tpl->assign_block_vars('liste_langages', array(
		'CODE' => $langue
	));
}

$tpl->assign_vars(array(
	'STYLE'			=>	$style_name,
	'ROOT'			=>	$root,
));

$tpl->assign_var_from_handle('WYSIWYG','wysiwyg');
?>