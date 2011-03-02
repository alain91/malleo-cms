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

class droits{

	// Tableau cotnenant la liste des bannis
	var $liste_bannis=array();
	// informations d'un utilisateur
	var $user;
	// Tableau mutli-dimensionnel contenant :
	// - la liste des modules (si précisé on passe au niveau inferieur)
	// - les fonctions et le droit associé
	var $rules;
	
	// Tableau temporaire contenant la liste des regles et des permissions
	var $regles;
	
	// Liste des groupes à afficher
	// Par defaut on affiche les invites, les membres et les admins
	var $liste_groupes = array(1,2,3);
	
	// Groupes auquel appartient un user
	var $user_groupes = array();
	
	function droits(){
		global $cache,$cf,$root;
		$cf->config['cache_duree_regles'] = 300;
		$cache->files_cache['listing_regles'] = array($root.'cache/data/droits_listing_regles','global $droits; return $droits->cache_lister_regles();',$cf->config['cache_duree_regles']);
	}
	
	//
	// Met en cache la liste des regles en place
	function cache_lister_regles(){
		global $c;
		$sql = 'SELECT r.id_regle, module,id_noeud,id_groupe,alias,nom_fonction,valeur
				FROM '.TABLE_DROITS_REGLES.' as r
				LEFT JOIN '.TABLE_DROITS_FONCTIONS.' as f
				ON (r.id_regle=f.id_regle)
				ORDER BY module ASC, id_noeud ASC, valeur DESC, id_groupe ASC, nom_fonction ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,52,__FILE__,__LINE__,$sql);
		$regles = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$regles[$row['module']][$row['id_noeud']][$row['nom_fonction']][$row['id_groupe']]['id_regle']	= $row['id_regle'];
			$regles[$row['module']][$row['id_noeud']][$row['nom_fonction']][$row['id_groupe']]['alias']		= $row['alias'];
			$regles[$row['module']][$row['id_noeud']][$row['nom_fonction']][$row['id_groupe']]['nom_fonction']= $row['nom_fonction'];
			$regles[$row['module']][$row['id_noeud']][$row['nom_fonction']][$row['id_groupe']]['valeur']		= ($row['valeur']!='')?$row['valeur']:'null';
		}
		return $regles;
	}
	
	
	//
	// Initialise des regles a l'installation d'un module
	function init_regles($module,$virtuel=null){
		global $root,$cache;
		$regles= array();		
		// Chargement des regles par defaut etablies
		// Si virtuel on charge les regles du module principal
		$file = $root.'plugins/modules/'.(($virtuel!=null)?$virtuel:$module).'/_admin_rules.php';
		if (file_exists($file)){
			require_once($file);
			// invites
			$this->add_regles($module,0,1,null,$regles[1]);
			// membres
			$this->add_regles($module,0,2,null,$regles[2]);
			//admins
			$this->add_regles($module,0,3,null,$regles[3]);
		}
	}
	
	//
	// Ajoute une regle
	function add_regles($module,$id_noeud=0,$id_groupe=null,$alias='null',$regles){
		global $c;
		$sql = 'INSERT INTO '.TABLE_DROITS_REGLES.' (module,id_noeud,id_groupe,alias)
				VALUES ("'.$module.'",'.$id_noeud.','.$id_groupe.',"'.$alias.'")';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,51,__FILE__,__LINE__,$sql);
		$this->id_regle = $c->sql_nextid($resultat);
		$this->add_fonctions($regles);
	}
	
	//
	// Ajoute des fonctions
	function add_fonctions($regles){
		global $c;
		$fonctions = '';
		foreach ($regles as $fonction=>$valeur){
			if ($fonctions!='')$fonctions.=',';
			$fonctions .= '('.$this->id_regle.',"'.$fonction.'",'.$valeur.')';
		}
		$sql = 'INSERT INTO '.TABLE_DROITS_FONCTIONS.' (id_regle,nom_fonction,valeur)
				VALUES '.$fonctions;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,52,__FILE__,__LINE__,$sql);
	}
	
	//
	// Supprime une regle et les fonctions associees
	function delete_regle($source,$parametre1,$parametre2=null){
		global $c;
		$sql ='';
		switch ($source){
			case 'module':
				$sql = 'DELETE r,f FROM '.TABLE_DROITS_REGLES.' AS r 
						LEFT JOIN '.TABLE_DROITS_FONCTIONS.' AS f
						ON (r.id_regle=f.id_regle)
						WHERE r.module="'.$parametre1.'"';
				break;
			case 'noeud':
				$sql = 'DELETE r,f FROM '.TABLE_DROITS_REGLES.' AS r 
						LEFT JOIN '.TABLE_DROITS_FONCTIONS.' AS f
						ON (r.id_regle=f.id_regle)
						WHERE r.id_noeud='.$parametre1.' AND r.module="'.$parametre2.'"';
				break;
			case 'groupe':
				$sql = 'DELETE r,f FROM '.TABLE_DROITS_REGLES.' AS r 
						LEFT JOIN '.TABLE_DROITS_FONCTIONS.' AS f
						ON (r.id_regle=f.id_regle)
						WHERE r.id_groupe='.$parametre1;
				break;
			case 'id_regle':
				$sql = 'DELETE r,f FROM '.TABLE_DROITS_REGLES.' AS r 
						LEFT JOIN '.TABLE_DROITS_FONCTIONS.' AS f
						ON (r.id_regle=f.id_regle)
						WHERE r.id_regle='.$parametre1;
				break;
		}
		if ($sql != ''){
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,54,__FILE__,__LINE__,$sql);	
		}
	}
		
	
	//
	// Liste les modules
	function lister_modules($handle,$module=null){
		global $c,$tpl,$cache;
		$sql = 'SELECT module, virtuel FROM '.TABLE_MODULES;
		if ($module!=null){
			if (is_array($module)){
				$module = implode(',',$module);
				$module = preg_replace('#([,]{0,1})([a-z0-9-_]{1,})([,]{0,1})#is','"\\2"\\3',$module);
				$sql .= ' WHERE module IN ('.$module.') ';
			}else{
				$sql .= ' WHERE module="'.$module.'"';
			}			
		}
		$sql .= ' ORDER BY module ASC';
	
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,52,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat))
		{
			$tpl->assign_block_vars($handle, array(
				'MODULE' => $row['module'],
				'VIRTUEL' => ($row['virtuel']!='')?'('.$row['virtuel'].')':''				
			));
			// Si le module n'existe pas on l'initialise
			if (!array_key_exists($row['module'],$this->regles)) $this->init_regles($row['module'],$row['virtuel']);
			
			// LISTING des noeuds
			$this->lister_noeuds($row['module'],$row['virtuel'],$handle);
		}
	}
	//
	// Liste les noeud d'un module
	function lister_noeuds($module,$virtuel,$handle){
		global $c,$tpl,$lang;
		$module_principal = ($virtuel!='')?$virtuel:$module;
		load_lang_mod($module_principal);
		$handle .= '.noeuds';
		foreach($this->regles[$module] as $noeud=>$fonctions)
		{
			// Les noeuds
			$tpl->assign_block_vars($handle, array());
			
			// Les Groupes
			$this->lister_groupes($handle.'.groupes');
			
			// Les fonctions
			$alias = ''; $i=0;
			foreach($fonctions as $fonction=>$groupes)
			{
				if ($alias=='' && $i==0) $alias = $groupes[key($groupes)]['alias'];
				$i++;
				
				$tpl->assign_block_vars($handle.'.fonctions', array(
					'ALIAS'		=> $alias,
					'FONCTION'	=> (array_key_exists('R_'.$module_principal.'_'.$fonction,$lang))?$lang['R_'.$module_principal.'_'.$fonction]:$fonction
				));
				$alias = '';
				
				// Lister les droits
				$this->lister_droits($handle.'.fonctions.droits',$module,$noeud,$fonction,$groupes);
			}
		}
	}
	
	//
	// Liste les groupes
	function lister_groupes($handle){
		global $tpl,$c;
		$groupes = (count($this->liste_groupes)==0)? '""':implode(',',$this->liste_groupes);
		$sql = 'SELECT group_id, titre 
				FROM '.TABLE_GROUPES.'
				WHERE group_id IN ('.$groupes.')
				ORDER BY group_id ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,52,__FILE__,__LINE__,$sql);
		$groupes = array();
		while($row = $c->sql_fetchrow($resultat))
		{
			$groupes[$row['group_id']] = $row['titre'];
		}
		foreach($this->liste_groupes as $id_groupe){
			$tpl->assign_block_vars($handle, array(
				'GROUPE' => $groupes[$id_groupe]
			));
		}
	}
	
	//
	// Liste les droits
	function lister_droits($handle,$module,$noeud,$fonction,$groupes){
		global $tpl;
		foreach($this->liste_groupes as $id_groupe)
		{
			// Ce groupe existe-t-il dans la liste des regles ?
			$valeur = (array_key_exists($id_groupe,$this->regles[$module][$noeud][$fonction]))? $this->regles[$module][$noeud][$fonction][$id_groupe]['valeur']:'';
			$tpl->assign_block_vars($handle, array(
				'LISTE'		=> $this->liste_choix($module.'|'.$noeud.'|'.$fonction.'|'.$id_groupe,$valeur)
			));
		}
	}
	
	//
	// Liste les choix dans un menu deroulant
	function liste_choix($nom,$valeur){
		global $lang;
		switch ($valeur){
			case '1':	$oui=' selected="selected"';	$non=$null='';	$class='oui';break;
			case '0':	$non=' selected="selected"';	$oui=$null='';	$class='non';break;
			case 'null':
			default :	$null=' selected="selected"';	$oui=$non='';	$class='null';break;
		}
		$liste =	"\n".'<select name="'.$nom.'" onChange="Color(this);" class="'.$class.'">'
					."\n".'<option value="null"	class="null"	'.$null.'>'.$lang['L_HERITE'].'</option>'
					."\n".'<option value="1"	class="oui"		'.$oui.'>'.$lang['L_OUI'].'</option>'
					."\n".'<option value="0"	class="non"		'.$non.'>'.$lang['L_NON'].'</option>'
					."\n".'</select>';
		return $liste;
	}
	
	//
	// Renvoie une chaine  de caracteres contenant les ID des groupes manuels
	// ex: 4,6,12
	function lister_groupes_manuels(){
		global $c;
		$sql = 'SELECT group_id
				FROM '.TABLE_GROUPES.' 
				WHERE group_id>3 AND type=1 
				ORDER BY group_id ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
		$liste_groupes = array();
		while ($row = $c->sql_fetchrow($resultat))
		{
			$liste_groupes[] = $row['group_id'];
		}
		return $liste_groupes;
	}
	//
	// Receptionne les regles modifiees
	function clean_regles_recues($vars){
		// Tableau contenant les regles temporaires
		$regles_tmp = array();
		foreach($vars as $key=>$val)
		{
			if ($key != 'enregistrer'){
				$elmts = explode('|',$key);
				$regles_tmp[$elmts[0]][$elmts[1]][$elmts[2]][$elmts[3]]['valeur'] = $val;
			}
		}
		$this->Compare_Regles($regles_tmp);
	}

	//
	// Compare les anciennes regles au nouvelles
	// Si de nouvelles regles sont apparues on les ajoute
	// Si des anciennes regles ont changees on les maj
	function Compare_Regles($temp){
		global $cache;
		foreach ($temp as $module=>$data_module){
			foreach ($data_module as $noeud=>$data_noeud){
				foreach ($data_noeud as $fonction=>$data_fonction){
					foreach ($data_fonction as $groupe=>$data_groupe){
						$new_val = $data_groupe['valeur'];

						// Aucun enregistrement n'existe pour ce groupe
						// On va créer des requetes de type INSERT
						if (!array_key_exists($groupe,$this->regles[$module][$noeud][$fonction])){
							$this->maj_regles('insert',$module,$noeud,$fonction,$groupe,$new_val);							
							
						//  Les valeurs ont change
						// On met a jour les enregistrements
						}elseif($new_val != $this->regles[$module][$noeud][$fonction][$groupe]['valeur']){
							$this->maj_regles('update',$module,$noeud,$fonction,$groupe,$new_val);		
						}
					}
				}
			}
		}
		$this->maj_regles_appliquer();
		$this->regles = $cache->appel_cache('listing_regles');
	}
	
	var $liste_requetes = array();
	
	//
	// Cree des fonction de maj de la table des regles et des fonctions
	function maj_regles($type,$module,$noeud,$fonction,$groupe,$valeur){
		global $c;
		switch($type){
			case 'insert':
				$this->liste_requetes[$module.'|'.$noeud.'|'.$groupe][$fonction] = $valeur;
				break;
			case 'update':
				$sql = 'UPDATE '.TABLE_DROITS_FONCTIONS.' as f 
						LEFT JOIN '.TABLE_DROITS_REGLES.' as r
							ON (f.id_regle=r.id_regle)
						SET valeur='.$valeur.'
						WHERE module="'.$module.'"
							AND id_noeud='.$noeud.'
							AND id_groupe='.$groupe.'
							AND nom_fonction="'.$fonction.'"';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,53,__FILE__,__LINE__,$sql);
				break;
		}	
	}
	
	//
	// Execute toutes les requetes de maj des regles
	function maj_regles_appliquer(){
		if (count($this->liste_requetes)>0){
			foreach($this->liste_requetes as $regle=>$fonctions){
				$elmts = explode('|',$regle);
				$this->add_regles($elmts[0],$elmts[1],$elmts[2],null,$fonctions);
			}		
		}	
	}
	
	function clean_pseudos($liste){
		if (count($liste)==0){
			return '""';
		}
		$liste_formatee = '';
		foreach ($liste as $pseudo){
			if ($liste_formatee!='')$liste_formatee.=',';
			$liste_formatee .= '\''.nettoyage_nom($pseudo).'\'';
		}
		return $liste_formatee;
	}
	
	//
	// Recherche utilisateurs
	function search_users($liste_users){
		global $c;
		//On recherche si ce user existe
		$sql = 'SELECT u.user_id, u.pseudo  
				FROM '.TABLE_USERS.' AS u 
				WHERE u.pseudo IN ('.$this->clean_pseudos($liste_users).')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
		$liste_users = array();
		while($row = $c->sql_fetchrow($resultat)){
			$liste_users[] = array(
							'user_id'=>$row['user_id'],
							'pseudo'=>$row['pseudo'],
							);
		}
		return $liste_users;
	}
	
	//
	// Recherche les id group
	// Si ils n'existent pas on les cree
	function search_id_group($user){
		global $c;
		$sql = 'SELECT gi.user_id,gi.group_id FROM '.TABLE_GROUPES_INDEX.' AS gi 
				LEFT JOIN '.TABLE_GROUPES.' AS g
					ON (gi.group_id=g.group_id) 
				WHERE type=0 AND gi.user_id='.$user['user_id'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
		if ($c->sql_numrows($resultat)==0){	
			return $this->create_groupe_individuel($user['pseudo'],$user['user_id']);
		}else{
			$row = $c->sql_fetchrow($resultat);
			return $row['group_id'];
		}
	}
	
	//
	// Creation d'un groupe indiviuel
	// Renvoie l'ID du groupe cree
	function create_groupe_individuel($pseudo,$user_id){
		global $c;
		$sql = 'INSERT INTO '.TABLE_GROUPES.' (titre, type, user_id) VALUES
				("'.$pseudo.'",0,'.$user_id.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
		$group_id = $c->sql_nextid($resultat);
		$sql = 'INSERT INTO '.TABLE_GROUPES_INDEX.' (group_id, user_id, accepte) VALUES
				('.$group_id.','.$user_id.',1)';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql);
		return $group_id;
	}

	//
	// Verifie le droit du user
	// si le noeud n'est pas definis on renvoie le droit par defaut equivalent
	// si le user est fondateur on dit toujours OUI
	function check($module,$noeud,$fonction)
	{
		global $user;
		// le Fondateur a toujours acces a tout
		if ($user['level'] == 10) return true;
 		if (!is_array($user) || !array_key_exists('rules',$user) || !array_key_exists($module,$user['rules'])){
			message_die(E_ERROR,55,__FILE__,__LINE__);
			exit;
		}else{
			// Regle etablie
			if (array_key_exists($noeud,$user['rules'][$module])){
				return $user['rules'][$module][$noeud][$fonction];
			}else{
				// Regle par defaut
				return $user['rules'][$module][0][$fonction];
			}
		}
	}
	
	//
	// Retourne les droits effectifs d'un utilisateur
	function droits_user(){
		global $c;
		$rules = array();
		switch($this->user['level']){
			case '10':case '9': $group_id = 3;break;
			case '8': case '7': case '6': case '5': case '4': case '3': case '2': $group_id = 2;break;
			case '1': default : $group_id = 1;break;
		}
		//chargement des regles par défaut
		$sql = 'SELECT module, id_noeud, nom_fonction, valeur 
				FROM '.TABLE_DROITS_REGLES.' as r
				LEFT JOIN '.TABLE_DROITS_FONCTIONS.' as f
					ON (r.id_regle=f.id_regle)
				WHERE id_groupe='.$group_id ;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
		while ($row = $c->sql_fetchrow($resultat)){
			$rules[$row['module']][$row['id_noeud']][$row['nom_fonction']] = $row['valeur'];
		}
		// Chargement des regles des groupes où l'utilisateur a été ajouté
		$sql = 'SELECT module, id_noeud, i.group_id, nom_fonction, valeur, g.titre  
				FROM '.TABLE_GROUPES_INDEX.' as i
				LEFT JOIN '.TABLE_GROUPES.' as g
					ON (i.group_id=g.group_id) 
				LEFT JOIN '.TABLE_DROITS_REGLES.' as r
					ON (i.group_id=r.id_groupe)
				LEFT JOIN '.TABLE_DROITS_FONCTIONS.' as f
					ON (r.id_regle=f.id_regle)
				WHERE i.user_id='.$this->user['user_id'].' 
				AND i.accepte=1
				AND type=1 
				ORDER BY g.ordre DESC'; // DESC car les groupes les  moins importants sont à passer en 1er. (Donc indice d'ordre fort)
				//die($sql);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql); 
		while ($row = $c->sql_fetchrow($resultat))
		{
			if ($row['valeur']!=null){
				$rules[$row['module']][$row['id_noeud']][$row['nom_fonction']] = $row['valeur'];
			}
			$this->user_groupes[$row['group_id']] = $row['titre'];
		}
		//Chargement des regles du user
		$sql = 'SELECT module, id_noeud, nom_fonction, valeur 
				FROM '.TABLE_GROUPES.' as g
				LEFT JOIN '.TABLE_GROUPES_INDEX.' as i
					ON (g.group_id=i.group_id) 
				RIGHT JOIN '.TABLE_DROITS_REGLES.' as r
					ON (i.group_id=r.id_groupe)
				LEFT JOIN '.TABLE_DROITS_FONCTIONS.' as f
					ON (r.id_regle=f.id_regle)
				WHERE i.user_id='.$this->user['user_id'].' 
				AND type=0';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql); 
		while ($row = $c->sql_fetchrow($resultat))
		{
			if ($row['valeur']!=null){
				$rules[$row['module']][$row['id_noeud']][$row['nom_fonction']] = $row['valeur'];
			}
		}
		return $rules;
	}
	
	function charge_droits_user($user,$force=false){
		global $root,$cache;
		$this->user = $user;
		// on stocke en cache les infos de session pendant 15 minutes	 ( 900)
		$tps = ($force==true)?0:900;
		return $cache->cache_donnees($root.'cache/sessions/rules_'.$this->user['user_id'].'.php', 'global $droits; return $droits->droits_user();', $tps);
	}
	//
	// Cree une liste des bannis
	function creer_liste_bannis(){
		global $c;
		$sql = 'SELECT id_ban, type_ban, pattern_ban, debut_ban, fin_ban, raison_ban 
				FROM '.TABLE_BANNIS.' ORDER BY fin_ban DESC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
		$liste = array();
		while ($row = $c->sql_fetchrow($resultat))
		{
			if ($row['fin_ban']<time() && $row['fin_ban']!=0){
				$this->delete_ban($row['id_ban']);
			}else{
				$liste[$row['type_ban']][$row['pattern_ban']] = $row;
			}
		}
		return $liste;
	}
	
	//
	// Supprime un enregistrement des bannis
	function delete_ban($id_ban){
		global $c,$cache;
		$sql = 'DELETE FROM '.TABLE_BANNIS.' WHERE id_ban='.$id_ban;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
		$cache->appel_cache('listing_bannis',true);
	}
	
	//
	// Met a jour la date de fin d'un ban
	function update_date_fin_ban($id_ban,$date_fin){
		global $c;
		$sql = 'UPDATE '.TABLE_BANNIS.' SET fin_ban='.$date_fin.' WHERE id_ban='.$id_ban;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
	}
	//
	// Ajoute une ip a la liste des ip bannies
	function ajoute_ip_ban($ip,$time,$motif='null'){
		global $c;
		if ($motif!='null')$motif='"'.$motif.'"';
		$sql = 'INSERT INTO '.TABLE_BANNIS.' (type_ban, pattern_ban, debut_ban, fin_ban, raison_ban) VALUES
				(2,"'.$ip.'",'.time().','.$time.','.$motif.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
	}
	//
	// Ajoute un pseudo a la liste des ip bannies
	function ajoute_pseudo_ban($pseudo,$time,$motif='null'){
		global $c;
		if ($motif!='null')$motif='"'.$motif.'"';
		$sql = 'INSERT INTO '.TABLE_BANNIS.' (type_ban, pattern_ban, debut_ban, fin_ban, raison_ban) VALUES
				(0,"'.$pseudo.'",'.time().','.$time.','.$motif.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,48,__FILE__,__LINE__,$sql);
	}
	//
	// Bannit une IP jusqu'a la date timestamp passee en parametre
	// ip = type 2
	function ban_ip($ip,$time,$motif='null'){
		global $cache;
		$this->liste_bannis = $cache->appel_cache('listing_bannis');
		// si l'IP est deja bannie on conserve la date la plus longue
		if (array_key_exists(2,$this->liste_bannis) && array_key_exists($ip,$this->liste_bannis[2]) && ($this->liste_bannis[2][$ip]['fin_ban']<$time || $time==0)){
			$this->update_date_fin_ban($this->liste_bannis[2][$ip]['id_ban'],$time);
		}else{
			$this->ajoute_ip_ban($ip,$time,$motif);
		}
		$this->liste_bannis = $cache->appel_cache('listing_bannis',true);
	}
	
	//
	// Bannit un Pseudo jusqu'a la date timestamp passee en parametre
	// pseudo= type 0
	function ban_pseudo($pseudo,$time,$motif='null'){
		global $cache;
		$this->liste_bannis = $cache->appel_cache('listing_bannis');
		// si le pseudo est deja bannie on conserve la date la plus longue
		if (array_key_exists(0,$this->liste_bannis) && array_key_exists($pseudo,$this->liste_bannis[0]) && ($this->liste_bannis[0][$pseudo]['fin_ban']<$time || $time==0)){
			$this->update_date_fin_ban($this->liste_bannis[0][$pseudo]['id_ban'],$time);
		}else{
			$this->ajoute_pseudo_ban($pseudo,$time,$motif);
		}
		$this->liste_bannis = $cache->appel_cache('listing_bannis',true);
	}
	
	//
	// Charge la liste des IP bannies
	function Charge_bannis(){
		global $cache;
		$this->liste_bannis = $cache->appel_cache('listing_bannis');
	}
}


?>