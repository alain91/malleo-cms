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
class Modelisation
{
	var $nom_switch;
	var $question;
	var $reponse;	
	var $page;
	var $lang;
	var $liste_champs=array();
	var $valeur_actuelle='';
	var $deporter=false;
	
	function Modelisation()
	{
		global $lang;
		load_lang('time');
		$this->lang = $lang;
	}
	
	
	//
	// Permet d'ajouter rapidement une ligne dans le panneau d'admin
	
	function ajouter_champs($clef_lang, $var, $type, $param = null)
	{
		global $tpl;
		switch ($type)
		{
			case 'text':	$this->reponse = $this->choix_var($var,$param);		break;
			case 'password':$this->reponse = $this->choix_password($var,$param);break;
			case 'email':	$this->reponse = $this->choix_var($var,$param);		break;
			case 'select':	$this->reponse = $this->choix_select($var,$param);	break;
			case 'bool':	$this->reponse = $this->choix_bool($var,$param);	break;
			case 'date':	$this->reponse = $this->choix_date($var,$param);	break;
			case 'image':	$this->reponse = $this->choix_var($var,$param);		break;
		}
		$tpl->assign_block_vars($this->nom_switch, array(
			'QUESTION' =>	$clef_lang,
			'REPONSE' =>	$this->reponse
		));
	}
	
	function ajouter_champ($var, $type, $param = null)
	{
		switch ($type)
		{
			case 'text':	return  $this->choix_var($var,$param);		break;
			case 'password':return  $this->choix_password($var,$param);	break;
			case 'email':	return  $this->choix_var($var,$param);		break;
			case 'select':  return  $this->choix_select($var,$param);	break;
			case 'bool':	return  $this->choix_bool($var,$param);		break;
			case 'date':	return  $this->choix_date($var,$param);		break;
			case 'image':	return  $this->choix_var($var,$param);		break;
		}
	}
	
	function formate_affichage($var, $type, $param = null)
	{
		switch ($type)
		{
			case 'text':	return  $this->formate_var($var);				break;
			case 'password':return  $this->formate_password($var);			break;
			case 'email':	return	$this->formate_url($var,true);			break;
			case 'select':  return  $this->formate_select($var);			break;
			case 'bool':	return  $this->formate_bool($var,$param);		break;
			case 'date':	return  $this->formate_date($var,$param);		break;
			case 'image':	return  $this->formate_image($var,$param);		break;
		}
	}
	
	function RetourValeur($var)
	{
		if ($this->deporter == false)
		{
			return ($this->valeur_actuelle != '') ?  eval('return '.$this->valeur_actuelle.'(\''.$var.'\');'):'';
		}else{
			return $this->valeur_actuelle;
		}
	}
	//
	// TYPE : chaine de caracteres
	function choix_var($var, $param)
	{
		return '<input type="text" name="'.$var.'" value="'.$this->RetourValeur($var).'" size="'.$param.'" />';
	}

	//
	// TYPE : chaine de caracteres cachee
	function choix_password($var, $param)
	{
		return '<input type="password" name="'.$var.'" value="'.$this->RetourValeur($var).'" size="'.$param.'" />';
	}
	
	function formate_var($var)
	{
		return $this->RetourValeur($var);
	}
	function formate_password($var)
	{
		return $this->RetourValeur($var);
	}
	
	function formate_url($var,$email=false)
	{
		$mailto = ($email==true)?'mailto:':'';
		$url = $this->RetourValeur($var);
		return '<a href="'.$mailto.$url.'">'.$url.'</a>';
	}
	//
	// TYPE : liste de choix
	function choix_select($var,$param)
	{
		$options =  '<select name="'.$var.'">';
		if ($param != null )
		{
			$param  = eval('return '.$param.'();');
			foreach ($param as $key)
			{
				$select = ($this->RetourValeur($var) == $key)? ' selected="selected"':'';
				$options .= '<option value="'.$key.'"'.$select.'>'.$key.'</option>';
			}
		}
		$options .=  '</select>';
		return $options;
	}
	
	function formate_select($var)
	{
		return $this->RetourValeur($var);	
	}
	
	//
	// TYPE : booleen (2 possibilités)
	function choix_bool($var,$param)
	{
		$oui = ($this->RetourValeur($var) == 1)?' checked="checked"':'';
		$non = ($this->RetourValeur($var) == 0)?' checked="checked"':'';
		$param  = eval('return '.$param.'();');
		return $param[0].': <input type="radio" name="'.$var.'" value="1" '.$oui.' />&nbsp;&nbsp;&nbsp; '.$param[1].': <input type="radio" name="'.$var.'" value="0" '.$non.' />';
	}
	
	function formate_bool($var,$param)
	{
		$param  = eval('return '.$param.'();');
		return ($this->RetourValeur($var) == 1)?$param[0]:$param[1];
	
	}
	
