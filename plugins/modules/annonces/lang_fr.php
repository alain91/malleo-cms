<?php
global $lang,$erreur;

$lang['L_MENU_ANNONCES'] 				= 'Annonces';
$lang['L_MENU_ANNONCES_CONFIGURATION'] 	= 'Configuration';
$lang['L_MENU_ANNONCES_CATEGORIES'] 	= 'Cat&eacute;gories';

$lang['L_BILLETS_PAR_PAGE'] = 'Nombre de billets par page';
$lang['L_CAT_COLS'] 		= 'Nombre de colonne d\'affichage des catégories';

$lang['L_AJOUTER_CAT'] 		= 'Ajouter une cat&eacute;gorie';
$lang['L_TITRE'] 			= 'Titre';
$lang['L_IMAGE'] 			= 'Image';
$lang['L_MODULE'] 			= 'Module';
$lang['L_TOUT_REMPLIR'] 	= 'Merci de mettre au moins un titre';

$lang['FORM_PAGE_TITRE'] 	= 'Edition msg citations';
$lang['FORM_AUTEUR'] 		= 'Auteur';
$lang['FORM_CATEGORIE'] 	= 'Catégorie';
$lang['FORM_BILLET'] 		= 'Billet';
$lang['FORM_SUBMIT'] 		= 'Envoyer';

$lang['sa_config'] 			= 'Configuration des petites annonces';
$lang['sa_rank_post'] 		= 'Niveau pour pouvoir poster';
$lang['sa_list_size'] 		= 'Taille de la liste pour menu';

$lang['sa_title'] 			= 'Petites annonces';
$lang['sa_title_all'] 		= 'Toutes les petites annonces';
$lang['sa_more_contents'] 	= '[Suite...]';
$lang['sa_mini_info']		= '%d annonce(s) depuis le %s';

$lang['sa_require_float']	= 'Entrer un nombre à virgule';
$lang['sa_require_upload']	= 'Les seuls formats gérés de fichier image sont : gif, png ou jpeg';
$lang['sa_no_smallads']		= 'Aucune petite annonce trouvée';
$lang['sa_edit_success']	= 'Modifications enregistrées';
$lang['sa_confirm_delete']	= 'Confirmer la suppression';
$lang['sa_confirm_delete_picture']	= 'Confirmer la suppression de la photo';
$lang['sa_created']			= 'Créé le : ';
$lang['sa_updated']			= 'Modifié le : ';
$lang['sa_list_not_approved'] = 'Lister PA non approuvées';

$lang['sa_db_type'] 		= 'Type';
$lang['sa_db_title'] 		= 'Titre';
$lang['sa_db_contents'] 	= 'Description';
$lang['sa_db_price'] 		= 'Prix';
$lang['sa_db_approved'] 	= 'Approuver la petite annonce';
$lang['sa_db_picture']		= 'Photo associée';
$lang['sa_db_max_weeks']	= 'Photo associée';

$lang['sa_group_all'] 		= 'Tout';
$lang['sa_group_1']			= 'Vend';
$lang['sa_group_2']			= 'Achète';
$lang['sa_group_3'] 		= 'Echange';
$lang['sa_group_4'] 		= ''; // vide si fin de liste
$lang['sa_group_5'] 		= '';
$lang['sa_group_6'] 		= '';
$lang['sa_group_7'] 		= '';
$lang['sa_group_8'] 		= '';
$lang['sa_group_9'] 		= '';

$lang['sa_price_unit']		= '&euro;';

$lang['sa_search_where'] 	= 'Rechercher dans ?';
$lang['sa_add_legend']		= 'Ajouter une petite annonce';
$lang['sa_update_legend']	= 'Modifier une petite annonce';
$lang['sa_view_legend']		= 'Voir une petite annonce';

$lang['sa_items_per_page']	= 'Nombre items maxi dans pagination';
$lang['sa_max_links'] 		= 'Nombre liens maxi dans pagination';
$lang['sa_list_size']		= 'Taille de la liste pour mini';
$lang['sa_maxlen_contents']	= 'Nombre Maxi de caractères dans contenu';
$lang['sa_max_weeks']		= 'Nombre de semaines d\'affichage';
$lang['sa_usage_terms']		= 'Afficher des conditions générales d\'utilisation';
$lang['sa_max_weeks_default'] = '(%d sem. si laissé vide)';

$lang['sa_auth_message']	= 'Sauf dans quelques cas signalés, Attribuer des droits sur les visiteurs sera ignoré';
$lang['sa_own_crud'] 		= 'Ajouter, Modifier, Supprimer ses propres petites annonces';
$lang['sa_update'] 			= 'Modifier les petites annonces<br />et approuver les contributions';
$lang['sa_delete'] 			= 'Supprimer les petites annonces';
$lang['sa_list']   			= 'Lister les petites annonces (visiteurs permis)';
$lang['sa_contrib'] 		= 'Ajouter, Modifier, Supprimer des contributions';

$lang['sa_sort_title']		= 'Titre';
$lang['sa_sort_date']		= 'Date';
$lang['sa_sort_price']		= 'Prix';

$lang['sa_mode_asc']		= 'Croissant';
$lang['sa_mode_desc']		= 'Décroissant';

$lang['sa_error_upload']		= 'Erreur durant chargement photo';
$lang['sa_unsupported_format']	= 'Format fichier image non supporté';
$lang['sa_unabled_create_pics']	= 'Impossible de créer les images';
$lang['sa_error_resize']		= 'Erreur fonction resize';
$lang['sa_error_resample']		= 'Erreur fonction resample';
$lang['sa_no_gd']				= 'Il manque l\'extension gd';
$lang['sa_no_getimagesize']		= 'Fonction getimagsize non trouvée';

$lang['sa_return_to_list']		= 'Revenir à la liste après une modification';
$lang['sa_view_mail']			= 'Afficher le lien vers mail';
$lang['sa_view_pm']				= 'Afficher le lien vers mp';

$lang['sa_contrib_in_progress']	= 'Contribution en cours de traitement. Recommencer plus tard';
$lang['sa_not_approved']		= 'Non approuvée à ce jour';

$lang['sa_xml_desc'] 			= 'Suivez les dernières Petites Annonces sur ';

$lang['sa_usage_legend']		= 'Conditions générales d\'utilisation';
$lang['sa_agree_terms']			= 'J\'accepte les conditions générales d\'utilisation';
$lang['sa_cgu_contents']		= 'Contenu de vos conditions générales d\'utilisation';

$lang['sa_e_cgu_invalid']		= 'You choose to disply general usage terms but the content is empty';
$lang['sa_e_cgu_file_invalid']	= 'Problème d\'accès au fichier des conditions générales';
$lang['sa_e_cgu_not_agreed']	= 'Vous n\'avez pas accepté les conditions générales';

$erreur[2100] = 'Exemple Exemple';
?>