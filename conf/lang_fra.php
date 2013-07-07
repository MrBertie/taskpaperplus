<?php
namespace tpp;
global $lang;

/**
 * Français / FRENCH language
 */


/**
 * These filters can be changes to suit your needs/whims!
 *
 * Each filter consists of: name => array(expression, tooltip, colour, visibility),
 * 
 * name:       
 *             this will be displayed to the user (no spaces allowed, however _ will be replced by space for display)
 * expression: 
 *             any valid search as used in search box (see help file), multiple terms are supported
 *             the expression can use either the language specific commands/intervals as below, or english (more consistent)
 *             you can even reuse other filter to create a new one, just put the = in front, e.g. '=late'
 * tooltip:    
 *             this will pop up when you hover the mouse over the filter, to explain it's purpose
 * colour:     
 *             identifies which CSS class to use (CSS class name suffix, see '.bk-...' in style.css)
 *             currently valid colours are: blue, brown, cyan, gray, green, red, violet, yellow  (all soft pastel shades)
 * visible:    
 *             should this filter be added to Filter sidebar (true),
 *             or just be available from the search box, or used in other filters (false)
 */
$lang['filter_settings']    = array(
                                    'suivante'    => array('*next | >week \\date', 'Les actions suivantes, et la prochaine semaine', 'yellow', true),
                                    'à_bientôt'   => array('*todo >month \\date', 'Toutes tâches à faire, dans le prochain mois', 'green', true),
                                    'en_attente'  => array('*wait', 'Toutes tâches en attente', 'cyan', true),
                                    'peutêtre'   => array('*maybe', 'Peut-être, un de ces jours', 'violet', true),
                                    'à_faire'     => array('*todo', 'Toutes tâches encore à faire', 'blue', true),
                                    'accompli'    => array('*done', 'Toutes tâches accomplies', 'gray', true),
                                    'dû'          => array('*todo =date \\date', "Toutes tâches à faire, avec date", 'green', true),
                                    'tard'        => array('*todo <today \\date', "Toutes tâches à faire, avec date dans le passé", 'red', true),
                                    );

// search engine intervals and commands
$lang['interval_names']     = array(
                                    'date'      => 'date',
                                    'future'    => 'futur',
                                    'past'      => 'passé',
                                    'yesterday' => 'hier',
                                    'today'     => "aujourd'hui",
                                    'tomorrow'  => 'demain',
                                    'day'       => 'jour',
                                    'week'      => 'semaine',
                                    'month'     => 'mois',
                                    'year'      => 'année',
                                    );

// names of the various sorting "columns" (English => Other Language)
$lang['sort_names']         = array(
                                    'task'  => 'tâche',
                                    'date'  => 'date',
                                    'topic' => 'sujet',
                                    'state' => 'état',
                                    );

// different states (todo, done should not be changed) (English => Other Language)
$lang['state_names']        = array(
                                    'done'  => 'fait',
                                    'todo'  => 'àfaire',
                                    'next'  => 'suivante',
                                    'wait'  => 'attends',
                                    'maybe' => 'peutêtre',
                                    );

// 0=done, 1=todo, etc.. done should always be first!
// REMAINS IN ENGLISH !!
$lang['state_order']        = array('done', 'todo', 'next', 'wait', 'maybe');

// colours used for various states (in order of use)
// currently: none, next, wait, maybe (done has no colour)
// REMAINS IN ENGLISH !!
$lang['state_colours']      = array('none', 'yellow', 'cyan', 'violet');


// main headers and titles
$lang['orphaned']           = '(Pas de sujet)';
$lang['no_tags']            = 'Pas de tag';
$lang['task_header']        = 'Tâches';
$lang['tag_header']         = 'Tags';
$lang['project_header']     = 'Sujets';
$lang['filter_header']      = 'Filtres';
$lang['search_header']      = 'Recherche: ';


// new tab sample content
$lang['new_tab_content']    = "Nouveau sujet:\n- nouvelle tâche #tag\n    petit note";


// main toolbar buttons
$lang['edit_all_tip']       = "Éditer l'onglet en texte brut";
$lang['archive_done_tip']   = 'Archiver toutes les tâches accomplies';
$lang['trash_done_tip']     = 'Supprimer toutes les tâches accomplies';
$lang['remove_actions_tip'] = "Supprimer l'etat des tâches";


// task related tips and buttons
$lang['search_box_tip']     = "Saisissez les mots-clés, les tags, les commandes ou les dates à rechercher, ou tapez une nouvelle tâche, puis appuyez sur ENTRÉE.";
$lang['search_help_tip']    = "Comment faire une recherche? Cliquez pour ouvrir un aide-mémoire (Ctrl+clique pour ouvrir une nouvelle page)";
$lang['startpage_tip']      = 'Retournez à la vue tâche par défaut';
$lang['save_changes_tip']   = 'Enregistrez vos modifications';
$lang['cancel_changes_tip'] = 'Annulez vos modifications et retournez à la vue tâche';
$lang['rename_tip']         = "Renommer l'onglet";
$lang['remove_tip']         = "Supprimer l'onglet";
$lang['new_tab_tip']        = 'Ajouter un nouvel onglet';
$lang['change_tab_tip']     = 'Sélectionner cet onglet. Toutes les modifications non enregistrées seront conservées';
$lang['reset_tab_tip']      = "Réinitialiser l'onglet à la vue par défaut";
$lang['archive_tab_tip']    = 'Toutes tâches accomplies ';
$lang['trash_tab_tip']      = 'Toutes tâches supprimées';
$lang['clear_box_tip']      = 'Réinitialiser le champ de recherche';
$lang['tag_click_tip']      = 'Filtrer par ce tag';
$lang['sortable_tip']       = 'Déplaçable';


