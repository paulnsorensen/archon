<?php

$fh = fopen('../packages/collections/lib/descriptiverules.json', 'r') or die("Can't open file");
$data = fread($fh, filesize('../packages/collections/lib/descriptiverules.json'));
fclose($fh);

echo($data);

print_r(json_decode($data, true));


?>
