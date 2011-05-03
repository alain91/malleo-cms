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

//
// Renvoie la valeur d'une variable de la table config
function valeur_variable_config($var)
{
	global $cf;
	return $cf->config[$var];
}

//
// Liste les styles présents sur le serveur
function lister_styles()
{
	$chemin = 'styles/';
	$liste_styles = array();
	$ch = @opendir($chemin);
	while ($style = @readdir($ch))
	{
		if ($style != "." && $style != ".." && is_dir($chemin.$style)) {
			$liste_styles[] = $style;
		}
	}
	@closedir($ch);
	return $liste_styles;
}

//
// Liste les modèles

function lister_modeles(){
	global $c,$lang;
	$sql = 'SELECT id_modele,titre_modele FROM '.TABLE_MODELES.' ORDER BY titre_modele ASC';
	if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
	$liste_modeles[] = '';
	while($row = $c->sql_fetchrow($resultat))
	{
		$liste_modeles[$row['id_modele']] = $row['titre_modele'];
	}
	return $liste_modeles;
}


//
// Liste les types de charset possibles

function lister_charset()
{
	return array('ISO-8859-1','ISO-8859-2','ISO-8859-3','ISO-8859-4','ISO-8859-5','ISO-8859-6','ISO-8859-7','ISO-8859-8','ISO-8859-9',
				'ISO-8859-10','ISO-8859-11','ISO-8859-12','ISO-8859-13','ISO-8859-14','ISO-8859-15','Windows-1250','Windows-1251',
				'Windows-1252','Windows-1256','Windows-1257','koi8-r','big5','gb2312','UTF-8','UTF-7','x-user-defined','euc-jp',
				'ks_c_5601-1987','tis-620','SHIFT_JIS');
}

//
//  Renvoie les clefs de langue pour les terme OUI et NON ou pour autre chose..active/desactive
function possibilites_bool()
{
	global $lang;
	return array($lang['L_OUI'],$lang['L_NON']);
}

function lister_langues()
{
	$chemin = 'lang/';
	$liste_langues = array();
	$ch = @opendir($chemin);
	while ($lang = @readdir($ch))
	{
		if ($lang != "." && $lang != ".." && is_dir($chemin.$lang)) {
			$liste_langues[] = $lang;
		}
	}
	@closedir($ch);
	return $liste_langues;
}

function lister_levels()
{
	return array(1,2,3,4,5,6,7,8,9,10);
}

function lister_chiffres($d,$f,$select,$exclus=array())
{
	$liste='';
	for ($i=$d;$i<=$f;$i++)
	{
		if (!in_array($i,$exclus))
		{	
			$selected = ($i==$select)? ' selected="selected"':'';
			$liste .= "\n ".'<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
	}
	return $liste;
}
function lister($tableau,$select){
	$liste='';
	foreach ($tableau as $id=>$libelle){
		$liste .= '<option value="'.$id.'"'.(($select==$id)?' selected="selected"':'').'>'.$libelle.'</option>'."\n";
	}
	return $liste;
}
//
//
function lister_sens()
{
	return array('ASC','DESC');
}

function lister_wysiwyg(){
	return array('tinymce');
}

function lister_affichage_membres(){
	return array('pseudo ASC','pseudo DESC','date_register ASC','date_register DESC','msg ASC','msg DESC','level ASC','level DESC');
}

function deplacer_id_tableau($nom_table, $id_table, $position_table, $sens_position_table, $id_cible, $sens='+', $joker_table='')
{
	global $c;
	$liste= array();
	$sql = 'SELECT '.$id_table.' FROM '. $nom_table .' '.$joker_table.' 
			ORDER BY '.$position_table.' '.$sens_position_table;
	if( !($result = $c->sql_query($sql)) )message_die(E_ERROR,41,__FILE__,__LINE__,$sql); 
	while ($row = $c->sql_fetchrow($result)){	$liste[] = $row[$id_table];}
	if (in_array($id_cible,$liste))
	{
		$clef = array_search($id_cible,$liste);
		switch ($sens)
		{
			case '+':
				if (!array_key_exists(($clef-1),$liste)) return true;
				$tmp = $liste[$clef];
				$liste[$clef] = $liste[($clef-1)];
				$liste[($clef-1)] = $tmp;
				break;
			case '-':
				if (!array_key_exists(($clef+1),$liste)) return true;
				$tmp = $liste[$clef];
				$liste[$clef] = $liste[($clef+1)];
				$liste[($clef+1)] = $tmp;		
				break;		
		}
		for ($i=0;$i<sizeof($liste);$i++)
		{
			$sql = 'UPDATE '. $nom_table .' 
					SET '.$position_table.'='.$i.' 
					WHERE '.$id_table.'='.$liste[$i];
			$c->sql_query($sql);
		}
		return true;
	}else return false;
}
?>