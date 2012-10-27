<?php
$lang['L_ACCUEIL'] = 'Accueil';
$lang['L_LIBELLE_LICENCE'] = 'Acceptation de la Licence';
$lang['L_LIBELLE_ETAPE1'] = '1: Versions des Composants';
$lang['L_LIBELLE_ETAPE2'] = '2: Droits sur les Fichiers';
$lang['L_LIBELLE_ETAPE3'] = '3: Saisie des Param&egrave;tres';
$lang['L_LIBELLE_ETAPE4'] = '4: Cr&eacute;ation du config.php';
$lang['L_LIBELLE_ETAPE5'] = '5: Cr&eacute;ation de la Base';
$lang['L_LIBELLE_ETAPE6'] = '6: Cr&eacute;ation du Fondateur';
$lang['L_LIBELLE_ETAPE7'] = '7: Installation des Modules';
$lang['L_LIBELLE_ETAPE8'] = '8: Suppression du dossier Install';


// Accueil
$lang['L_TITRE_SITE'] = 'Installation de Malleo';
$lang['L_INSTALL'] = 'Accueil de l\'installation';
$lang['L_VERIFICATION_VERSIONS'] = 'V&eacute;rification des Versions';
$lang['L_INSTALLATION_MALLEO'] = 'Bienvenue sur Malleo';
$lang['L_PRESENTATION_MALLEO'] = 'Vous avez choisi d\'installer le gestionnaire de contenu Malleo et nous vous remercions de votre confiance.<br/> Cette proc&eacute;dure d\'installation va vous guider dans la v&eacute;rification de votre serveur Web et l\'installation de votre syst&egrave;me de base. Si vous &ecirc;tes arriv&eacute; sur cette page c\'est que vous avez s&ucirc;rement d&eacute;j&agrave; lu <a href="http://www.malleo-cms.com/index.php?module=wiki&t=Prerequis_pour_installer_Malleo" target="_blank">les Pr&eacute;requis d\'installation</a>, si ce n\'est pas le cas je vous invite &agrave; le faire avant d\'aller plus loin.';
$lang['L_PRESENTATION_LICENCE'] = '<b>Pr&eacute;requis:</b> Acceptation de la licence et des conditions d\'utilisation de Malleo';
$lang['L_PRESENTATION_ETAPE1'] = '<b>&eacute;tape 1:</b> V&eacute;rification des versions des composants';
$lang['L_PRESENTATION_ETAPE2'] = '<b>&eacute;tape 2:</b> V&eacute;rification des droits en &eacute;criture sur les dossiers';
$lang['L_PRESENTATION_ETAPE3'] = '<b>&eacute;tape 3:</b> Saisie des param&egrave;tres de connexion &agrave; votre base de donn&eacute;es';
$lang['L_PRESENTATION_ETAPE4'] = '<b>&eacute;tape 4:</b> Cr&eacute;ation du fichier de configuration';
$lang['L_PRESENTATION_ETAPE5'] = '<b>&eacute;tape 5:</b> Cr&eacute;ation de la base de donn&eacute;es';
$lang['L_PRESENTATION_ETAPE6'] = '<b>&eacute;tape 6:</b> Cr&eacute;ation du compte fondateur';
$lang['L_PRESENTATION_ETAPE7'] = '<b>&eacute;tape 7:</b> Installation des modules';
$lang['L_PRESENTATION_ETAPE8'] = '<b>&eacute;tape 8:</b> Suppression du dossier install';

// etape 0
$lang['L_VERSION_FR'] = 'Version Fran&ccedil;aise';
$lang['L_VERSION_EN'] = 'English Version';
$lang['L_CHECK_LICENCE'] = 'J\'ai lu, je comprends et j\'acceptes de respecter la licence CECILL v2 appliqu&eacute;e &agrave; Malleo';
$lang['L_EXPLICATION_LICENCE'] = 'Malleo est distribu&eacute; sous la licence CECILL V2, il est &agrave; noter que les modules, blocs et styles inclus dans ce pack t&eacute;l&eacute;charg&eacute; sur <a href="http://www.malleo-cms.com">http://www.malleo-cms.com</a> sont soumis &agrave; cette licence. <br /><br />Tous les blocs, modules et styles cr&eacute;&eacute;s ou adapt&eacute;s pour Malleo ne sont pas automatiquement distribu&eacute;s sous la licence CECILL V2. Je vous invite &agrave; rester vigilent quant au mode de distribution choisi par ses auteurs.<br/> Afin de v&eacute;rifier la licence d\'un &eacute;l&eacute;ment vous avez deux mani&egrave;res de proc&eacute;der: en vous rendant sur le site de l\'auteur, ou en &eacute;ditant les fichiers .php, les mentions l&eacute;gales &eacute;tant g&eacute;n&eacute;ralement indiqu&eacute;es en t&ecirc;te du fichier.';


