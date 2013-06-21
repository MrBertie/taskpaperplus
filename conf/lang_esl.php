<?php
namespace tpp;

/**
 * Espangol / SPANISH language
 */

$lang = array();


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
                                    'siguiente'  => array('*next | >week \\date', 'Siguiente tarea mas siguiente semana', 'yellow', true),
                                    'pronto'     => array('*todo >month \\date', 'Durante los proximos meses', 'green', true),
                                    'en_espera'  => array('*wait', 'Tarea esperando a alguien/algo', 'cyan', true),
                                    'quizás'     => array('*maybe', 'Tarea para haceralguna vez, quizas', 'violet', true),
                                    'por_hacer'  => array('*todo', 'Todas las tareas sin completar', 'blue', true),
                                    'hecho'      => array('*done', 'Solo tareas completadas', 'gray', true),
                                    'esperado'   => array('*todo =date \\date', 'Tareas sin completar con fecha', 'green', true),
                                    'tarde'      => array('*todo <today \\date', 'Tareas Incompletas con fehca pasada', 'red', true),
                                    );

// search engine intervals and commands (English => Other Language)
$lang['interval_names']     = array(
                                    'day'       => 'dia',
                                    'week'      => 'semana',
                                    'month'     => 'mes',
                                    'year'      => 'año',
                                    'yesterday' => 'ayer',
                                    'today'     => 'hoy',
                                    'tomorrow'  =>'mañana',
                                    'future'    => 'futuro',
                                    'past'      => 'pasado',
                                    'date'      => 'fecha',
                                    );

// names of the various sorting "columns" (English => Other Language)
$lang['sort_names']         = array(
                                    'task'  => 'tarea',
                                    'date'  => 'fecha',
                                    'topic' => 'asunto',
                                    'state' => 'estado',
                                    );

// different states (English => Other Language)
$lang['state_names']        = array(
                                    'done'  => 'hecho',
                                    'todo'  => 'por hacer',
                                    'next'  => 'siguiente',
                                    'wait'  => 'en espera',
                                    'maybe' => 'quizás',
                                    );

// 0=done, 1=todo, etc.. done should always be first!
// REMAINS IN ENGLISH !!
$lang['state_order']        = array('done', 'todo', 'next', 'wait', 'maybe');

// colours used for various actions (in order of use)
// currently: none, next, wait, maybe (done has no colour)
// REMAINS IN ENGLISH !!
$lang['state_colours']      = array('none', 'yellow', 'cyan', 'violet');


// main headers and titles
$lang['orphaned']           = '[Sin Asunto]';
$lang['no_tags']            = 'Sin Tags';
$lang['task_header']        = 'Tareas';
$lang['tag_header']         = 'Tags';
$lang['project_header']     = 'Asuntos';
$lang['filter_header']      = 'Filtros';
$lang['search_header']      = 'Busquedas: ';
$lang['sortable']           = 'Ordenar';


// new tab sample content
$lang['new_tab_content']    = "Nuevo Proyecto:\n- Nueva Tarea #tag\n    Nota Simple";


// main toolbar buttons
$lang['edit_all_tip']       = 'Editar la pesta;a como texto plano';
$lang['archive_done_tip']   = 'Archivar todas las tareas completadas';
$lang['trash_done_tip']     = 'Enviar a la papelera todas las tareas completadas';
$lang['remove_actions_tip'] = 'Quitar el Subrayado de todas las tareas';


// task related tips and buttons
$lang['search_box_tip']     = "Escribe palabras, tags, filtros, o fechas para buscar, pulsa [ENTER]\nO escribe una nueva tarea pulsa [Ctrl+Enter]\n[Atajo: Alt+Shift+C]";
$lang['search_help_tip']    = "Necesitas ayuda buscando? Pulsa por hoja de trucos (Ctrl+click para una nueva página)";
$lang['startpage_tip']      = 'Volever a la vista por defecto';
$lang['save_changes_tip']   = 'Guardar los cambios';
$lang['cancel_changes_tip'] = 'Cancelar cuanlquiercambio y volver aq la lista de tareas';
$lang['rename_tip']         = 'Renombrar esta pestaña';
$lang['remove_tip']         = 'Borrar esta pestaña';
$lang['new_tab_tip']        = 'Añadir nueva pestaña';
$lang['change_tab_tip']     = 'Cambiar a esta pestaña. Cualquier edicion sin salvar sera guardada';
$lang['reset_tab_tip']      = 'Resetea esta petaña y vuelve a la vista por defecto';
$lang['archive_tab_tip']    = 'Todas las tareas archivadas';
$lang['trash_tab_tip']      = 'Todas las tareas borradas';
$lang['clear_box_tip']      = 'Limpia el campo busqueda';
$lang['tag_click_tip']      = 'Filtra este tag';


