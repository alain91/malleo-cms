<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Fonctions par defaut et droits appliques aux groupes 
// l'ID dans le tableau regles correspond a l'id du groupe
// INVITES
$regles[1] = array(
	'voir_membres'	=> 1,
	'voir_groupes'	=> 0,
	'rechercher'	=> 0
);
// MEMBRES 
$regles[2] = array(
	'voir_membres'	=> 1,
	'voir_groupes'	=> 1,
	'rechercher'	=> 1
);

// ADMINS
$regles[3] = array(
	'voir_membres'	=> 1,
	'voir_groupes'	=> 1,
	'rechercher'	=> 1
);

?>