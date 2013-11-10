<?php
/**
 * View class in PHP to view render script
 *
 * @author Piotr Jarolewski <jarolewski.piotr@gmail.com>
 * @license http://pl.wikipedia.org/wiki/MIT_Licence Licencja X11
 * @copyright (c) 2012, Piotr Jarolewski
 * @link http://www.webcoding.pl
 */
class View {

    /**
     * View vars do extract
     *
     * @var array
     */
    private $view_vars = array();

    /**
     * View file exstension
     *
     * @var string
     */
    private $ext = '.php';

    /**
     * View file patch
     *
     * @var string
     */
    private $path = '';

    /**
     * File rendering file
     *
     * @var string
     */
    private $file = '';

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Static render view
     *
     * @param string $file file path
     * @param array $vars variable in extract
     *
     * @return string
     */
    public static function factory($file, array $vars = null)
    {
        $view = new self;
        $view->setFile($file);
        if (is_array($vars)) {
            $view->setVars($vars);
        }

        return $view;
    }

    /**
     * View renderer
     *
     * @param string $file File patch
     * @param string $vars
     * @param boolean $return
     */
    public function render($return = false)
    {
        if (!file_exists($this->path.$this->file.$this->ext)) {
            throw new Exception('The '.$this->file.' view file is not exist!');
        }

        extract($this->view_vars);
        ob_start();
        include($this->path.$this->file.$this->ext);
        $buffer = ob_get_contents();
        ob_end_clean();
        if (true === $return) {

			return $buffer;
        } else {
            echo $buffer;
        }
    }

    /**
     * Set vars to view
     *
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        if (is_array($vars)) {
            $this->view_vars = array_merge($this->view_vars, $vars);
        }

        return;
    }

    /**
     * Set extension in view file
     *
     * @param string $ext default .php
     */
    public function setFileExtension($ext = '.php')
    {
        $this->ext = $ext;
    }

    /**
     * Set views file path
     *
     * @param string $path
     */
    public function setViewPath($path)
    {
        $this->path = $path;
    }

    /**
     * File name in render
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * set vars to view
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->view_vars[$name] = $value;
    }

    /**
     * To string method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }
}
// End of file: View.php