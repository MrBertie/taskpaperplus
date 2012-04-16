<?php
/**
 * German language (Deutsch)
 * To change to this language use the memo bottom right
 */

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
$lang['filter_settings']    = array('unblockiert'  => array('*next | >week \\date', 'Unblockierte Aufgaben inklusive nächster Woche', 'yellow', true),
                                     'bald_fällig'  => array('*todo >month \\date', 'Fällig im nächsten Monat', 'green', true),
                                     'blockiert'  => array('*wait', 'Aufgaben wartend auf jemanden/etwas', 'cyan', true),
                                     'eventuell' => array('*maybe', 'Mögliche Aufgaben für die Zukunft', 'violet', true),
                                     'pendent'  => array('*todo', 'Alle pendenten Aufgaben', 'blue', true),
                                     'erledigt'  => array('*done', 'Nur erledigte Aufgaben', 'gray', true),
                                     'mit_Datum'   => array('*todo =date \\gdate', 'Pendente Aufgaben mit Datum', 'green', true),
                                     'überfällig'  => array('*todo <today \\gdate', 'Überfällige pendente Aufgaben', 'red', true),
                                    );

// search engine intervals and commands (English => Other Language)
$lang['interval_names']     = array('date' => 'Datum',
                                     'future' => 'zukünftig',
                                     'past' => 'vergangene',
                                     'yesterday' => 'gestern',
                                     'today' => 'heute',
                                     'tomorrow' =>'morgen',
                                     'day' => 'Tag',
                                     'week' => 'Woche',
                                     'month' => 'Monat',
                                     'year' => 'Jahr',
                                    );

// names of the various sorting "columns" (English => Other Language)
$lang['sort_names']         = array('task' => 'Aufgabe',
                                     'date' => 'Datum',
                                     'gdate' => 'Gruppierte Daten',    // grouped dates
                                     'topic' => 'Projekt',
                                     'state' => 'Status',
                                    );

// different states (sequence should not be changed) (English => Other Language)
$lang['state_names']        = array('todo' => 'zutun',
                                     'next' => 'unblockierte',
                                     'wait' => 'wartend',
                                     'maybe' => 'eventuell',
                                     'done' => 'erledigt',
                                    );

// 0=todo, 1=next, etc.. done should always be last!
$lang['state_order']        = array('todo', 'next', 'wait', 'maybe', 'done');
// colours used for various states (in order of use)
// currently: none, next, wait, maybe (done has no colour)
$lang['state_colours']      = array('none', 'yellow', 'cyan', 'violet', '');

// main headers and titles
$lang['projectless']        = '[Kein Projekt]';
$lang['tagless']            = 'Keine Tags';
$lang['task_header']        = 'Aufgaben';
$lang['tag_header']         = 'Tags';
$lang['project_header']     = 'Projekte';
$lang['filter_header']      = 'Filter';
$lang['search_header']      = 'Suche: ';
$lang['can_sort']           = 'Sortierbar';

// new tab sample content
$lang['new_tab_content']    = "Neues Projektt:\n- neue Aufgabe @tag\n... eine einfache Notiz";

// main toolbar buttons
$lang['edit_all_tip']       = 'Klick hier um den Text zu editieren';
$lang['archive_all_tip']    = 'Klick hier um alle abgeschlossenen Aufgaben zu archivieren';
$lang['remove_action_tip']  = 'Klick hier um alle Highlights von den Aufgaben zu entfernen';

// task related tips and buttons
$lang['mark_complete_tip']  = 'Klick um die Aufgabe als pendent/abgeschlossen zu markieren';
$lang['action_toggle_tip']  = 'Klick um zwischen den verschiedenen Aktionen zu wechseln: keine » pendent » wartend » eventuell';
$lang['archive_task_tip']   = 'Klick um diese Aufgabe zu archivieren';
$lang['delete_task_tip']    = 'Klick um diese Aufgabe zu löschen';
$lang['edit_in_place_tip']  = 'Doppelklicken um diese Aufgabe direkt zu editieren';
$lang['project_click_tip']  = 'Klick um nur dieses Projekt anzuzeigen';
$lang['tag_click_tip']      = 'Klick um nach diesem Tag zu filtern';
$lang['search_box_tip']     = "Gib Wörter, Tags, Kommandos oder Daten ein um danach zu suchen, oder gib eine neue Aufgabe ein; dann drück ENTER";
$lang['search_help_tip']    = "Brauchst Du Hilfe beim Suchen? Klick um die Kurzübersicht zu öffnen (Ctrl+click für eine neue Seite)";
$lang['startpage_tip']      = 'Zurück zur Standardansicht';
$lang['save_changes_tip']   = 'Speichere deine Änderungen';
$lang['cancel_changes_tip'] = 'Alle Änderungen verwerfen und zur Aufgabenansicht zurückkehren';
$lang['rename_tip']         = 'Klick um dieses Tab umzubenennen';
$lang['remove_tip']         = 'Klick um dieses Tab zu löschen';
$lang['add_tab_tip']        = 'Klick um ein neues Tab hinzuzufügen';
$lang['change_tab_tip']     = 'Klick um zu diesem Tab zu wechseln. Ungespeicherte Änderungen bleiben erhalten';
$lang['reset_tab_tip']      = 'Klick um dieses Tab zur Standardansicht zurückzusetzen';
$lang['archive_tab_tip']    = 'Klick um archivierte Aufgaben anzuzeigen';
$lang['trash_tab_tip']      = 'Klick um gelöschte Aufgaben anzuzeigen';
$lang['reveal_tip']         = "Klick um die Notiz anzuzeigen/zu verstecken";
$lang['clear_box_tip']      = 'Klick um die Suchbox zurückzusetzen';
$lang['sort_tip']           = '&#10; -ODER- Klick und ziehe um die Reihenfolge der Einträge zu ändern';

// general control labels
$lang['find_lbl']           = 'Suchen:';
$lang['replace_lbl']        = 'Ersetzen:';
$lang['help_lbl']           = 'Hilfe';
$lang['about_lbl']          = 'Über';
$lang['faq_lbl']            = 'FAQ';
$lang['website_lbl']        = 'Webseite';
$lang['go_lbl']             = 'Go';
$lang['save_lbl']           = 'Speichern';
$lang['cancel_lbl']         = 'Abbrechen';
$lang['placeholder']        = 'Erstell eine Aufgabe -ODER- Gib eine Suchanfrage ein...';

// used before date intervals in result interface
$lang['next_lbl']           = 'in den nächsten';
$lang['prev_lbl']           = 'in den letzten';
$lang['before_lbl']         = 'vor';
$lang['after_lbl']          = 'nach';
$lang['no_date_hdr']        = 'Kein Datum';

// used by javascript side to display messages
$lang['alert_messages']     = 'Aufgabe hinzugefügt|Aufgabe geändert|Aufgabe gelöscht|Aufgabe archiviert|Alle abgeschlossenen Aufgaben archiviert|' .
                              'Neuer Name für dieses Tab?|Dieses Tab löschen?|Name für das neue Tab?|' .
                              'Applizier gewünschte Änderungen und klick Speichern oder Abbrechen|' .
                              $lang['save_lbl'] . '|' . $lang['cancel_lbl'];
?>
