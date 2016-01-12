<?php
$zeilen = passthru('cat *.php | wc -l');
echo 'Amasebase hat '.  $zeilen . ' Zeilen<br>';

?>