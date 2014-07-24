# MySQL_Diff

A PHP script to compare two MySQL databases and report on the differences, if any, in their schemas. MySQL_Diff can be run from the CLI (eg: from a cron job) or via a Web Browser. It can configured to perform a strict comparison (any differences will be reported). Alternatively, it can perform a "loose" comparison where it can be configured to ignore "Auto_Increment" and/or "Comment" values.

### Usage

    $MySQL_Diff = new MySQL_Diff($config);
    $data = array( 'diffs' => $MySQL_Diff->diffs );

See included example file, [`db_diff.php`](https://github.com/PerpetualBeta/MySQL_Diff/blob/master/db_diff.php) for configuration and usage examples.

### Third Party

So as to be able to run out-of-the-box, MySQL_Diff includes [szok's](https://github.com/szok) [View class.](https://github.com/szok/View) However, this is not a pre-requisite. MySQL_Diff can be configured as an integrated component of existing frameworks (eg: CodeIgniter), or Continuous Integration suites.

MySQL_Diff IS dependent on [Stephen Morley's](http://stephenmorley.org/) [class.Diff,](http://code.stephenmorley.org/php/diff-implementation/) which is included with this package.

### Further Details

See [MySQL_Diff: Database Schema Difference Reconciliation](http://darkblue.sdf.org/weblog/mysql-diff.html)
