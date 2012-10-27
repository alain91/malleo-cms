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
require_once($root.'plugins/modules/blog/prerequis.php');

$tpl->set_filenames(array(
	  'blog_last_coms' => $root.'plugins/blocs/blog_last_coms/html/bloc_blog_last_coms.html'
));

include_once($root.'class/class_posting.php');
$post = new posting();

if (!isset($module))$module='blog';

$sql = 'SELECT DISTINCT b.id_billet,b.titre_billet, c.user_id, c.pseudo, c.email, c.site, c.date, u.pseudo AS PseudoUser, u.avatar
		FROM '.TABLE_BLOG_BILLETS.' AS b 
		LEFT JOIN '.TABLE_BLOG_COMS.' AS c 
			ON (b.id_billet=c.id_billet)
		LEFT JOIN '.TABLE_USERS.' AS u
			ON 	(c.user_id=u.user_id) 
		LEFT JOIN '.TABLE_BLOG_CATS.' AS cat
			ON 	(b.id_cat=cat.id_cat) 
		WHERE cat.module="'.$module.'" 
		AND nbre_coms>0 
		ORDER BY date DESC
		LIMIT 12';

if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,504,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat)==0 ){
	$tpl->assign_block_vars('blog_no_coms', array());
}else{
	$ids = array();
	while ($row = $c->sql_fetchrow($resultat)){
		if (!in_array($row['id_billet'],$ids) && (sizeof($ids) <= 10)){
			$ids[] = $row['id_billet'];
			$D = explode(':',date('j:n:Y:H:i',$row['date']));
			$D = sprintf($lang['L_DATE'],$D[0],$D[1],$D[2],$D[3],$D[4]);
			$com = utf8_encode(html_to_str($row['titre_billet']));
			$com = str_to_html((strlen($com)>25)? substr($com,0,25):$com);
			$tpl->assign_block_vars('blog_liste_coms', array(
				'URL'		=> formate_url('mode=billet&id_billet='.$row['id_billet'].'#commentaires',1),
				'COM'		=> $com,
				'DATE'		=> $D,
				'AUTEUR'	=> ($row['user_id']>1)? formate_pseudo($row['user_id'],$row['PseudoUser']):$row['pseudo']
			));
		}
	}
}
$tpl->assign_vars(array(
	'MODULE'		=> $module,
	'DATE_TODAY'	=> (isset($_GET['date']))? $_GET['date']:date('j/m/Y',$session->time)
));
?>