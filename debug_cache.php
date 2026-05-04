<?php
require_once __DIR__ . '/api/bootstrap.php';
$dir = __DIR__ . '/cache';
echo "Cache Dir: " . $dir . "\n";
echo "Exists: " . (is_dir($dir) ? 'Yes' : 'No') . "\n";
if (is_dir($dir)) {
    echo "Files:\n";
    print_r(scandir($dir));
}
$key = 'opt_directions';
$md5 = md5($key);
echo "Key: $key, MD5: $md5\n";
?>