// etape 1
$lang['L_EXPLAIN_VERIF_VERSIONS'] = 'Malleo n&eacute;cessite PHP5 et MySQL4.1/5 pour fonctionner. Il a besoin &eacute;galement de diff&eacute;rents plugins install&eacute;s.<br/> Un test automatis&eacute; va tenter de d&eacute;tecter leur pr&eacute;sence. Si certains composants ne sont pas valid&eacute;s je vous invite &agrave; vous rendre sur <a href="http://www.malleo-cms.com/index.php?module=wiki&t=Extensions_PHP" target="_blank">cette page</a> pour obtenir plus de renseignements.';
$lang['L_VERSIONS'] = 'Versions';
$lang['L_EXTENSIONS'] = 'Extensions';
$lang['L_FONCTIONS'] = 'Fonctions';
$lang['PHP'] = 'PHP: %s';
$lang['MySQL'] = 'MySQL: %s';
$lang['L_MySQL_NON_INSTALLE'] = 'non install&eacute;/configur&eacute; sur votre serveur PHP';
$lang['L_EXT_EXPLAIN_gd'] = 'GD2 <i>(librairie graphique permettant de manipuler les images)</i>';
$lang['L_EXT_EXPLAIN_mbstring'] = 'MbString <i>(librairie permettant d\'encoder dans divers formats les caract&egrave;res)</i>';
$lang['L_EXT_EXPLAIN_mysql'] = 'MySQL <i>(Base de donn&eacute;es simple et gratuite permettant de sauvegarder vos donn&eacute;es)</i>';
$lang['L_EXT_EXPLAIN_sockets'] = 'Sockets <i>(Librairie permettant de cr&eacute;er des connexions avec des sites)</i>';
$lang['L_EXT_EXPLAIN_dom'] = 'Dom <i>(Librairie permettant de manipuler le contenu de fichiers XML)</i>';
$lang['L_FSOCKOPEN_NON_INSTALLE'] = 'FSockOpen <i>(Fonction PHP permettant de v&eacute;rifier les versions des modules, mais aussi de t&eacute;l&eacute;charger certaines images. Cette fonction n\'est pas indispensable pour faire fonctionner Malleo, mais certains processus ne seront pas fonctionnels)</i>';


// etape 2
$lang['L_VERIFICATION_CHMODS'] = 'V&eacute;rification des Droits sur les dossiers';
$lang['L_EXPLAIN_VERIF_CHMODS'] = 'Parfois votre site web aura besoin d\'&eacute;crire des donn&eacute;es dans des dossiers. Des espaces d&eacute;di&eacute;s en &eacute;criture doivent donc lui &ecirc;tre am&eacute;nag&eacute;s pour que celui-ci puisse fonctionner correctement. Il est indispensable que tous les dossiers et fichiers indiqu&eacute;s ci-dessous soient inscriptibles.<br /><br />Pour se faire, vous devez utiliser votre logiciel FTP, faire un clic droit sur les fichiers cit&eacute;s, aller dans les propri&eacute;t&eacute;s ou les attributs et fixer la valeur chiffr&eacute;e &agrave; 777, ou 0777 si la valeur attendue est sur 4 chiffres.<br /><br />Une fois l\'installation totalement termin&eacute;e, il est recommend&eacute; de baisser les droits de config/config.php &agrave; 640.';
$lang['L_LISTE_DOSSIERS'] = 'liste des dossiers';


// etape 3
$lang['L_SAISIE_PARAMETRES'] = 'Saisie des param&egrave;tres MySQL';
$lang['L_EXPLAIN_MySQL'] = 'Afin de remplir votre base de donn&eacute;es, vous devez fournir &agrave; Malleo les param&egrave;tres de connexion &agrave; votre base de donn&eacute;es. Ces param&egrave;tres sont fournis par votre h&eacute;bergeur. Contactez sa FAQ pour savoir comment obtenir ces informations.<br />Le test de connexion peut parfois &ecirc;tre n&eacute;gatif alors que les param&egrave;tres saisis sont bons en raison d\'une lenteur de connexion, n\'h&eacute;sitez pas &agrave; r&eacute;essayer.';
$lang['L_ADRESSE_BASE_DONNEES'] = 'Adresse de la base de donn&eacute;es';
$lang['L_NOM_BASE_DONNEES'] = 'Nom de la base de donn&eacute;es';
$lang['L_NOM_UTILISATEUR'] = 'Nom de l\'utilisateur';
$lang['L_MOT_DE_PASSE'] = 'Mot de passe de l\'utilisateur';
$lang['L_SAISISSEZ_PARAMETRES'] = 'Saisissez les param&egrave;tres ci-dessous:';
$lang['L_ALERTE_CORRECTION'] = 'Veuillez corriger les erreurs ci-dessus, une fois corrig&eacute;es relancez cette page l\'installation reprendra.';
$lang['L_TESTER_ACCES_BASE'] = 'Tester l\'acc&egrave;s';
$lang['L_PATIENTER'] = 'Veuillez patienter ...';

