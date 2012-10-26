<?php
/**
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2012, Alain GANDON All Rights Reserved
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
class forum
{
	var $liste_forums;
	var $id_topic;
	var $id_post;
	var $post;
	var $topic;
	var $forum;
	var $type_topic=1;
	var $fin_annonce='null';
	var $liste_topics_recents = '';
	var $tags;

	/**
	* recupere le tableau passe en parametre
	* nettoie/affecte les variables si fournies
    */
	function clean($vars)
	{
		foreach($vars as $key=>$val){
			switch($key){
				case 'id_forum':
				case 'id_topic':
				case 'type_topic':
				case 'id_post':	$this->$key = $this->saisie[$key] = intval($val); break;
				case 'post':
				case 'titre': $this->saisie[$key] = protection_chaine($val); break;
				case 'jour': $this->fin_annonce = mktime(0,0,1,intval($vars['mois']),intval($vars['jour']),intval($vars['annee'])); break;
			}
		}
	}

    protected function _tronquer($string)
    {
        $str=strlen($string<=75)?$string:substr($string,0,75).'...';
        return $str;
    }

    protected function _date($date)
    {
        global $user;
        return formate_date($date,'d m Y H i','FORMAT_DATE',$user['fuseau']);
    }

    protected function _plien($id_topic,$id_post)
    {
        return formate_url('mode=topic&id_topic='.intval($id_topic).'&id_post='.intval($id_post).'#'.intval($id_post),true);
    }

    protected function _flien($id_forum)
    {
        return formate_url('mode=forum&id_forum='.intval($id_forum),true);
    }

    protected function _ficone($icone)
    {
        return 'data/icones_forum/'.$icone;
    }

    protected function _escape($str)
    {
        return str_replace("\'","''",trim($str));
    }

    /**
	* Affiche les forums de la categorie saisie en parametre
	* input : int -> $id_cat; var -> $handle (le nom du noeud de la class $tpl)
	* output: true+affichage/error
    */
	function afficher_forums($id_cat,$handle)
	{
		global $tpl,$cf,$cache,$droits,$module,$user;
		if (!is_array($this->liste_forums)) $this->liste_forums = $cache->appel_cache('listing_forums');
		if (array_key_exists($id_cat,$this->liste_forums)
            AND array_key_exists(0,$this->liste_forums[$id_cat])
            AND sizeof($this->liste_forums[$id_cat][0])>0)
		{
            $class='row2';
			foreach ($this->liste_forums[$id_cat][0] as $key=>$val){
				if ($droits->check($module,$val['id_forum'],'voir')){
                    $class=($class=='row2')?'row1':'row2';
                    $liste_forums=$this->cherche_tous_fils($val['id_forum']);
                    $fpost=$this->afficher_dernier_post($liste_forums);
                    $cumuls=$this->recupere_compteurs_cumules($liste_forums);
					$tpl->assign_block_vars($handle, array(
                        'CLASS' => $class,
						'LIEN' => $this->_flien($val['id_forum']),
						'ICONE' => $this->_ficone($val['icone_forum']),
						'TITRE' => $val['titre_forum'],
						'NBRE_TOPICS' => $cumuls['nbre_topics'],
						'NBRE_REPONSES'	=> $cumuls['nbre_reponses'],
                        'P_TEXT' => empty($fpost['id_post'])?'':$this->_tronquer($fpost['text_post']),
                        'P_DATE' => empty($fpost['id_post'])?'':$this->_date($fpost['date_post']),
                        'P_USER' => empty($fpost['id_post'])?'':$fpost['pseudo'],
                        'P_LIEN' => empty($fpost['id_post'])?'':$this->_plien($fpost['id_topic'],$fpost['id_post']),
					));
				}
			}
		}
		return true;
	}

    /**
    * affiche les sous-forums
    *
    */
	function affiche_sous_forums($id_forum, $handle_actif, $handle)
	{
		global $c,$tpl,$cf,$droits,$module,$user;
		$sql = 'SELECT f.id_forum, f.titre_forum, f.icone_forum
				FROM '.TABLE_FORUM_FORUMS.' as f
				WHERE f.parent_forum='.intval($id_forum).'
                ORDER BY f.ordre ASC,f.id_forum ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,704,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)>0){
			$tpl->assign_block_vars($handle_actif, array());
            $class='row2';
			while($row = $c->sql_fetchrow($resultat)){
				if ($droits->check($module,$row['id_forum'],'voir')){
                	$class=($class=='row2')?'row1':'row2';
                    $liste_forums=$this->cherche_tous_fils($row['id_forum']);
                    $fpost=$this->afficher_dernier_post($liste_forums);
                    $cumuls=$this->recupere_compteurs_cumules($liste_forums);
					$tpl->assign_block_vars($handle_actif.'.'.$handle, array(
                        'CLASS' => $class,
						'LIEN' => $this->_flien($row['id_forum']),
						'ICONE' => $this->_ficone($row['icone_forum']),
						'TITRE'	=> $row['titre_forum'],
						'NBRE_TOPICS' => $cumuls['nbre_topics'],
						'NBRE_REPONSES'	=> $cumuls['nbre_reponses'],
                        'P_TEXT' => empty($row['id_post'])?'':$this->_tronquer($fpost['text_post']),
                        'P_DATE' => empty($row['id_post'])?'':$this->_date($fpost['date_post']),
                        'P_USER' => empty($row['id_post'])?'':$fpost['pseudo'],
						'P_LIEN' => empty($row['id_post'])?'':$this->_plien($fpost['id_topic'],$fpost['id_post']),
					));
				}
			}
		}
	}

	/**
	* affiche les topics demandes
	* les topics apparaitront dans {LISTE_TOPICS}
    */
	function affiche_liste_topics($start,$nbre_topics,$id_forum)
	{
		global $c,$cf,$tpl,$post,$lang,$img,$root,$droits,$module,$user;
		switch($user['forum_vue']){
			case '1':$choix_tpl = 'liste_topics_complet.html';break;
			case '2':$choix_tpl = 'liste_topics_classique.html';break;
		}
		$tpl->set_filenames(array('liste_topics'=>$root.'plugins/modules/forum/html/'.$choix_tpl));
		$sql = 'SELECT t.*, pfin.text_post, pfin.date_post,
				tnl.id_topic AS topic_lu, ts.id_topic AS topic_abonne, ufin.pseudo
			FROM '.TABLE_FORUM_TOPICS.' as t
			LEFT JOIN '.TABLE_FORUM_POSTS.' as pfin ON (t.post_fin=pfin.id_post)
			LEFT JOIN '.TABLE_USERS.' as ufin ON (pfin.user_id=ufin.user_id)
			LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as tnl ON (t.id_topic=tnl.id_topic AND tnl.user_id='.$user['user_id'].')
			LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
			WHERE t.id_forum='.intval($id_forum).' AND type_topic<=1
			ORDER BY date_topic DESC
			LIMIT '.$start.','.$nbre_topics;

		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==0){
			$tpl->assign_block_vars('aucun_topic', array());
		}else{
			$class='row2';
			while($row = $c->sql_fetchrow($resultat)){
				if ($droits->check($module,$row['id_forum'],'voir')
					AND $droits->check($module,$row['id_forum'],'lire'))
                {
					$class=($class=='row2')?'row1':'row2';
					$tpl->assign_block_vars('items', array(
						'CLASS'	=> $class,
                        'TITRE_TOPIC' => $row['titre_topic'],
						'LIEN_TOPIC' => $this->_plien($row['id_topic'],$row['post_depart']),
						'TOPIC_LU' => ($row['topic_lu']!=$row['id_topic'])? $img['forum_sujet_lu']:$img['forum_sujet_non_lu'],
						'TOPIC_LU_LIBELLE' => ($row['topic_lu']!=$row['id_topic'])? $lang['L_TOPIC_DEJA_LU']:$lang['L_TOPIC_JAMAIS_LU'],
						'TOPIC_ABONNE' => ($row['topic_abonne']!=$row['id_topic'])? $img['forum_sujet_non_abonne']:$img['forum_sujet_abonne'],
						'TOPIC_ABONNE_LIBELLE' => ($row['topic_abonne']!=$row['id_topic'])? $lang['L_TOPIC_NON_ABONNE']:$lang['L_TOPIC_ABONNE'],
						'REPONSES' => $row['reponses_topic'],
						'LECTURES' => $row['lectures_topic'],
                        'P_TEXT' => empty($row['id_post'])?'':$this->_tronquer($row['text_post']),
                        'P_DATE' => empty($row['id_post'])?'':$this->_date($row['date_post']),
                        'P_USER' => empty($row['id_post'])?'':$row['pseudo'],
						'P_LIEN' => empty($row['id_post'])?'':$this->_plien($row['id_topic'],$row['id_post']),
					));
				}
				// Meta description
				$tpl->meta_description .= ' '.$post->bbcode2html($row['titre_topic']);
			}
		}
		$tpl->assign_var_from_handle('LISTE_TOPICS','liste_topics');
	}

	/**
	* affiche les topics speciaux : annonces ou postits
	*
    */
	function affiche_liste_generic($id_forum,$type,$template,$varliste,$vartemplate)
	{
		global $c,$cf,$tpl,$post,$lang,$img,$root,$droits,$module,$user;
		$sql = 'SELECT t.*i, pfin.text_post, pfin.date_post,
				tnl.id_topic AS topic_lu, ts.id_topic AS topic_abonne, ufin.pseudo
			FROM '.TABLE_FORUM_TOPICS.' as t
			LEFT JOIN '.TABLE_FORUM_POSTS.' as pfin ON (t.post_fin=pfin.id_post)
			LEFT JOIN '.TABLE_USERS.' as ufin ON (pfin.user_id=ufin.user_id)
			LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' as tnl ON (t.id_topic=tnl.id_topic AND tnl.user_id='.$user['user_id'].')
			LEFT JOIN '.TABLE_FORUM_TOPICS_SUIVIS.' as ts ON (t.id_topic=ts.id_topic AND ts.user_id='.$user['user_id'].')
			WHERE t.id_forum='.intval($id_forum).' AND type_topic='.intval($type).'
			ORDER BY date_topic DESC';

		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)>0){
			$tpl->set_filenames(array($template=>$root.'plugins/modules/forum/html/'.$template.'.html'));
			$class='row2';
            $tmp=explode('.',$varliste);
            $tpl->assign_block_vars($tmp[0], array());
			while($row = $c->sql_fetchrow($resultat)){
				if ($droits->check($module,$row['id_forum'],'voir')
					AND $droits->check($module,$row['id_forum'],'lire'))
                {
					$class=($class=='row2')?'row1':'row2';
					$tpl->assign_block_vars($varliste, array(
						'CLASS'	=> $class,
                        'TITRE_TOPIC' => $row['titre_topic'],
						'LIEN_TOPIC' => $this->_plien($row['id_topic'],$row['post_depart']),
						'TOPIC_LU' => ($row['topic_lu']!=$row['id_topic'])? $img['forum_sujet_lu']:$img['forum_sujet_non_lu'],
						'TOPIC_LU_LIBELLE' => ($row['topic_lu']!=$row['id_topic'])? $lang['L_TOPIC_DEJA_LU']:$lang['L_TOPIC_JAMAIS_LU'],
						'TOPIC_ABONNE' => ($row['topic_abonne']!=$row['id_topic'])? $img['forum_sujet_non_abonne']:$img['forum_sujet_abonne'],
						'TOPIC_ABONNE_LIBELLE' => ($row['topic_abonne']!=$row['id_topic'])? $lang['L_TOPIC_NON_ABONNE']:$lang['L_TOPIC_ABONNE'],
						'REPONSES' => $row['reponses_topic'],
						'LECTURES' => $row['lectures_topic'],
                        'P_TEXT' => empty($row['id_post'])?'':$this->_tronquer($row['text_post']),
                        'P_DATE' => empty($row['id_post'])?'':$this->_date($row['date_post']),
                        'P_USER' => empty($row['id_post'])?'':$row['pseudo'],
						'P_LIEN' => empty($row['id_post'])?'':$this->_plien($row['id_topic'],$row['id_post']),
					));
				}
			}
			$tpl->assign_var_from_handle($vartemplate,$template);
		}
	}

	/**
    * Afficher le dernier post d'un forum et de ses fils
    *
    */
	function afficher_dernier_post($liste_forums)
    {
        global $c;
        $sql = 'SELECT p.*, u.pseudo
                FROM '.TABLE_FORUM_TOPICS.' as t
                LEFT JOIN '.TABLE_FORUM_POSTS.' as p ON (t.post_fin=p.id_topic)
                LEFT JOIN '.TABLE_USERS.' as u ON (p.user_id=u.user_id)
                WHERE t.id_forum IN ('.implode(',',$liste_forums).')
                ORDER BY p.date_post DESC
                LIMIT 1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
        return $row;
	}

    /**
    * cherche les fils d'un forum
    *
    */
	function cherche_tous_fils($forums,$liste_forums=array())
	{
		global $c;
        if (is_array($forums)){
            $clause=' IN ('.implode(',',$forums).')';
        }else{
            $clause='='.intval($forums);
            if (empty($liste_forums)) $liste_forums=array($forums);
        }
		$sql = 'SELECT f.id_forum
				FROM '.TABLE_FORUM_FORUMS.' as f
				WHERE f.parent_forum '.$clause;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,704,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)>0){
            $rows = $c->sql_fetchrowset($resultat);
            $items=array();
            foreach($rows as $row) $items[]=$row['id_forum'];
            $liste_forums=array_merge($liste_forums,$items);
            return $this->cherche_tous_fils($items,$liste_forums);
		}
        return $liste_forums;
	}

    /**
    * recupere les compteurs cumules des sous-forums d'un forum
    *
    */
	function recupere_compteurs_cumules($liste_forums)
	{
		global $c;
		$sql = 'SELECT count(t.id_topic) as nbre_topics,sum(t.reponses_topic) as nbre_reponses
				FROM '.TABLE_FORUM_TOPICS.' as t
				WHERE t.id_forum IN ('.implode(',',$liste_forums).')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,704,__FILE__,__LINE__,$sql);
        $row = $c->sql_fetchrow($resultat);
        return $row;
	}

	/**
	* Récupère en cache la liste des tags
    *
    */
	function get_tags()
    {
		if (is_array($this->tags)){
			return $this->tags;
		}else{
			global $cache;
			return $this->tags = $cache->appel_cache('listing_tags');
		}
	}

	/**
	* Permet d'extraire la liste des tags disponibles sur le site
    *
    */
	function cache_liste_tags()
    {
		global $c;
		$sql = 'SELECT id_stick,mot,type,image,couleur,alternatif
				FROM '.TABLE_FORUM_TAG;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql);
		$liste_tags = array();
		while ($row = $c->sql_fetchrow($resultat)){
			$liste_tags[$row['mot']] = $row;
		}
		return $liste_tags;
	}

	/**
	* fonction permettant de supprimer accents/majuscules
    *
    */
	function supprimer_accents_majuscules($chaine){
		global $cf;
		return strtolower(supprimer_accents(utf8_decode(html_entity_decode($chaine,ENT_QUOTES,$cf->config['charset']))));
	}

	/**
	* Formate le titre avec des tags
    *
    */
	function formate_titre_sujet($titre)
    {
		// Pas de tags définis? pas la peine d'en chercher dans les titres.
		if (sizeof($this->get_tags()) == 0) return $titre;
		preg_match_all("/\[(.*?)\]/", $titre, $sortie, PREG_PATTERN_ORDER);
		if(isset($sortie[1])){
			foreach($sortie[1] AS $id=>$match){
				foreach($this->tags AS $tag=>$t){
					$_tag = $this->supprimer_accents_majuscules($tag);
					$_match = $this->supprimer_accents_majuscules($match);
					// Remplacement complet [Reglé]
					if ($_tag == $_match){
						$alt = (!empty($t['alternatif']))?$t['alternatif']:$match;
						$remplace=($t['type']=='1')?'<img src="'.$t['image'].'" alt="'.$alt.'" title="'.$alt.'" />':'<span style="color:'.$t['couleur'].';">'.$alt.'</span>';
						$titre = preg_replace("/\[".$match."\]/si", $remplace, $titre);

					// Remplacement partiel [En cours par ? ]
					}elseif(preg_match('/'.$_tag.'/',$_match)){
						$alt = (!empty($t['alternatif']))?sprintf($t['alternatif'],strtoupper(preg_replace('/'.$_tag.'/','',$_match))):$match;
						$remplace=($t['type']=='1')?'<img src="'.$t['image'].'" alt="'.$alt.'" title="'.$alt.'" />':'<span style="color:'.$t['couleur'].';">'.$alt.'</span>';
						$titre = preg_replace("/\[".$match."\]/si", $remplace, $titre);
					}
				}
			}
		}
		return $titre;
	}

	/**
	*
    *
    */
	function affiche_select_liste_forums()
	{
		global $tpl,$cache;
		$select = '';
		if (!is_array($this->liste_forums)) $this->liste_forums = $cache->appel_cache('listing_forums');
		foreach ($this->liste_forums as $id_cat=>$valeur){
			if ($id_cat!='index') $select .= $this->select_liste_forums($id_cat,0);
		}
		return $select;
	}

	/**
	* Menu deroulant permettant de selectionner un forum
    *
    */
	function select_liste_forums($id_cat,$parent=0,$select='',$liste_cat = array(),$pre='')
	{
		if (array_key_exists($id_cat,$this->liste_forums)
            AND array_key_exists($parent,$this->liste_forums[$id_cat]))
        {
			foreach ($this->liste_forums[$id_cat][$parent] as $key=>$val){
				if (!in_array($id_cat,$liste_cat)){
					$select .= '<optgroup label="'.$val['titre_cat'].'"></optgroup>';
					$liste_cat[]=$id_cat;
				}
				$select .= '<option value="'.$val['id_forum'].'">'.$pre.'_ '.$val['titre_forum'].'</option>';
				if (array_key_exists($val['id_forum'],$this->liste_forums[$id_cat])){
					$select = $this->select_liste_forums($id_cat,$val['id_forum'],$select,$liste_cat,$pre.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|');
				}
			}
		}
		return $select;
	}

	/**
	* Mise a jour des caches modules
    *
    */
	function maj_liste_forums()
    {
		global $c,$cache,$root,$f;
		$sql = 'SELECT module FROM '.TABLE_MODULES.' WHERE module="forum" OR virtuel="forum"';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
			$cache->cache_donnees($root.'cache/data/liste_forums_'.$row['module'].'.php', 'global $f; return $f->cache_liste_forums(\''.$row['module'].'\');', 0, true);
		}
	}

	/**
	* Deplace le topic dans le forum selectionne
    *
    */
	function deplacer_topic()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET id_forum='.$this->id_forum.'
                WHERE id_topic='.$this->id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
		$this->maj_forums();
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_DEPLACE',formate_url('mode=topic&id_topic='.$this->id_topic,true));
	}

	/**
	* Met a jour l'identifiant du topic des posts passée en parametre
	* $liste_posts sous la forme 1,25,69 ou ""
    *
    */
	function update_id_topic_from_id_post($liste_posts)
    {
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_POSTS.' SET id_topic='.$this->id_topic.'
                WHERE id_post IN ('.$liste_posts.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
	}

	/**
	* Met a jour l'identifiant du topic des posts passée en parametre
	* $liste_topics sous la forme 1,25,69 ou ""
    *
    */
	function update_id_topic_from_id_topic($liste_topics)
    {
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_POSTS.' SET id_topic='.$this->id_topic.'
                WHERE id_topic IN ('.$liste_topics.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
	}

	/**
	* Recherche tous les identifiants de post qui sont du meme topic
	* que l'id_post passe en parametre.
	* Il renvoit un tableau des posts posterieur au referent
    *
    */
	function recherche_liste_posts($id_post)
    {
		global $c;
		$sql = 'SELECT id_post,date_post FROM '.TABLE_FORUM_POSTS.'
				WHERE id_topic=(
					SELECT id_topic FROM '.TABLE_FORUM_POSTS.'
					WHERE id_post='.$id_post.'
					LIMIT 1)
				AND id_post>='.$id_post;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
		$liste_topics = array();
		while($row = $c->sql_fetchrow($resultat)){
			$liste_topics[] = $row['id_post'];
		}
		return $liste_topics;
	}

    /**
    *
    *
    */
	function initialiser_forum_demo()
    {
		global $c,$lang,$module,$cache;

		// On vérifie tout de même que le forum est bien vide de catégories.
		$sql = 'SELECT titre_cat FROM '.TABLE_FORUM_CATS.' WHERE module=\''.$module.'\'';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat) == 0){
			$sql = 'INSERT INTO '.TABLE_FORUM_CATS.' (titre_cat, desc_cat, module)
					VALUES (\''.$this->_escape($lang['L_DEMO_CAT_TITRE']).'\',
							\''.$this->_escape($lang['L_DEMO_CAT_DESCRIPTION']).'\',
							\''.$module.'\')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql);

			$id_cat = $c->sql_nextid();
			$sql = 'INSERT INTO '.TABLE_FORUM_FORUMS.' (titre_forum, parent_forum, icone_forum, id_cat)
					VALUES (\''.$this->_escape($lang['L_DEMO_FORUM_TITRE']).'\',
							\'0\',
							\'Chat.png\',
							\''.$id_cat.'\')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,708,__FILE__,__LINE__,$sql);
			// Ajout de regles par defaut pour ce forum
			$id_forum = $c->sql_nextid();
			$this->droits_ajoute_noeud($id_forum,$module,$this->_escape($lang['L_DEMO_FORUM_TITRE']));
		}
		// Purge du cache (suppression du cache du forum mais aussi des regles d'accès de tous les utilisateurs.)
		$cache->purger_cache();
	}

	/**
	* Extrait les informations sur les forums afin de les stocker en cache
	* en parallele un index est créé afin d'associer facilement les forums et sous forums d'une catégorie.
	* input : /
	* output : array->$liste_forums
    *
    */
	function cache_liste_forums($module)
	{
		global $c;
		$sql = 'SELECT c.id_cat, c.titre_cat, f.*
				FROM  '.TABLE_FORUM_FORUMS.' as f
				LEFT JOIN '.TABLE_FORUM_CATS.' as c ON (f.id_cat=c.id_cat)
				WHERE c.module="'.$module.'"
				ORDER BY c.ordre ASC, f.ordre ASC, c.id_cat ASC, f.id_forum ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
		$liste_forums=array();
		while($row = $c->sql_fetchrow($resultat)){
			$liste_forums[$row['id_cat']][$row['parent_forum']][] = $row;
			$liste_forums['index'][$row['id_cat']][] = $row['id_forum'];
		}
		return $liste_forums;
	}

	/**
	* Incrémente le compteur de lectures de topics
    *
    */
	function incremente_lecture_topic()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET lectures_topic=lectures_topic+1 WHERE id_topic='.$this->id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,700,__FILE__,__LINE__,$sql);
	}

	/**
	* Suppression d'un post
    *
    */
	function supprimer_post()
	{
		global $c;
		$sql = 'SELECT id_topic,user_id FROM '.TABLE_FORUM_POSTS.' WHERE id_post='.$this->saisie['id_post'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		// On met à jour le nbre de messages de l'utilisateur
		$this->update_nbre_messages($row['user_id'],'-');
		$this->id_topic = $row['id_topic'];
		$sql = 'DELETE FROM '.TABLE_FORUM_POSTS.' WHERE id_post='.$this->saisie['id_post'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$this->maj_topic();
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_POST_SUPPRIME',formate_url('mode=topic&id_topic='.$this->id_topic,true));
	}
	/**
	* Supprime topic
    *
    */
	function supprimer_topic()
	{
		global $c;
		$sql = 'DELETE FROM '.TABLE_FORUM_POSTS.' WHERE id_topic='.$this->saisie['id_topic'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS.' WHERE id_topic='.$this->saisie['id_topic'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_NONLUS.' WHERE id_topic='.$this->saisie['id_topic'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_SUIVIS.' WHERE id_topic='.$this->saisie['id_topic'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_FAVORIS.' WHERE id_topic='.$this->saisie['id_topic'];
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,714,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_SUPPRIME',formate_url('',true));
	}
    /**
    *
    *
    */
	function verrouiller_topic()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET status_topic=0 WHERE id_topic='.$this->id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,715,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_VERROUILLE',formate_url('mode=topic&id_topic='.$this->id_topic,true));
	}
    /**
    *
    *
    */
	function deverrouiller_topic()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET status_topic=1 WHERE id_topic='.$this->id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,715,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_DEVERROUILLE',formate_url('mode=topic&id_topic='.$this->id_topic,true));
	}
    /**
    *
    *
    */
	function verrouiller_forum()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_FORUMS.' SET status_forum=0 WHERE id_forum='.$this->id_forum;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,727,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_FORUM_VERROUILLE',formate_url('mode=forum&id_forum='.$this->id_forum,true));
	}
    /**
    *
    *
    */
	function deverrouiller_forum()
	{
		global $c;
		$sql = 'UPDATE '.TABLE_FORUM_FORUMS.' SET status_forum=1 WHERE id_forum='.$this->id_forum;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,727,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_FORUM_DEVERROUILLE',formate_url('mode=forum&id_forum='.$this->id_forum,true));
	}
	/**
	* suivre le sujet
    *
    */
	function suivre_sujet($id_topic)
    {
		global $c,$user;
		$sql = 'INSERT INTO '.TABLE_FORUM_TOPICS_SUIVIS.' (user_id, id_topic)
                VALUES ('.$user['user_id'].','.$id_topic.')';
		$c->sql_query($sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_MARQUE_SUIVI',formate_url('mode=topic&id_topic='.$id_topic,true));
	}
	/**
	* Resilier le suivi du sujet
    *
    */
	function resilier_sujet($id_topic)
    {
		global $c,$user;
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_SUIVIS.'
				WHERE user_id='.$user['user_id'].' AND id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_MARQUE_RESILIE',formate_url('mode=topic&id_topic='.$id_topic,true));
	}
	/**
	* Ajouter aux favoris
    *
    */
	function ajouter_favoris($id_topic)
    {
		global $c,$user;
		$sql = 'INSERT INTO '.TABLE_FORUM_TOPICS_FAVORIS.' (user_id, id_topic)
                VALUES ('.$user['user_id'].','.$id_topic.')';
		$c->sql_query($sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_AJOUTE_FAVORIS',formate_url('mode=topic&id_topic='.$id_topic,true));
	}
	/**
	* Supprimer des favoris
    *
    */
	function supprimer_favoris($id_topic)
    {
		global $c,$user;
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_FAVORIS.'
				WHERE user_id='.$user['user_id'].' AND id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPIC_SUPPRIME_FAVORIS',formate_url('mode=topic&id_topic='.$id_topic,true));
	}
	/**
	* Liste user_id qui n'ont toujours pas lu ce topic
    *
    */
	function liste_user_id_nonlu_topic($id_topic)
    {
		global $c;
		$liste = array();
		$sql = 'SELECT user_id FROM '.TABLE_FORUM_TOPICS_NONLUS.' WHERE id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		while ($row = $c->sql_fetchrow($resultat)){
			$liste[] = $row['user_id'];
		}
		return $liste;
	}
	/**
	* Supprime tous les enregistrement dont le user_id est le user courant
    *
    */
	function marquer_tout_lu()
    {
		global $c,$user,$session,$module;
		$sql = 'DELETE tnl FROM '.TABLE_FORUM_TOPICS_NONLUS.' as tnl
				LEFT JOIN '.TABLE_FORUM_TOPICS.' as t ON (tnl.id_topic=t.id_topic)
				LEFT JOIN '.TABLE_FORUM_FORUMS.' as f ON (f.id_forum=t.id_forum)
				LEFT JOIN '.TABLE_FORUM_CATS.' as c ON (f.id_cat=c.id_cat)
				WHERE (c.module=\''.$module.'\' AND user_id='.$user['user_id'].')
				OR date<'.($session->time-1209600);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_TOPICS_MARQUES_LUS',formate_url('',true));
	}
	/**
	* Supprime l' enregistrement dont le user_id est le user courant et le topic courant
    *
    */
	function marquer_lu($id_topic)
    {
		global $c,$user,$session;
		$sql = 'DELETE FROM '.TABLE_FORUM_TOPICS_NONLUS.'
				WHERE (user_id='.$user['user_id'].' AND id_topic='.$id_topic.')
                OR date<'.($session->time-1209600);
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
	}
	/**
	* Ajoute le topic au listing des topics non lu des utilisateurs
    *
    */
	function marquer_non_lu($id_topic)
    {
		global $c,$user,$session;
		$sql_insert = '';

		$liste_user_id_nonlu_topic = $this->liste_user_id_nonlu_topic($id_topic);
		$sql = 'SELECT user_id FROM '.TABLE_USERS.' WHERE actif=1';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
        while ($row = $c->sql_fetchrow($resultat)){
            if (!in_array($row['user_id'],$liste_user_id_nonlu_topic)){
                if ($sql_insert!='') $sql_insert .= ', ';
                $sql_insert .= '('.$id_topic.','.$row['user_id'].','.$session->time.')';
            }
        }
        if ($sql_insert != ''){
            $sql_insert = 'INSERT INTO '.TABLE_FORUM_TOPICS_NONLUS.' (id_topic,user_id,date)
                VALUES '.$sql_insert;
            if (!$resultat = $c->sql_query($sql_insert))message_die(E_ERROR,712,__FILE__,__LINE__,$sql_insert);
        }
		return true;
	}
	/**
	* Marquer le sujet suivis comme lu
    *
    */
	function marquer_suivis_lu($id_topic)
    {
		global $c,$user;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS_SUIVIS.' SET prevenu=false
				WHERE user_id='.$user['user_id'].' AND id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
	}
	/**
	* Modifie le type d'un topic en message normal
    *
    */
	function update_topic_fin_annonce($id_topic)
    {
		global $c,$user;
		$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET type_topic=1, fin_annonce=null
				WHERE id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
	}
    /**
    * Creer un nouveau topic
    *
    */
	function creer_topic()
	{
		global $c,$user,$droits,$module;
		if (($droits->check($module,$this->id_forum,'moderer') || $user['level']>9) && $this->type_topic==2){
			$type_topic = $this->type_topic;
			$fin_annonce = $this->fin_annonce;
		}elseif (($droits->check($module,$this->id_forum,'moderer') || $user['level']>9) && $this->type_topic==3){
			$type_topic = $this->type_topic;
			$fin_annonce = 'null';
		}else{
			$type_topic = 1;
			$fin_annonce = 'null';
		}
		$sql = 'INSERT INTO '.TABLE_FORUM_TOPICS.' (titre_topic, id_forum, type_topic, fin_annonce)
                VALUES (\''.$this->_escape($this->saisie['titre']).'\',
                    '.$this->saisie['id_forum'].',
                    '.$type_topic.',
                    '.$fin_annonce.')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		$this->id_topic=$c->sql_nextid();
		$this->maj_forum();
	}
    /**
    * Enregistrer un topic
    *
    */
	function enregistrer_post()
	{
		global $c,$user,$module,$droits,$root;
		// NOUVEAU Topic
		if (isset($this->saisie['id_forum']) && isset($this->saisie['titre']) && isset($this->saisie['post'])){
			// securite : droit d'ecrire et forum verrouille ?
			$this->get_forum();
			if ((!$droits->check($module,$this->id_forum,'ecrire') || $this->forum['status_forum'] == 0 )
				&& $user['level']<10)	error404(720);
			$this->creer_topic();
			$titre = $this->saisie['titre'];
			$mode = 'topic';

		// REPONSE (simple post)
		}elseif(isset($this->saisie['id_topic']) && isset($this->saisie['post'])){
			// On doit savoir dans quel forum est ce post pour verifier les droit de l'utilisateur
			$this->get_topic();
			if ((!$droits->check($module,$this->topic['id_forum'],'repondre') || $this->topic['status_topic'] == 0 )
				&& $user['level']<10)	error404(721);
			$titre = $this->topic['titre_topic'];
			$mode = 'post';
		}else{
			message_die(E_WARNING,716,'','');
		}

		$sql = 'INSERT INTO '.TABLE_FORUM_POSTS.' (id_topic, date_post, user_id, text_post,ip_posteur)
                VALUES ('.$this->id_topic.',
                    '.time().',
                    '.$user['user_id'].',
                    \''.$this->_escape($this->saisie['post']).'\',
                    \''.$user['user_ip'].'\')';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,713,__FILE__,__LINE__,$sql);
		$this->id_post = $c->sql_nextid();
		// Update topic
		$this->maj_topic();
		// On met à jour le nbre de messages de l'utilisateur
		$this->update_nbre_messages($user['user_id'],'+');
		// Enregistrement en local des images
		require_once($root.'class/class_image.php');
		$image = new image();
		$this->saisie['post'] = $image->copie_locale_images($this->_escape($this->saisie['post']),'UPDATE '.TABLE_FORUM_POSTS.' SET text_post=\'%s\' WHERE id_post='.$this->id_post);
		// Marquer ce topic comme non lu
		$this->marquer_non_lu($this->id_topic);
		// Abonner le user ayant posté au suivis de ce topic
		if ($user['forum_email_reponse'] == true)$this->suivre_sujet($this->id_topic);
		// envoi du mail de notification
		if ($mode == 'post'){
			$this->mail_notification_reponse($this->id_topic,$this->id_post,$titre,$this->saisie['post']);
		}
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_POST_ENREGISTRE',formate_url('mode=topic&id_topic='.$this->id_topic.'&id_post='.$this->id_post.'#'.$this->id_post,true));
	}
    /**
    * Editer/Modifier un topic
    *
    */
	function editer_post()
	{
		global $c,$root,$module,$user,$droits;
		// MAJ du post
		if (isset($this->saisie['post']) && !empty($this->saisie['post'])){
			if (empty($this->saisie['post'])) message_die(E_WARNING,716,'','');
			$sql = 'UPDATE '.TABLE_FORUM_POSTS.' SET
						text_post=\''.$this->_escape($this->saisie['post']).'\'
					WHERE id_post='.$this->saisie['id_post'];
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,713,__FILE__,__LINE__,$sql);
			// Enregistrement en local des images
			require_once($root.'class/class_image.php');
			$image = new image();
			$image->copie_locale_images($this->_escape($this->saisie['post']),'UPDATE '.TABLE_FORUM_POSTS.' SET text_post=\'%s\' WHERE id_post='.$this->saisie['id_post']);
		}
		if (empty($this->saisie['titre']) || empty($this->saisie['post'])) message_die(E_WARNING,716,'','');
		// MAJ du titre
		if (!empty($this->saisie['titre'])){
            $this->id_post = $this->saisie['id_post'];
            $this->get_post();
            $this->id_topic = $this->post['id_topic'];
            $this->get_topic();
            if (($droits->check($module,$this->topic['id_forum'],'moderer') || $user['level']>9)
                && $this->type_topic==2)
            {
                $type_topic = $this->type_topic;
                $fin_annonce = $this->fin_annonce;
            }elseif (($droits->check($module,$this->topic['id_forum'],'moderer') || $user['level']>9)
                && $this->type_topic==3)
            {
                $type_topic = $this->type_topic;
                $fin_annonce = 'null';
            }else{
                $type_topic = 1;
                $fin_annonce = 'null';
            }
            $sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET
                        titre_topic=\''.$this->_escape($this->saisie['titre']).'\',
                        type_topic='.$type_topic.',
                        fin_annonce='.$fin_annonce.'
                    WHERE id_topic = '.$this->id_topic;
            if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,713,__FILE__,__LINE__,$sql);
		}
		// On affiche une fenetre de confirmation
		affiche_message('forum','L_POST_ENREGISTRE',formate_url('mode=topic&id_post='.$this->saisie['id_post'],true));
	}
	/**
	* Envoi d'un mail a tous les participants d'un topic
    *
    */
	function mail_notification_reponse($id_topic,$id_post,$titre,$message)
    {
		global $c,$cf,$root,$module,$lang,$user,$post;
		$liste_user_id = array();
		require_once($root.'class/class_mail.php');
		$email = new mail();
		$url = 'http://'.$cf->config['adresse_site'].$cf->config['path'].'index.php?module='.$module.'&mode=topic&id_topic='.$id_topic.'&id_post='.$id_post.'#'.$id_post;
		$email->subject = $lang['L_MAIL_SUJET'];
		$email->message_explain = sprintf($lang['L_MAIL_BODY_HTML'],$url,$url);
		$email->titre_message = $post->bbcode2html($titre);
		$email->formate_html($post->bbcode2html($message));

		// Liste des participants voulant recevoir un email de notification
		$sql = 'SELECT s.user_id, u.email, u.pseudo
				FROM '.TABLE_FORUM_TOPICS_SUIVIS.' AS s
				LEFT JOIN '.TABLE_USERS.' AS u ON (s.user_id=u.user_id)
				LEFT JOIN '.TABLE_FORUM_TOPICS_NONLUS.' AS tnl ON (s.user_id=tnl.user_id AND s.id_topic=tnl.id_topic)
				WHERE u.forum_email_reponse=true
				AND s.prevenu=false
				AND s.id_topic='.$id_topic;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
		while ($row = $c->sql_fetchrow($resultat)){
			if ($user['user_id'] != $row['user_id']){
				$email->addaddress( $row['email'],$row['pseudo']);
				$liste_user_id[] = $row['user_id'];
			}
		}
		if (sizeof($liste_user_id) >0){
 			if(!$email->send()){
                message_die(E_WARNING,35,__FILE__,__LINE__);
			}
			// Mise a jour du champs "Prevenu" a true pour eviter qu'ils se fassent spammer a chaque nouveau message
			$liste_user_id = implode(',',$liste_user_id);
			if ($liste_user_id=='')$liste_user_id='""';
			$sql = 'UPDATE '.TABLE_FORUM_TOPICS_SUIVIS.'
                    SET prevenu=true
					WHERE id_topic='.$id_topic.' AND user_id IN ('.$liste_user_id.')';
			$c->sql_query($sql);
		}
	}
    /**
    *
    *
    */
	function maj_topic()
    {
		global $c;
		$premier_post = $dernier_post = 'null';
		$sql = 'SELECT id_post FROM '.TABLE_FORUM_POSTS.' WHERE id_topic='.$this->id_topic.' ORDER BY date_post ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		$nbre_resultats = $c->sql_numrows();
		if ($nbre_resultats > 0){
			$i=0;
			while($row = $c->sql_fetchrow($resultat)){
				if ($i==0) $premier_post = $row['id_post'];
				if ($i==($nbre_resultats-1)) $dernier_post = $row['id_post'];
				$i++;
			}
			if ($premier_post==$dernier_post)$dernier_post='null';
			// MAJ des id des premiers/derniers posts du topic
			$sql = 'UPDATE '.TABLE_FORUM_TOPICS.' SET
						post_depart='.$premier_post.',
						post_fin='.$dernier_post.',
						reponses_topic='.($nbre_resultats-1).',
						date_topic='.time().'
					WHERE id_topic='.$this->id_topic;
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		}
	}
    /**
    *
    *
    */
	function maj_forum()
	{
		global $c;
		$this->maj_forums($this->saisie['id_forum']);
	}
    /**
    *
    *
    */
	function maj_forums($id_forum=false)
	{
		global $c,$cache;
		$sql = 'SELECT COUNT(id_topic) as cpt,f.id_forum FROM '.TABLE_FORUM_FORUMS.' as f
				LEFT JOIN '.TABLE_FORUM_TOPICS.' as t ON (f.id_forum=t.id_forum)';
		if ($id_forum!=false) $sql .= ' WHERE f.id_forum='.$id_forum;
		$sql .= ' GROUP BY f.id_forum';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,712,__FILE__,__LINE__,$sql);
		while($row = $c->sql_fetchrow($resultat)){
			$sql_update = 'UPDATE '.TABLE_FORUM_FORUMS.' SET nbre_topics='.intval($row['cpt']).'
						WHERE id_forum='.$row['id_forum'];
			if (!$c->sql_query($sql_update))message_die(E_ERROR,712,__FILE__,__LINE__,$sql_update);
		}
		// On met a jour le cache
		$this->liste_forums = $cache->appel_cache('listing_forums',true);
	}
    /**
    *
    *
    */
	function get_post()
	{
		global $c;
		if (!isset($this->id_post)) error404(711);
		$sql = 'SELECT p.*, t.*
				FROM '.TABLE_FORUM_POSTS.' AS p
				LEFT JOIN '.TABLE_FORUM_TOPICS.' AS t ON (p.id_topic=t.id_topic)
                WHERE p.id_post='.$this->id_post;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
		$this->post = $c->sql_fetchrow($resultat);
	}
    /**
    *
    *
    */
	function get_topic()
	{
		global $c;
		if (!isset($this->id_topic)) error404(711);
		$sql = 'SELECT t.*,p.*,f.*
				FROM '.TABLE_FORUM_TOPICS.' AS t
				LEFT JOIN '.TABLE_FORUM_POSTS.' AS p ON (t.id_topic=p.id_topic)
				LEFT JOIN '.TABLE_FORUM_FORUMS.' AS f ON (t.id_forum=f.id_forum)
				WHERE t.id_topic='.$this->id_topic.'
				ORDER BY date_post ASC';
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
		$this->topic = $c->sql_fetchrow($resultat);
	}
    /**
    *
    *
    */
	function get_forum()
	{
		global $c;
		$sql = 'SELECT f.*
				FROM '.TABLE_FORUM_FORUMS.' AS f
				WHERE f.id_forum='.$this->id_forum;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
		$this->forum = $c->sql_fetchrow($resultat);
	}
	/**
	* Incremente le compteur de messages de l'utilisateur
    *
    */
	function update_nbre_messages($user_id,$sens='+')
    {
		global $c;
		$sql = 'UPDATE '.TABLE_USERS.' AS u
				SET msg=msg'.$sens.'1
				WHERE user_id='.$user_id;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
	}
	/**
	* Fournit le nom du module auquel appartient un forum via son ID
    *
    */
	function get_module_titre_forum($id_forum)
    {
		global $c;
		$sql = 'SELECT module, titre_forum
				FROM '.TABLE_FORUM_FORUMS .' AS f
				LEFT JOIN '.TABLE_FORUM_CATS.' AS c ON (f.id_cat=c.id_cat)
				WHERE id_forum='.$id_forum;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
		if ( $c->sql_numrows()> 0){
			return $c->sql_fetchrow($resultat);
		}else
            return false;
	}
	/**
	* Ajoute le forum comme noeud dans les permissions
    *
    */
	function droits_ajoute_noeud($id_forum,$module,$titre_forum)
    {
		global $root,$droits;
		$regles= array();
		$file = $root.'plugins/modules/forum/_admin_rules.php';
		if (file_exists($file)){
			require_once($file);
			// invites
			$droits->add_regles($module,$id_forum,1,$titre_forum,$regles[1]);
			// membres
			$droits->add_regles($module,$id_forum,2,$titre_forum,$regles[2]);
			//admins
			$droits->add_regles($module,$id_forum,3,$titre_forum,$regles[3]);
		}
	}
	/**
	* Edite l'alias associe aux droits
    *
    */
	function droits_edite_alias($id_forum,$module,$titre_forum)
    {
		global $c;
		$sql= 'UPDATE '.TABLE_DROITS_REGLES.' SET alias="'.$titre_forum.'"
				WHERE module="'.$module.'" AND id_noeud='.$id_forum;
		if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,703,__FILE__,__LINE__,$sql);
	}
}
?>
