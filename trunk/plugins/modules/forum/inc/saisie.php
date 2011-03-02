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
if (!$droits->check($module,0,'voir')) error404(725);

$tpl->set_filenames(array('forum'=>$root.'plugins/modules/forum/html/saisie.html'));
$hidden='';

if ($mode == 'Enregistrer' 
		&& ((isset($_POST['id_forum']) && isset($_POST['titre']) && (trim($_POST['titre'])=='')) 
		|| (trim($_POST['post'])==''))){
	erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
				'POST'=>isset($_POST['post'])?stripslashes($_POST['post']):''));
	$mode = (isset($_POST['id_forum']))? 'NouveauTopic':'NouveauPost';
	$_GET = $_POST;
}
		
switch($mode)
{
	// SUPPRESSION de l'INDEX NON_LU pour le USER
	case 'marquer_lu':
		$f->marquer_tout_lu();
		break;
	// Suivre le sujet
	case 'SuivreTopic':
		$f->suivre_sujet(intval($_GET['id_topic']));
		break;
	//  Arreter de Suivre le sujet
	case 'ResilierTopic':
		$f->resilier_sujet(intval($_GET['id_topic']));
		break;
	//  Ajouter aux favoris
	case 'AjouterFavoris':
		$f->ajouter_favoris(intval($_GET['id_topic']));
		break;
	//  Supprimer des favoris
	case 'SupprimerFavoris':
		$f->supprimer_favoris(intval($_GET['id_topic']));
		break;
	// SUPPRESSION POST
	case 'SupprimerPost':
		if (!isset($_GET['confirme']) || $_GET['confirme'] != 1){
			error404(722);break;
		}
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			$f->clean($_GET);
			$f->Get_Post();
			if (($droits->check($module,$f->post['id_forum'],'supprimer') && ($user['user_id'] == $f->post['user_id']))
				|| $droits->check($module,$f->post['id_forum'],'moderer') 
				|| $user['level']>9){
				$f->supprimer_post();
			}else{
				error404(722);	
			}
		}
		break;
	// SUPPRESSION TOPIC
	case 'SupprimerTopic':
		if (!isset($_GET['confirme']) || $_GET['confirme'] != 1){
			error404(722);break;
		}
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			$f->clean($_GET);
			$f->Get_Topic();
			if (($droits->check($module,$f->topic['id_forum'],'supprimer') && ($user['user_id'] == $f->topic['user_id']))
				|| $droits->check($module,$f->topic['id_forum'],'moderer') 
				|| $user['level']>9){
				$f->supprimer_topic();
			}else{
				error404(722);	
			}
		}
		break;
	// ENREGISTREMENT d'un nouveau POST
	case 'Enregistrer':
		$f->clean($_POST);
		//  /!\  SECURITE reportee dans la class pour limiter le nombre de tests.
		$f->enregistrer_post();
		break;
	// PREPARATION d'un nouveau TOPIC
	case 'NouveauTopic':
		$f->clean($_GET);	
		$f->Get_Forum();
		if ((!$droits->check($module,$f->id_forum,'ecrire') || $f->forum['status_forum'] == 0 ) 
			&& $user['level']<10){
			// Securite
			error404(720);
		}
		$tpl->titre_page = $f->forum['titre_forum'];
		$hidden='<input type="hidden" name="id_forum" value="'.intval($_GET['id_forum']).'" />
				<input type="hidden" name="mode" value="Enregistrer" />';
		$tpl->assign_block_vars('newtopic', array());
		// Champs de selection du type du sujet
		if ($droits->check($module,$f->id_forum,'moderer') || $user['level']>9){
			$tpl->assign_block_vars('newtopic.type_sujet', array());
		}
		include_once($root.'fonctions/fct_formulaires.php');
		$tpl->assign_vars(array(
			'SELECT_JOUR'	=> lister_chiffres(1,31,date('d')),
			'SELECT_MOIS'	=> lister_chiffres(1,12,date('m')),
			'SELECT_ANNEE'	=> lister_chiffres(date('Y'),(date('Y')+5),date('Y')),
		));
		break;
	// PREPARATION d'un nouveau  POST
	case 'NouveauPost':
		$f->clean($_GET);
		$f->Get_Topic();
		$tpl->titre_page = $f->topic['titre_topic'];
		if ((!$droits->check($module,$f->topic['id_forum'],'repondre')	|| $f->topic['status_topic'] == 0)
			&& $user['level']<10){
			error404(721);
		}
		
		$hidden='<input type="hidden" name="id_topic" value="'.$f->topic['id_topic'].'" />
				<input type="hidden" name="mode" value="Enregistrer" />';
		break;
	// ENREGISTREMENT de l'edition
	case 'EnregistrerEditerPost':
		$f->clean($_POST);
		$f->Get_Post();
		if (($droits->check($module,$f->post['id_forum'],'editer') && ($user['user_id'] == $f->post['user_id'])) 
			|| $droits->check($module,$f->post['id_forum'],'moderer') 
			|| $user['level'] > 9){
			$f->editer_post();
		}else{
			// securite
			error404(719);
		}
		break;
	// PREPARATION a l'edition d'un POST
	case 'EditerPost':
		$f->clean($_GET);
		$f->Get_Post();
		if (($droits->check($module,$f->post['id_forum'],'editer') && ($user['user_id'] == $f->post['user_id'])) 
		|| $droits->check($module,$f->post['id_forum'],'moderer') 
		|| $user['level'] > 9){
			$hidden='<input type="hidden" name="id_post" value="'.$f->id_post.'" />
					<input type="hidden" name="mode" value="EnregistrerEditerPost" />';
			$tpl->titre_page = $f->post['titre_topic'];
			if($f->id_post==$f->post['post_depart']){
				$tpl->assign_block_vars('newtopic', array());
				// Champs de selection du type du sujet
				if ($droits->check($module,$f->post['id_forum'],'moderer') || $user['level']>9){
					$tpl->assign_block_vars('newtopic.type_sujet', array());
				}
			}
			include_once($root.'fonctions/fct_formulaires.php');
			$select_d = ($f->post['fin_annonce']!='')? date('d',$f->post['fin_annonce']):date('d');
			$select_m = ($f->post['fin_annonce']!='')? date('m',$f->post['fin_annonce']):date('m');
			$select_Y = ($f->post['fin_annonce']!='')? date('Y',$f->post['fin_annonce']):date('Y');
			$tpl->assign_vars(array(
				'POST'	=> $f->post['text_post'],
				'TITRE'	=> $f->post['titre_topic'],
				'SELECTED_TYPE_TOPIC_'.$f->post['type_topic'] => 'selected="selected"',
				'SELECT_JOUR'	=> lister_chiffres(1,31,$select_d),
				'SELECT_MOIS'	=> lister_chiffres(1,12,$select_m),
				'SELECT_ANNEE'	=> lister_chiffres(date('Y'),(date('Y')+5),$select_Y),
			));
		}else{
			// securite
			error404(719);
		}
		break;
	default: header('location :'.$base_formate_url);
}
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

// Navlinks
$session->make_navlinks(array(
	ucfirst($module)		=> formate_url('',true),
	$lang['L_SAISIE_MSG']	=> formate_url('',true)
));


$tpl->assign_vars(array(
	'HIDDEN'		=>	$hidden,
));
?>