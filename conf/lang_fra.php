<?php
/**
 * French language
 * To change to this language change to 'language=fra' in conf/config.ini file
 */

$lang = array();

 /**
 * These filters can be changes to suit your needs/whims!
 *
 * each filter consists of: name => (1. expression, 2. tooltip, 3. colour, 4.visibility)
 * name:       this will be displayed to the user (no spaces allowed, however _ will be changed to space on display)
 * expression: any valid search as used in search box (see help file), multiple terms are supported
 *             the expression can use either the language specific commands/intervals as above, or english (more consistent)
 *             you can even reuse other filter to create a new one, just put the = in front, e.g. '=late'
 * tooltip:    this will pop up when you hover the mouse over the filter, to explain it's purpose
 * colour:     identifies which CSS class to use (CSS class name suffix, see '.filter-' in style.css)
 *             currently valid colours are: blue, brown, cyan, gray, green, red, violet, yellow  (all soft pastel shades)
 * visible:    should this filter be added to Filter sidebar (true),
 *             or just be available from the search box, or used in other filters (false)
 */
$lang['filter_settings']    = array('suivante'  => array('*next | >week \\date', 'Les actions suivantes, et la prochaine semaine', 'yellow', true),
                                     'à_bientôt'  => array('*todo >month \\date', 'Toutes tâches à faire, dans le prochain mois', 'green', true),
                                     'en_attente'  => array('*wait', 'Toutes tâches en attente', 'cyan', true),
                                     'peut-être' => array('*maybe', 'Peut-être, un de ces jours', 'violet', true),
                                     'à_faire'  => array('*todo', 'Toutes tâches encore à faire', 'blue', true),
                                     'accompli'  => array('*done', 'Toutes tâches accomplies', 'gray', true),
                                     'dû'   => array('*todo =date \\gdate', "Toutes tâches à faire, avec date", 'green', true),
                                     'tard'  => array('*todo <today \\gdate', "Toutes tâches à faire, avec date dans le passé", 'red', true),
                                    );
// search engine intervals and commands
$lang['interval_names']     = array('date' => 'date',
                                     'future' => 'futur',
                                     'past' => 'passé',
                                     'yesterday' => 'hier',
                                     'today' => "aujourd'hui",
                                     'tomorrow' => 'demain',
                                     'day' => 'jour',
                                     'week' => 'semaine',
                                     'month' => 'mois',
                                     'year' => 'année',
                                    );

// names of the various sorting "columns" (English => Other Language)
$lang['sort_names']         = array('task' => 'tâche',
                                     'date' => 'date',
                                     'gdate' => 'gdate',    // grouped dates
                                     'topic' => 'sujet',
                                     'state' => 'état',
                                    );

// different states (todo, done should not be changed) (English => Other Language)
$lang['state_names']        = array('todo' => 'àfaire',
                                     'next' => 'suivante',
                                     'wait' => 'attends',
                                     'maybe' => 'peutêtre',
                                     'done' => 'fait',
                                    );

// 0=todo, 1=next, etc.. done should always be last!
$lang['state_order']        = array('àfaire', 'suivante', 'attends', 'peutêtre', 'fait');
// colours used for various states (in order of use)
// currently: none, next, wait, maybe (done has no colour)
$lang['state_colours']      = array('none', 'yellow', 'cyan', 'violet', '');


// main headers and titles
$lang['projectless']        = '(Pas de sujet)';
$lang['no_tags']            = 'Pas de tag';
$lang['task_header']        = 'Tâches';
$lang['tag_header']         = 'Tags';
$lang['project_header']     = 'Sujets';
$lang['filter_header']      = 'Filtres';
$lang['search_header']      = 'Recherche: ';
$lang['can_sort']           = 'Déplaçable';

// new tab sample content
$lang['new_tab_content']    = "Nouveau sujet:\n- nouvelle tâche";

// main toolbar buttons
$lang['edit_all_tip']       = "Cliquez ici pour éditer l'onglet en texte brut";
$lang['archive_all_tip']    = 'Cliquez pour archiver toutes les tâches accomplies';
$lang['remove_action_tip']  = "Cliquez pour supprimer l''etat des tâches";

// task related tips and buttons
$lang['mark_complete_tip']  = 'Cliquez pour marquer cette tâche comme faite ou pas';
$lang['action_toggle_tip']  = "Cliquez afin de changer l'êtat de cette tâche";
$lang['archive_task_tip']   = 'Cliquez pour archiver cette tâche';
$lang['delete_task_tip']    = 'Cliquez pour effacer cette tâche';
$lang['edit_in_place_tip']  = 'Double-cliquez pour modifier cette tâche';
$lang['project_click_tip']  = 'Afficher seulement ce sujet';
$lang['tag_click_tip']      = 'Cliquez pour filtrer par ce tag';
$lang['search_box_tip']     = "Tapez les mots-clés, les tags, les commandes ou les dates à rechercher, ou tapez une nouvelle tâche, puis appuyez sur ENTRÉE.";
$lang['search_help_tip']    = "Need help searching? Click for a cheatsheet (Ctrl+click for a new page)";
$lang['startpage_tip']      = 'Retournez à la vue tâche par défaut';
$lang['save_changes_tip']   = 'Enregistrez vos modifications';
$lang['cancel_changes_tip'] = 'Annulez vos modifications et retournez à la vue tâche';
$lang['rename_tip']         = "Cliquez pour renommer l'onglet";
$lang['remove_tip']         = "Cliquez pour supprimer l'onglet";
$lang['add_tab_tip']        = 'Cliquez pour ajouter un nouvel onglet';
$lang['change_tab_tip']     = 'Cliquez pour sélectionner cet onglet. Toutes les modifications non enregistrées seront conservées';
$lang['reset_tab_tip']      = "Cliquez pour réinitialiser l'onglet à la vue par défaut";
$lang['archive_tab_tip']    = 'Click to view archived tasks';
$lang['trash_tab_tip']      = 'Click to view deleted tasks';
$lang['reveal_tip']         = 'Cliquez pour afficher / cacher le bloc note';
$lang['clear_box_tip']      = 'Click to clear the search box';

// labels
$lang['find_lbl']           = 'Trouver:';
$lang['replace_lbl']        = 'Remplacer:';
$lang['help_lbl']           = 'Aide';
$lang['about_lbl']          = 'À Propos';
$lang['faq_lbl']            = 'FAQ';
$lang['website_lbl']        = 'Site Web';
$lang['go_lbl']             = 'Aller';
$lang['save_lbl']           = 'Enregistrer';
$lang['cancel_lbl']         = 'Annuler';
$lang['placeholder']        = 'Tapez une recherche ou ajouter une nouvelle tâche, puis ENTRÉE';

// used before date intervals in result interface
$lang['next_lbl']           = 'à venir';
$lang['prev_lbl']           = 'passé';
$lang['before_lbl']         = 'avant';
$lang['after_lbl']          = 'après';
$lang['no_date_hdr']        = 'Pas de date';

// used by javascript side to display messages
$lang['alert_messages']     = 'Tâche ajoutée|Tâche modifiée|Tâche effacée|Alterner suivi|Tâche archivée|Toutes les tâches accomplies ont été archivées|' .
                              'Quel est le nouveau nom pour cet onglet?|Supprimer cet onglet|Quel est le nom du nouvel onglet?|' .
                              'Faites vos modifications et cliquez sur Enregistrer ou Annuler|' .
                              $lang['save_lbl'] . '|' . $lang['cancel_lbl'];
?>
