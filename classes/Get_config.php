<?php

class Get_config {

  public $diff = array();
  public $other = array();

  /**
   * Constructor
   */
  function __construct() {
    $this->initialise();
  }

  public function initialise() {

    /**
     * Get configuration file
     */
    $config_path = dirname( dirname(__FILE__) ) . '/config/';
    if (Utilities::isCLI() === TRUE) {
      // Running from the CLI, there might be a configuration file specified in
      // the options
      $options = getopt( '', array('config::') );
      $config_file = (isset($options['config'])) ? $options['config'] : '';
    } else {
      // Running from the browser, there might be a configuration file specified in
      // GET or POST
      $config_file = (isset($_REQUEST['config'])) ? $_REQUEST['config'] : '';
      $config_file = preg_replace('/[^A-Za-z0-9\-_]/', '', $config_file);
    }
    if ($config_file) {
      if (is_readable($config_file)) {
        $config_used = $config_file;
      } elseif (is_readable($config_path . $config_file)) {
        $config_used = $config_path . $config_file;
      } elseif (is_readable($config_file . '.ini')) {
        $config_used = $config_file . '.ini';
      } elseif (is_readable($config_path . $config_file . '.ini')) {
        $config_used = $config_path . $config_file . '.ini';
      } else {
        Utilities::error('The configuration file you have specified could not be loaded!');
      }
    } else {
      // No configuration file REQUESTed, get the most recent one by default
      $dir = glob($config_path . '*.ini');
      $files = array();
      foreach ($dir as $file) $files[$file] = filemtime($file);
      if (count($files)) {
        arsort($files);
        $keys = array_keys($files);
        $default = array_shift($keys);
        if (is_readable($default)) {
          $config_used = $default;
        } else {
          Utilities::error('The default configuration file could not be loaded!');
        }
      } else {
        Utilities::error('No configuration file(s) found!');
      }
    }
    $config = parse_ini_file($config_used, TRUE);

    /**
     * Process configuration file
     */
    if (
          is_array($config) &&
          isset($config['db_1']) && isset($config['db_2']) && isset($config['behaviour']) &&
          (count($config['db_1']) == 4) && (count($config['db_2']) == 4) && count($config['behaviour'])
    ) {
      // Configuration file seems to be well-formed
      $this->diff = array(
        'left' => $config['db_1'],
        'right' => $config['db_2'],
        'strict' => filter_var($config['behaviour']['strict'], FILTER_VALIDATE_BOOLEAN),
        'ignore_comments' => filter_var($config['behaviour']['ignore_comments'], FILTER_VALIDATE_BOOLEAN),
        'ignore_auto_increment' => filter_var($config['behaviour']['ignore_auto_increment'], FILTER_VALIDATE_BOOLEAN)
      );
      unset($config['db_1']);
      unset($config['db_2']);
      unset($config['behaviour']);
      $this->other = $config;
      $this->other['config_used'] = $config_used;
      unset($config_used);
    } else {
      Utilities::error('Invalid configuration file!');
    }
  }

}
