<?php

/**
 * Autoloader
 */
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/classes/');
spl_autoload_register();


/**
 * Pre-flight
 */
$db_1 = array (
                'host'      => '127.0.0.1',
                'username'  => 'root',
                'password'  => 'letmein',
                'database'  => 'employees_1'
              );
$db_2 = array (
                'host'      => '127.0.0.1',
                'username'  => 'root',
                'password'  => 'letmein',
                'database'  => 'employees_2'
              );
/*
  If $config['strict'] is TRUE then the table schemas must match exactly
  If $config['ignore_comments'] is TRUE then SQL COMMENTS will not be
  considered diffs
  If $config['ignore_auto_increment'] is TRUE then the AUTO_INCREMENT index
  of the DB's tables will not be considered diffs
*/
$config = array (
                  'left'                  => $db_1,
                  'right'                 => $db_2,
                  'strict'                => FALSE,
                  'ignore_comments'       => FALSE,
                  'ignore_auto_increment' => TRUE
                );
$template_path = __DIR__ . '/templates/';


/**
 * Email Configuration
 */
$to = 'you@yourdomain.com';
$subject = 'Database Schema Comparison';
$headers = "From: MySQL_Diff <do-not-reply@yourdomain.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";


/**
 * Do It
 */
$MySQL_Diff = new MySQL_Diff($config);
$data = array( 'diffs' => $MySQL_Diff->diffs );


/**
 * Deal with the result (display or email it)
 */
if ($MySQL_Diff->isCLI() === TRUE) {
  // Running from the CLI
  $what = 'differences were detected between the "' . $MySQL_Diff->diffs['left'] . '" and "' . $MySQL_Diff->diffs['right'] . '" databases';
  $data['message'] = ($MySQL_Diff->mismatch === TRUE)
    ? '<p>The following ' . $what . ':</p><hr />'
    : '<p>No ' . $what . '.</p>';
  if ($MySQL_Diff->mismatch === TRUE) {
    // Capture the result
    ob_start();
    View::factory($template_path . 'index', $data)->render();
    $body = ob_get_clean();
    // Email the result
    mail($to, $subject, $body, $headers);
    exit(1); // Let the CLI know that we detected DB differences
  } else {
    exit(0); // Let the CLI know that we terminated successfully
  }
  // Bash will have this return value in $?. Thus "$? -eq 0" can be used to
  // determine whether or not Schema differences were detected
} else {
  // Running from the Web Browser
  // Display the result
  View::factory($template_path . 'index', $data)->render();
}
