<?php
global $lang,$erreur;

$lang['CHEMIN_ICONES'] 	= 'data/icones_annonces/';

$lang['L_MENU_ANNONCES'] 				= 'Annonces';
$lang['L_MENU_ANNONCES_CONFIGURATION'] 	= 'Configuration';
$lang['L_MENU_ANNONCES_CATEGORIES'] 	= 'Cat&eacute;gories';

$lang['L_BILLETS_PAR_PAGE'] = 'Nombre de billets par page';
$lang['L_CAT_COLS'] 		= 'Nombre de colonne d\'affichage des catégories';

$lang['L_EDITER'] 		= 'Editer';
$lang['L_SUPPRIMER'] 	= 'Supprimer';
$lang['L_TOUT_REMPLIR']	= 'Merci de mettre au moins un titre';
$lang['L_LIST_NOT_APPROVED'] = 'Lister PA non approuvées';
$lang['L_ORDER_BY'] 	= 'Trier par';
$lang['L_NOT_APPROVED'] = 'PA non approuvée, à ce jour';

$lang['L_CREE'] 	= 'Créé le';
$lang['L_MODIFIE'] 	= 'Modifié le';
$lang['L_APPROUVE'] = 'Approuvé le';

$lang['L_TITRE_PAGE'] 	= 'Petites annonces';

$lang['L_ID'] 			= 'ID';
$lang['L_TITRE'] 		= 'Titre';
$lang['L_DESCRIPTION'] 	= 'Description';
$lang['L_IMAGE'] 		= 'Image';
$lang['L_MODULE'] 		= 'Module';
$lang['L_CATEGORIE'] 	= 'Catégorie';
$lang['L_PRIX'] 		= 'Prix';
$lang['L_PRIX_UNITE']	= '&euro;';
$lang['L_TYPE'] 		= 'Type';
$lang['L_PICTURE'] 		= 'Image';
$lang['L_DATE_DEBUT'] 	= 'Date de parution';
$lang['L_NB_SEMAINES'] 	= 'Nombre de semaines';
$lang['L_DATE_CREATION'] 	 = 'Date de création';
$lang['L_DATE_MODIFICATION'] = 'Date de modification';
$lang['L_DATE_APPROBATION'] = 'Date approbation';

$lang['L_SAVE'] = 'Enregistrer';
$lang['L_APPROVE'] = 'Approuver';

$lang['L_REQUIRE_FLOAT']	= 'Entrer un nombre à virgule';
$lang['L_REQUIRE_UPLOAD']	= 'Les seuls formats gérés de fichier image sont : gif, png ou jpeg';
$lang['L_NO_SMALLADS']		= 'Aucune petite annonce trouvée';
$lang['L_EDIT_SUCCESS']		= 'Modifications enregistrées';
$lang['L_CONFIRM_DELETE']	= 'Confirmer la suppression';
$lang['L_CONFIRM_DELETE_PICTURE']	= 'Confirmer la suppression de la photo';
$lang['L_CREATED']			= 'Créé le : ';
$lang['L_UPDATED']			= 'Modifié le : ';

$lang['L_USAGE_LEGEND']	= 'Conditions générales d\'utilisation';
$lang['L_AGREE_TERMS']	= 'J\'accepte les conditions générales d\'utilisation';
$lang['L_CGU_CONTENTS']	= 'Contenu des conditions générales d\'utilisation';

$lang['sa_group_all'] 	= 'Tout';
$lang['sa_group_1']		= 'Vend';
$lang['sa_group_2']		= 'Achète';
$lang['sa_group_3'] 	= 'Echange';
$lang['sa_group_4'] 	= ''; // vide si fin de liste
$lang['sa_group_5'] 	= '';
$lang['sa_group_6'] 	= '';
$lang['sa_group_7'] 	= '';
$lang['sa_group_8'] 	= '';
$lang['sa_group_9'] 	= '';

$lang['sa_sort_title']	= 'Titre';
$lang['sa_sort_date']	= 'Date';
$lang['sa_sort_price']	= 'Prix';

$lang['sa_mode_asc']	= 'Croissant';
$lang['sa_mode_desc']	= 'Décroissant';

$erreur[2100] = 'Le contenu des cgu est vide';
$erreur[2105] = 'Problème d\'accès au fichier des conditions générales';
$erreur[2110] = 'Vous n\'avez pas accepté les conditions générales';
$erreur[2115] = 'Erreur durant chargement photo';
$erreur[2120] = 'Format fichier image non supporté';
$erreur[2125] = 'Impossible de créer les images';
$erreur[2130] = 'Erreur fonction resize';
$erreur[2135] = 'Erreur fonction resample';
$erreur[2140] = 'Il manque l\'extension gd';
$erreur[2145] = 'Fonction getimagsize non trouvée';
?>