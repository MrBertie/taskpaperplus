<?php
namespace tpp\view;


/**
 *  Simple Template class
 *
 * Usage
 * =====
 *
 * /---code php
 *
 * $view = new View( 'template.phtml' );
 * $view->title = 'My Title';
 * $view->text = 'Some text';
 *
 * $nav = new View( 'nav.phtml' );
 * $nav->links = array( 'http://www.google.com' => 'Google', 'http://www. y *ahoo.com' => 'Yahoo' );
 *
 * $view->nav = $nav;
 *
 * echo $view;
 * \---
 *
 * The templates
 *
 * *template.phtml*
 * /---code html
 * <html>
 *     <head>
 *         <title><?php echo $this->title ?></title>
 *     </head>
 *     <body>
 *         <?php echo $this->nav ?>
 *         <?php echo $this->_h( $this->text ) ?>
 *     </body>
 * </html>
 * \---
 *
 * *nav.phtml*
 * /---code html
 * <?php foreach( $this->links as $url => $link ): ?>
 *     <a href="<?php echo $url ?>"><?php echo $link ?></a>
 * <?php endforeach ?>
 * \---
 */

class Template {
    protected $_tpl_name;
    protected $_vars;

    const TPL_DIR = 'tpl/';
    const TPL_SUFFIX = '.tpl.php';

    function __construct($tpl_name) {
        $this->_tpl_name = $tpl_name;
    }

    function _h($str) {
        return htmlspecialchars($str);
    }

    function __get($name) {
        if( isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return null;
    }

    function __set($name, $value) {
        $this->_vars[$name] = $value;
    }

    // when testing for empty reverse the result!
    function __isset($name) {
        $empty = empty($this->_vars[$name]);
        return ! $empty;
    }

    function render() {
        ob_start();
        $path = APP_PATH . self::TPL_DIR . $this->_tpl_name . self::TPL_SUFFIX;
        include($path);
        $rendered = ob_get_clean();
        return $rendered;
    }

    function __toString() {
        return $this->render();
    }
}

?>
