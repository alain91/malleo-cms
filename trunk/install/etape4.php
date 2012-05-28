<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}

if (isset($_POST['nom_base']) && isset($_POST['adresse_base']) && isset($_POST['nom_utilisateur'])){
	$contenu = '<?php
$hote = \''.trim($_POST['adresse_base']).'\';
$base = \''.trim($_POST['nom_base']).'\';
$utilisateur = \''.trim($_POST['nom_utilisateur']).'\';
$password = \''.trim($_POST['mdp']).'\';
?>';

	// Enregistrement du fichier config.php
	$fichier = $root.'config/config.php';
	if (is_writable($fichier)){
		$file = @fopen($fichier, 'w');
		@fwrite($file, $contenu);
		@fclose($file);
		//@chmod($fichier,0644);
		$tpl->assign_block_vars('creation_fichier_ok', array());
	}else{
		require_once($root.'librairies/geshi/geshi.php');
		$geshi = new GeSHi($contenu, 'PHP');
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		$tpl->assign_vars(array(
			'FILE'	=> $geshi->parse_code()
		));
		$tpl->assign_block_vars('creation_fichier_nok', array());
	}
}else{
	header('location: index.php?etape=3');
}

$tpl->assign_vars(array(
	'L_EXPLAIN_FICHIER_CONFIG'		=> $lang['L_EXPLAIN_FICHIER_CONFIG'],
	'L_EXPLAIN_FICHIER_CONFIG_NOK'	=> $lang['L_EXPLAIN_FICHIER_CONFIG_NOK'],
	'VALIDE'						=> $img['valide'],
	'INVALIDE'						=> $img['invalide'],
));
?>