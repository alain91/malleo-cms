<?php
defined('PROTECT_ADMIN') OR die("Tentative de Hacking");

// Fonctions par defaut et droits appliques aux groupes 
// l'ID dans le tableau regles correspond a l'id du groupe

// INVITES
$regles[1] = array(
	'lire'			=> 0,
);

// MEMBRES 
$regles[2] = array(
	'lire'			=> 1,
);

// ADMINS
$regles[3] = array(
	'lire'			=> 1,
);
?>