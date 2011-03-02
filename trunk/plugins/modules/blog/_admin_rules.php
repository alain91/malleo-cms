<?php
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
// Fonctions par defaut et droits appliques aux groupes 
// l'ID dans le tableau regles correspond a l'id du groupe

// INVITES
$regles[1] = array(
	'voir'				=> 1,
	'commenter'			=> 0,
	'poster'			=> 0,
	'editer'			=> 0,
	'supprimer'			=> 0,
	'date_publication'	=> 0,
	'tags'				=> 0
);
// MEMBRES 
$regles[2] = array(
	'voir'				=> 1,
	'commenter'			=> 1,
	'poster'			=> 0,
	'editer'			=> 0,
	'supprimer'			=> 0,
	'date_publication'	=> 0,
	'tags'				=> 0
);

// ADMINS
$regles[3] = array(
	'voir'				=> 1,
	'commenter'			=> 1,
	'poster'			=> 1,
	'editer'			=> 1,
	'supprimer'			=> 1,
	'date_publication'	=> 1,
	'tags'				=> 1
);
?>