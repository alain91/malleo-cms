<?php
define('PROTECT',true);
$root = '../../';
require_once($root.'/chargement.php');
$style_name=load_style();
$lang=$erreur=array();
load_lang('defaut');
if (isset($_POST['RequeteAjax']) && strlen($_POST['RequeteAjax']) > 0 )
{
	$sql = 'SELECT DISTINCT pseudo FROM '.TABLE_USERS.' 
	WHERE pseudo REGEXP lower("'.strtolower(protection_chaine($_POST['RequeteAjax'])).'") 
	AND user_id>1 LIMIT 0,10';	
	if ($resultat = $c->sql_query($sql)){ //message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
		$options = '';
		while($row = $c->sql_fetchrow($resultat))
		{
			if ($options!='') $options .= '##';
			$options .= utf8_encode(html_to_str($row['pseudo']));
		}
		die($options);
	}
}
die('?');
?>