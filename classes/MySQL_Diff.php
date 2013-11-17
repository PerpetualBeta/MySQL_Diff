<?php

class MySQL_Diff {

  public $diffs = array();
  public $mismatch;
  private $db;


  /**
   * Constructor
   */
  function __construct($config) {
    $this->mismatch = FALSE;
    $tables = $this->check_tables_integrity($config);
    $schema = $this->check_table_schemas($config);
    if ( ($tables === FALSE) || ($schema === FALSE) ) $this->mismatch = TRUE;
  }

  private function switch_db($params = NULL) {
    if ( is_null($params) || !is_array($params) ) { return FALSE; } else {
      try {
        $this->db = new PDO('mysql:host=' . $params['host'] . '; dbname=' .  $params['database'], $params['username'], $params['password']);
      }
      catch(PDOException $e) {
        $this->db = FALSE;
        $this->error($e->getMessage() . PHP_EOL);
      }
    }
  }

  private function which_db() {
    if ( $this->db && $result = $this->db->query('SELECT DATABASE()') ) {
      $row = $result->fetch();
      $result = NULL;
      return $row[0];
    } else {
      return FALSE;
    }
  }

  private function get_tables() {
    $result = $this->db->query('SHOW TABLES');
    $tables = array();
    while($row = $result->fetch()) {
      $tables[] = $row[0];
    }
    $result = NULL;
    return $tables;
  }

  private function check_tables_integrity($config = NULL) {
    if  (
          is_null($config) || !is_array($config) ||
          !isset($config['left']) || !isset($config['right'])
        )
    {
      return false;
    }
    $this->switch_db($config['left']);
    $left_db = $this->which_db();
    $tables_left = $this->get_tables();
    $this->switch_db($config['right']);
    $right_db = $this->which_db();
    $tables_right = $this->get_tables();
    $tmp = array_diff($tables_left, $tables_right);
    if ( count($tmp) ) $this->diffs['mismatched_tables'][$left_db] = $tmp;
    $tmp = array_diff($tables_right, $tables_left);
    if ( count($tmp) ) $this->diffs['mismatched_tables'][$right_db] = $tmp;
    if (
        (
          isset($this->diffs['mismatched_tables'][$left_db]) &&
          count($this->diffs['mismatched_tables'][$left_db])
        ) ||
        (
          isset($this->diffs['mismatched_tables'][$right_db]) &&
          count($this->diffs['mismatched_tables'][$right_db])
        )
      )
    {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  function check_table_schemas($config = NULL) {
    if  (
          is_null($config) || !is_array($config) ||
          !isset($config['left']) || !isset($config['right'])
        )
    {
      return false;
    }
    if ( !isset($config['strict']) ) $config['strict'] = TRUE;
    if ( !isset($config['ignore_comments']) ) $config['ignore_comments'] = FALSE;
    if ( !isset($config['ignore_auto_increment']) ) $config['ignore_auto_increment'] = FALSE;

    $this->switch_db($config['right']);
    $this->diffs['right'] = $this->which_db();
    $tables_right = $this->get_tables();
    $schemas_right = array();
    foreach ($tables_right as $table) {
      $result = $this->db->query('SHOW CREATE TABLE ' . $table);
      $row = $result->fetch();
      $schema_right = $row[1];
      if ( $config['ignore_auto_increment'] && ($config['strict'] === FALSE) ) $schema_right = preg_replace('/ AUTO_INCREMENT=[0-9]+\b/', '', $schema_right);
      if ( $config['ignore_comments'] && ($config['strict'] === FALSE) ) $schema_right = preg_replace('/ COMMENT \'(.*)?\'/', '', $schema_right);
      $schemas_right[ $table ] = $schema_right;
    }

    $this->switch_db($config['left']);
    $this->diffs['left'] = $this->which_db();
    $tables_left = $this->get_tables();
    $this->diffs['missing'] = $this->diffs['html'] = array();

    foreach ($tables_left as $table) {
      $result = $this->db->query('SHOW CREATE TABLE ' . $table);
      $left_schema = $result->fetch();
      if ( array_key_exists($left_schema['Table'], $schemas_right) ) {
        if ( $config['ignore_auto_increment'] && ($config['strict'] === FALSE) ) $left_schema[1] = preg_replace('/ AUTO_INCREMENT=[0-9]+\b/', '', $left_schema[1]);
        if ( $config['ignore_comments'] && ($config['strict'] === FALSE) ) $left_schema[1] = preg_replace('/ COMMENT \'(.*)?\'/', '', $left_schema[1]);
        if ( $left_schema[1] != $schemas_right[ $left_schema['Table'] ] ) {
          $raw_diffs = Diff::compare( $left_schema[1], $schemas_right[ $left_schema['Table'] ] );
          $actual_diffs = $seen = array();
          foreach ($raw_diffs as $k => $v) {
            if ($v[1] > 0) {
              if ($config['strict'] === false) $v[0] = rtrim($v[0], ',');
              $actual_diffs[$k] = array( $v[0], $v[1] );
              $seen[] = $v[0];
            }
          }
          // De-duplication: Get only those elements that don't match another
          $dupes = array_count_values($seen);
          unset($seen);
          foreach ($dupes as $k => $v) {
            foreach ($actual_diffs as $ad_k => $ad_v) {
              if ( ($ad_v[0] == $k) && ($v > 1) ) {
                unset($actual_diffs[$ad_k]);
                unset($raw_diffs[$ad_k]);
              }
            }
          }
          // Clean Up
          $raw_diffs = array_values(array_filter($raw_diffs));
          // See if we still have diffs
          $gotDiffs = FALSE;
          foreach ($raw_diffs as $k => $v) if ($v[1] > 0) $gotDiffs = TRUE;
          // Prepare the HTML for Schema diffs
          if ($gotDiffs) $this->diffs['html'][] = Diff::toTable( $raw_diffs );
        }
      } else {
        $this->diffs['missing'][] = $table;
      }
    }
    $result = NULL;
    if ( count($this->diffs['html']) || count($this->diffs['missing']) ) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  // Returns TRUE if running from the command line (eg: cron job). Otherwise returns FALSE
  public function isCLI() {
    return (php_sapi_name() === 'cli');
  }

  private function error($error = 'An unknown error occurred!') {
    $error .= PHP_EOL;
    if ($this->isCLI()) {
      echo $error;
      exit(1);
    } else {
      die($error);
    }
  }

}
