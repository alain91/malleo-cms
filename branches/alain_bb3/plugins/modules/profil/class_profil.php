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
class profil{

	var $cat = array();
	var $modeles = array();
	var $saisie = array();
	var $user_id;
	var $id_cat;
	
	function clean($vars)
	{
		foreach($vars as $key=>$val)
		{
			switch($key)
			{
				// Entier
				case 'user_id':		
				case 'id_cat':		$this->$key = intval($val);break;
				// Chaine
				case 'texte':		$this->saisie[$key] = protection_chaine($val); break;
			}
		}
	}
	
	function lecture_modeles(){
		global $c;
		$sql = 'SELECT m.id_cat,m.titre_cat,m.modele 
			FROM '.TABLE_PROFIL_MODELES.' as m 
			ORDER BY m.ordre ASC';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			$this->modeles[$row['id_cat']]['titre_cat']= $row['titre_cat'];
			$this->modeles[$row['id_cat']]['modele']= $row['modele'];
		}
	}

	function lecture_profil(){
		global $c;
		$this->lecture_modeles();

		$sql = 'SELECT p.id_cat, p.texte, u.user_id,u.pseudo   
			FROM '.TABLE_PROFIL_USERS.' as p
			LEFT JOIN '.TABLE_USERS.' as u
				ON (p.user_id=u.user_id)
			WHERE u.user_id='.$this->user_id;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			$this->cat[$row['id_cat']]['texte']= $row['texte'];
			$this->cat[$row['id_cat']]['user_id']= $row['user_id'];
			$this->cat[$row['id_cat']]['pseudo']= $row['pseudo'];
			if (array_key_exists($row['id_cat'],$this->modeles)){
				$this->cat[$row['id_cat']]['titre_cat']= $this->modeles[$row['id_cat']]['titre_cat'];
				$this->cat[$row['id_cat']]['modele']= $this->modeles[$row['id_cat']]['modele'];			
			}
		}
		// Nouveaux modeles a creer
		$nouveaux_modeles = array_diff_key($this->modeles,$this->cat);
		if (sizeof($nouveaux_modeles)>0){
			$this->update_profil($nouveaux_modeles);
			$this->lecture_profil();
		}
	}
	
	function update_profil($modeles){
		global $c;
		$valeurs = '';
		if (!is_array($modeles) || sizeof($modeles) == 0) return false;
		foreach($modeles AS $id_cat=>$val)
		{
			if ($valeurs!='')$valeurs .= ',';
			$valeurs .= ' ('.$id_cat.','.$this->user_id.',\''.$val['modele'].'\')';
		}
		$sql = 'INSERT INTO '.TABLE_PROFIL_USERS.' (id_cat,user_id,texte) VALUES '.$valeurs;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);	
		$this->lecture_profil($this->user_id);
	}

	function update_categorie(){
		global $c;
		$sql = 'UPDATE '.TABLE_PROFIL_USERS.' 
				SET texte=\''.str_replace("\'","''",$this->saisie['texte']).'\' 
				WHERE user_id='.$this->user_id.' AND id_cat='.$this->id_cat;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
		affiche_message('profil','L_CATEGORIE_ENREGISTREE',formate_url('user_id='.$this->user_id.'&mode=visu',true));
	}
	
	function update_element($element){
		global $c,$cache;
		$sql = 'UPDATE '.TABLE_USERS.' SET 
				'.$element.'
				WHERE user_id='.$this->user_id;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql);
		$cache->appel_cache('infos_user',true);
		affiche_message('profil','L_MODIFICATION_ENREGISTREE',formate_url('user_id='.$this->user_id.'&mode=visu',true));
	}

	function reactivation_email($mail,$pseudo)
	{
		global $root,$c,$cf,$lang;
		require($root.'fonctions/fct_maths.php');
		$mail = str_replace("\'","''",$mail);
		$clef = generate_key(30);
		// On envoit le mail de verification
		require_once($root.'class/class_mail.php');
		$email = new mail($cf->config);
		
		$verifier = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'register.php?etape=3&email='.$mail.'&clef='.$clef;
		
		$email->From     = $cf->config['mail_from'];
		$email->FromName = $cf->config['mail_fromname'];
		$email->Subject = sprintf($lang['L_MAIL_REACTIVATION_SUJET'],$cf->config['nom_site'] );
		$email->AltBody = sprintf($lang['L_MAIL_REACTIVATION_BODY_TEXTE'],$pseudo,$verifier,  $cf->config['mail_fromname']);
		$email->Body    = sprintf($lang['L_MAIL_REACTIVATION_BODY_HTML'],$pseudo,$verifier,$verifier,  $cf->config['mail_fromname']);
		$email->AddAddress($mail,$pseudo);
		$email->Send();
		// On met a jour le compte
		$this->update_element('actif=0,email=\''.$mail.'\',level=1,clef=\''.$clef.'\'');
	}
}

?>