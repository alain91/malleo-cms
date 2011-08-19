<?php
defined('PROTECT_ADMIN') OR die("Tentative de Hacking");

// Fonctions par defaut et droits appliques aux groupes 
// l'ID dans le tableau regles correspond a l'id du groupe
// INVITES
$regles[1] = array(
	'lire'			=> 0,
	'ecrire'		=> 0,
	'supprimer'		=> 0,
	'approuver' 	=> 0,
	'ecrire_tout'	=> 0,
	'supprimer_tout' => 0,
);
// MEMBRES 
$regles[2] = array(
	'lire'			=> 1,
	'ecrire'		=> 1,
	'supprimer'		=> 1,
	'approuver' 	=> 0,
	'ecrire_tout'	=> 0,
	'supprimer_tout' => 0,
);

// ADMINS
$regles[3] = array(); // On droit à tout systématiquement
?>