// sent with task-buttons
$lang['action_button_tip']  = "Changer l'êtat de cette tâche";
$lang['archive_button_tip'] = "Archiver cette tâche";
$lang['trash_button_tip']   = "Effacer cette tâche";


// general control labels
$lang['find_lbl']           = 'Trouver:';
$lang['replace_lbl']        = 'Remplacer:';
$lang['help_lbl']           = 'Aide';
$lang['about_lbl']          = 'À Propos';
$lang['faq_lbl']            = 'FAQ';
$lang['website_lbl']        = 'Site Web';
$lang['go_lbl']             = 'Aller';
$lang['save_lbl']           = 'Enregistrer';
$lang['cancel_lbl']         = 'Annuler';
$lang['trash_lbl']          = 'Poubelle';
$lang['archive_lbl']        = 'Archive';
$lang['placeholder']        = 'Saisissez une recherche ou ajouter une nouvelle tâche, puis ENTRÉE';
$lang['language']           = 'Langue';


// used before date intervals in result interface
$lang['next_lbl']           = 'à venir';
$lang['prev_lbl']           = 'passé';
$lang['before_lbl']         = 'avant';
$lang['after_lbl']          = 'après';
$lang['no_date_hdr']        = 'Pas de date';


// miscellaneous
$lang['deleted_lbl']        = 'Effacé:';


// login general form labels
$lang['username_lbl']         = "nom d'utilisateur";
$lang['email_lbl']            = 'adresse e-mail';
$lang['password_lbl']         = 'mot de passe';
$lang['repeatpassword_lbl']   = 'répéter le mot de passe';
$lang['login_lbl']            = 'Connexion';
$lang['register_lbl']         = "S'Inscrire";
$lang['forgotpassword_lbl']   = 'Mot de passe oublié';
$lang['logout_lbl']           = 'Déconnexion';
$lang['logged_in_as_lbl']     = 'Connexion:';

// login allowed pattern decription
$lang['username_pattern']     = 'Admis: 0-9, a-z, A-Z, longueur: 2-32 caractères';
$lang['password_pattern']     = 'Admis: 0-9, a-z, A-Z, longueur: 2-32 caractèresAllowed: 0-9, a-z, A-Z; Length: 2-32 chars';


// login msgs
$lang['login_msg']               = 'Identifiez-vous ou inscrivez-vous ci-dessousLogin or Register below';
$lang['registration_msg']        = 'Inscription réussi</br>Maintenant vous connecter ci-dessous';
$lang['login_failed_msg']        = "Connexion échouer</br>Corriger les erreurs et d'essayer à nouveau";
$lang['registration_failed_msg'] = "Inscription échouer</br>Corriger les erreurs et d'essayer à nouveau";


// login errors
$lang['no_such_user_err']       = "Ce nom d'utilisateur n'existe pas</br>Vous vous étés inscrit?";
$lang['user_exists_err']        = "Nom d'utilisateur déjà pris";
$lang['invalid_username_err']   = "Nom d'utilisateur n'est pas valable</br>(" . $lang['username_pattern'] . ')';
$lang['invalid_password_err']   = "Mot de passe n'est pas valabled</br>(" . $lang['password_pattern'] . ')';
$lang['nonmatch_passwords_err'] = 'Deuxième mot de passe ne correspond pas à la première';
$lang['invalid_email_err']      = "Votre adresse e-mail n'est pas valable";
$lang['userfile_missing_err']   = 'Le fichier contenant la liste des utilisateurs manquait, un nouveau a été créé';


// ****************
// ** JAVASCRIPT **
// ****************


// colours are based on bk-* class colours in style.less
$jslang['add_msg']            = array('Tâche ajoutée', 'blue');
$jslang['edit_msg']           = array('Tâche modifiée', 'yellow');
$jslang['trash_msg']          = array('Tâche effacée', 'red');
$jslang['arch_msg']           = array('Tâche archivée', 'orange');
$jslang['all_trash_msg']      = array('Toutes les tâches accomplies ont été effacées', 'orange');
$jslang['all_arch_msg']       = array('Toutes les tâches accomplies ont été archivées', 'orange');

$jslang['rename_msg']         = 'Quel est le nouveau nom pour cet onglet?';
$jslang['remove_msg']         = 'Supprimer cet onglet';
$jslang['create_msg']         = 'Quel est le nom du nouvel onglet?';
$jslang['search_msg']         = '';   // currently unused
$jslang['lang_change_msg']    = 'Nouvelle langue sélectionnée! Rechargement de la page...';

$jslang['editable_tip']       = 'Faites vos modifications et cliquez sur Enregistrer ou Annuler';
$jslang['save_tip']           = $lang['save_lbl'];
$jslang['cancel_tip']         = $lang['cancel_lbl'];

$jslang['tag_click_tip']      = $lang['tag_click_tip'];
$jslang['edit_in_place_tip']  = 'Double-cliquez pour modifier cette tâche sur place';
$jslang['project_click_tip']  = 'Afficher seulement ce sujet';
$jslang['mark_complete_tip']  = 'Marquer cette tâche comme faite ou pas';
$jslang['reveal_tip']         = "Afficher / Cacher le bloc notee";
$jslang['sort_tip']           = "&#10; -ou- glisser et déplacer des tâches pour changer l'ordre";