	//
	// TYPE : date [ jj ] [ mm ] [ AAAA ]
	function choix_date($var,$param)
	{
		$retour  = '
		<input type="hidden" name="'.$var.'" id="'.$var.'" value="'.$this->RetourValeur($var).'" />
		<script language="JavaScript" type="text/javascript" src="js/requete_ajax.js"></script>
		<script language="JavaScript" type="text/javascript">
			function GMTimestamp()
			{
				DateChoisie = "";
				if (document.getElementById("date_Y")) DateChoisie += "Y:"+document.getElementById("date_Y").value+"|";
				if (document.getElementById("date_n")) DateChoisie += "n:"+document.getElementById("date_n").value+"|";
				if (document.getElementById("date_j")) DateChoisie += "j:"+document.getElementById("date_j").value+"|";
				if (document.getElementById("date_H")) DateChoisie += "H:"+document.GetElementById("date_H").value+"|";
				if (document.getElementById("date_i")) DateChoisie += "i:"+document.getElementById("date_i").value+"|";
				if (document.getElementById("date_s")) DateChoisie += "s:"+document.getElementById("date_s").value+"|";
				RequeteAjax("fonctions/fct_modelisation.php","POST","date="+DateChoisie,"'.$var.'","ChangerValeurDiv");
				return true;
			}
		</script>
		<table><tr>';
		// Date saisie 
		//exemple : 1:2:2008 pour 1er février 2008
		$date = explode(':',date('j:n:Y:H:i:s',$this->RetourValeur($var)));

		$champs =  explode(':',$param);
		foreach ($champs as $key=>$val){
			$retour .= '<td nowrap="nowrap">';
			switch($val){
				case 'Y':
					$retour .= '<select id="date_Y" OnChange="GMTimestamp();">';
					for ($annee=1920;$annee<=2020;$annee++){
						$selected = ($date[2] == $annee)? ' selected': '';
						$retour .= '<option'.$selected.'>'.$annee.'</option>';
					}
					$retour .= '</select>';
					break;
				case 'n':
					$retour .= '<select id="date_n" OnChange="GMTimestamp();">';
					for ($mois=1;$mois<=12;$mois++){
						$selected = ($date[1] == $mois)? ' selected': '';
						$retour .= '<option value="'.$mois.'"'.$selected.'>'.$this->lang['mois'][$mois].'</option>';
					}
					$retour .= '</select>';			
					break;
				case 'j':
					$retour .= '<select id="date_j" OnChange="GMTimestamp();">';
					for ($jour=1;$jour<=31;$jour++){
						$selected = ($date[0] == $jour)? ' selected': '';
						$retour .= '<option'.$selected.'>'.$jour.'</option>';
					}
					$retour .= '</select>';
					break;
				case 'H':
					$retour .= '<select id="date_H" OnChange="GMTimestamp();">';
					for ($heure=0;$heure<=23;$heure++){
						$selected = ($date[3] == $heure)? ' selected': '';
						$retour .= '<option'.$selected.'>'.$heure.'</option>';
					}
					$retour .= '</select>'.$this->lang['L_HEURE'];
					break;
				case 'i':
					$retour .= '<select id="date_i" OnChange="GMTimestamp();">';
					for ($minute=0;$minute<=59;$minute++){
						$selected = ($date[4] == $minute)? ' selected': '';
						$retour .= '<option'.$selected.'>'.$minute.'</option>';
					}
					$retour .= '</select>'.$this->lang['L_MINUTE'];
					break;
				case 's':
					$retour .= '<select id="date_s" OnChange="GMTimestamp();">';
					for ($seconde=0;$seconde<=59;$seconde++){
						$selected = ($date[5] == $seconde)? ' selected': '';
						$retour .= '<option'.$selected.'>'.$seconde.'</option>';
					}
					$retour .= '</select>'.$this->lang['L_SECONDE'];
					break;
			}
			$retour .= '</td>';
		}
		$retour .= '</tr></table>';
		unset($date,$champs,$annee,$mois,$jour,$heure,$minute,$seconde);
		return $retour;
	}

	function formate_date($var,$param)
	{
		return ereg_replace(':','/',date($param,$this->RetourValeur($var)));
	}

	function choix_image($var,$param)
	{
		return $this->RetourValeur($var);
	}	
	
	function formate_image($var,$param)
	{
		return '<img src="'.$this->RetourValeur($var).'" height="'.$param.'" />';
	}
	//
	// Récupère en base les données correspondant à l'interface traitée
	// et exporte les résultats dans le template demandé.
	
	function generer_saisie()
	{
		global $c,$lang;
		$sql = 'SELECT  nom_champs, type_saisie, lang, param 
				FROM '.TABLE_MODELISATION.'
				WHERE page=\''.$this->page.'\'';
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,14,__FILE__,__LINE__,$sql);
		
		if ($this->deporter == false )
		{	// On affiche pendant le traitement les champs
			while ($row = $c->sql_fetchrow($resultat))
			{
				$this->ajouter_champs($lang[$row['lang']], $row['nom_champs'], $row['type_saisie'], $row['param']);
			}
		}else{
			// Les champs seront retraités ulterieurement
			$liste = '';
			while ($row = $c->sql_fetchrow($resultat))
			{
					$this->liste_champs[$row['nom_champs']] = array(
						'lang' 			=> $row['lang'],
						'nom_champs'	=> $row['nom_champs'],
						'type_saisie'	=> $row['type_saisie'],
						'param'			=> $row['param']
					);
					if ($liste != '') $liste .= ',';
					$liste .= $row['nom_champs'];
			}
			return $liste;
		}
	}
}


?>