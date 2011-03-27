<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Citations
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|  Please read Licence_CeCILL_V2-en.txt
|  SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/
defined('CITATIONS_PATH') OR die("Tentative de Hacking");

// Listing des tables
global $prefixe;
define('TABLE_CITATIONS', $prefixe.'mod_citations');

// Fichier des Classes du Module
require_once(CITATIONS_PATH.'/class_citations.php');

global $citations;
$citations = new Citations();

// Chargement des fichiers de langue si il y'en a
load_lang_mod('citations');
?>