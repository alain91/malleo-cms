<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Fonctions par defaut et droits appliques aux groupes 
// l'ID dans le tableau regles correspond a l'id du groupe

// INVITES
$regles[1] = array(
	'voir'			=> 1,
	'jouer'			=> 0,
	'favoris'		=> 0,
	'moderer'		=> 0,
);
// MEMBRES 
$regles[2] = array(
	'voir'			=> 1,
	'jouer'			=> 1,
	'favoris'		=> 1,
	'moderer'		=> 0,
);

// ADMINS
$regles[3] = array(
	'voir'			=> 1,
	'jouer'			=> 1,
	'favoris'		=> 1,
	'moderer'		=> 1,
);
?>