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
	'lire'			=> 1,
	'ecrire'		=> 0,
	'proteger'		=> 0,
	'moderer'		=> 0,
	'supprimer'		=> 0
);
// MEMBRES 
$regles[2] = array(
	'voir'			=> 1,
	'lire'			=> 1,
	'ecrire'		=> 1,
	'proteger'		=> 0,
	'moderer'		=> 0,
	'supprimer'		=> 0
);

// ADMINS
$regles[3] = array(
	'voir'			=> 1,
	'lire'			=> 1,
	'ecrire'		=> 1,
	'proteger'		=> 1,
	'moderer'		=> 1,
	'supprimer'		=> 1
);
?>