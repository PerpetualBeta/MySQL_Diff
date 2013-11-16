<?php

/**
 * Autoloader
 */
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/classes/');
spl_autoload_register();


/**
 * Load and parse the configuration file
 */
$config = new Get_config();

/**
 * Configure emailer
 */
if (isset($config->other['email'])) {
  $headers = '';
  if (isset($config->other['email']['from'])) $headers .= "From: MySQL_Diff <" . $config->other['email']['from'] . ">\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
}


/**
 * Do It
 */
$MySQL_Diff = new MySQL_Diff($config->diff);
$data = array( 'diffs' => $MySQL_Diff->diffs );
$data['config_used'] = $config->other['config_used'];


/**
 * Deal with the result (display or email it)
 */
$template_path = __DIR__ . '/templates/';
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
    if ( isset($config->other['email']['to']) && isset($config->other['email']['subject']) && isset($headers) ) {
      mail($config->other['email']['to'], $config->other['email']['subject'], $body, $headers);
    }
    exit(64); // Let the CLI know that we detected DB differences
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