// etape 4
$lang['L_CREATION_CONFIG'] = 'Cr&eacute;ation du Fichier config.php';
$lang['L_EXPLAIN_FICHIER_CONFIG'] = 'Un fichier config.php a &eacute;t&eacute; cr&eacute;&eacute; dans le dossier config/. Il contient vos param&egrave;tres d\'acc&egrave;s &agrave; votre base de donn&eacute;es. Si vous changez un jour d\'h&eacute;bergeur vous devrez penser &agrave; modifier ces param&egrave;tres.';
$lang['L_EXPLAIN_FICHIER_CONFIG_NOK'] = 'Impossible d\'enregistrer le fichier config/config.php. Vous allez devoir cr&eacute;er vous m&ecirc;me le fichier en recopiant le code ci-dessous.';

// etape 5
$lang['L_CREATION_BASE'] = 'Cr&eacute;ation de la Base de Donn&eacute;es';
$lang['L_LISTE_TABLES_CREEES'] = 'Liste des requ&ecirc;tes SQL effectu&eacute;es';
$lang['L_EXPLAIN_CREATION_TABLES'] = 'Votre base de donn&eacute;es &agrave; &eacute;t&eacute; cr&eacute;&eacute; ou mise &agrave; niveau vers la version sup&eacute;rieure. Si vous avez plusieurs versions de retard, veuillez recharger cette page jusqu\'&agrave;  ce que vous soyez dans la derni&egrave;re version disponible.<br />Si des erreurs surviennent rendez vous sur le <a href="http://www.malleo-cms.com/index.php?module=forum&mode=forum&id_forum=5" target="_blank">forum d\'installation de Malleo</a>, on vous fournira les corrections &agrave; effectuer.';
$lang['L_AUCUNE_REQUETE'] = 'Aucune requ&ecirc;te &agrave; effectuer, vous disposez s&ucirc;rement d&eacute;j&agrave; de la derni&egrave;re version existante.';

// etape 6
$lang['L_EXPLAIN_FONDATEUR'] = 'Le Fondateur est le compte de plus haut niveau sur votre site, il a tous les droits sur tous les modules et il est le seul &agrave; pouvoir acc&eacute;der &agrave; la zone d\'administration; mais &eacute;galement le seul qui peut promouvoir un membre au status de Fondateur ou Administrateur.';
$lang['L_CREATION_FONDATEUR'] = 'Cr&eacute;ation du compte Fondateur';
$lang['L_LOGIN'] = 'Pseudo';
$lang['L_MAIL'] = 'Email';
$lang['L_MDP'] = 'Mot de Passe';
$lang['L_CREER_COMPTE'] = 'Cr&eacute;er le compte';
$lang['L_COMPTE_CREE'] = 'Votre compte a &eacute;t&eacute; cr&eacute;&eacute;';
$lang['L_LEGEND_PSEUDO'] = '<i>Lettres, Chiffres, Caract&egrave;res: - _ et @, Maximum 30 caract&egrave;res</i>';

// etape 7
$lang['L_INSTALLATION_MODULES'] = 'Installation des Modules';
$lang['L_EXPLAIN_MODULES'] = 'Des Modules ont &eacute;t&eacute; d&eacute;tect&eacute;s dans le dossier plugins/modules/, ils viennent d\' &ecirc;tre automatiquement install&eacute;s sur votre site. Il ne vous reste plus qu\'&agrave;  s&eacute;lectionner votre nouveau module par d&eacute;faut, qui deviendra la page d\'accueil de votre site.';
$lang['L_MODULES'] = 'Modules';
$lang['L_DEFINIR_DEFAUT'] = 'D&eacute;finir par d&eacute;faut';

// etape 8
$lang['L_SUPPRESSION_DOSSIER'] = 'Suppression du dossier d\'installation';
$lang['L_EXPLAIN_SUPPRESSION_DOSSIER'] = 'L\'installation est termin&eacute;e, vous pouvez supprimer ce dossier et vous connecter &agrave; votre interface d\'administration.<br /><br />Une fois le dossier supprim&eacute;, <a href="../login.php">cliquez ici pour vous connecter</a>';
?>