// sent with task-buttons
$lang['action_button_tip']  = 'Cambia entre diferentes acciones: Nada » Siguiente » En Espera » Quizas';
$lang['archive_button_tip'] = 'Archivar esta tarea';
$lang['trash_button_tip']   = 'Borrar esta tarea';


// general control labels
$lang['find_lbl']           = 'Busca:';
$lang['replace_lbl']        = 'Remplaza:';
$lang['help_lbl']           = 'Ayuda';
$lang['about_lbl']          = 'Acerca';
$lang['faq_lbl']            = 'FAQ';
$lang['website_lbl']        = 'Website';
$lang['go_lbl']             = 'Ir';
$lang['save_lbl']           = "Salvar\n [Ctrl+Enter]";
$lang['cancel_lbl']         = "Cancelar\n [Esc]";
$lang['trash_lbl']          = 'Papelera';
$lang['archive_lbl']        = 'Archivo';
$lang['placeholder']        = 'Añadir una tarea [Ctrl+Enter] o buscarla en la lista [Enter]';
$lang['language']           = 'Idioma';


// used before date intervals in result interface
$lang['next_lbl']           = 'en los siguientes';
$lang['prev_lbl']           = 'en los anteriores';
$lang['before_lbl']         = 'Antes';
$lang['after_lbl']          = 'Despues';
$lang['no_date_hdr']        = 'Sin fecha';


// miscellaneous
$lang['deleted_lbl']        = 'Borrado:';


// login
$lang['username_lbl']       = 'User Name';
$lang['password_lbl']       = 'Password';
$lang['confirmpassword_lbl'] = 'Confirm Password';
$lang['login_lbl']          = 'Login';
$lang['resetlogin_lbl']     = 'Reset Login';
$lang['createuser_lbl']     = 'Create User';



// ****************
// ** JAVASCRIPT **
// ****************


// colours are based on bk-* class colours in style.less
$jslang['add_msg']            = array('Tarea añadió', 'blue');
$jslang['edit_msg']           = array('Tarea editado', 'yellow');
$jslang['trash_msg']          = array('Tarea borrado', 'red');
$jslang['arch_msg']           = array('Tarea archivada', 'orange');
$jslang['all_trash_msg']      = array('Todas las tareas realizadas borrados', 'orange');
$jslang['all_arch_msg']       = array('Todas las tareas completadas archivados', 'orange');

$jslang['rename_msg']         = 'Cual es el nuevo nombre de esta pestaña?';
$jslang['remove_msg']         = 'Borrar esta pestaña?';
$jslang['create_msg']         = 'Cual es el nombre de la nueva pestaña?';
$jslang['search_msg']         = '';   // currently unused
$jslang['lang_change_msg']    = 'Idioma Cambiado! Recargando...';

$jslang['editable_tip']       = 'Haz tus cambios y haz click en [Salvar] o en  [Cancelar]';
$jslang['save_tip']           = $lang['save_lbl'];
$jslang['cancel_tip']         = $lang['cancel_lbl'];

$jslang['tag_click_tip']      = $lang['tag_click_tip'];
$jslang['edit_in_place_tip']  = 'Doble-click para editar esta tarea';
$jslang['project_click_tip']  = 'Clicka para ver solo esta descripcion';
$jslang['mark_complete_tip']  = 'Clicka en tareas para cambiar entre Hecho/PorHacer';
$jslang['reveal_tip']         = "Clicka en la palanca nota";
$jslang['sort_tip']           = '&#10; -O- Clicka y arrastra para cambiar el orden de las tareas';