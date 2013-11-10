<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
	<title>Database Schema Comparison</title>
	<style type="text/css">
<?php
	include_once 'index.css';
?>
	</style>
</head>

<body>
<?php
	if ( isset($message) && $message ) echo $message;
?>
	<h1>Database Schema Comparison</h1>
<?php
	$no_mismatches = $no_diffs = FALSE;
	echo '<p class="inverse">Comparing the database: &ldquo;<strong>' . $diffs['left'] . '</strong>&rdquo; with: &ldquo;<strong>' . $diffs['right'] . '</strong>&rdquo;&hellip;</p>';
	if ( isset($diffs['mismatched_tables']) && count($diffs['mismatched_tables']) ) {
		echo '<h2>Mismatched Table(s)</h2>';
		foreach ($diffs['mismatched_tables'] as $db => $tables) {
			if ( count($diffs['mismatched_tables'][$db]) ) {
				echo 'The following table' . ((count($diffs['mismatched_tables'][$db]) == 1) ? '' : 's') . ' only exist' . ((count($diffs['mismatched_tables'][$db]) == 1) ? 's' : '') . ' in the DB: &ldquo;<strong>' . $db . '</strong>&rdquo;';
				echo '<ul>';
				foreach ( $diffs['mismatched_tables'][$db] as $mismatch) {
					echo '<li>' . $mismatch . '</li>';
				}
				echo '</ul>';
			}
		}
	} else {
		$no_mismatches = TRUE;
	}
	if ( count($diffs['html']) ) {
		echo '<h2>Schema Differences</h2>';
		echo '<table class="diff diffHeader"><tr><td class="diffLeft">DB: ' . $diffs['left'] . '</td><td class="diffRight">DB: ' . $diffs['right'] . '</td></tr></table>';
		foreach ( $diffs['html'] as $diff) {
			echo $diff;
		}
	} else {
		$no_diffs = TRUE;
	}
	if ( ($no_mismatches === TRUE) && ($no_diffs === TRUE) ) {
		echo '<h3 class="aCenter">The databases match. Hell yeah!</h3>';
	}

?>

</body>
</html>
