<?php
/**
 * This script manually copies all files from:
 * storage/app/public → public/storage
 *
 * Use it only once to sync files.
 */

function copyDirectory($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);

    while (false !== ($file = readdir($dir))) {
        if (($file !== '.') && ($file !== '..')) {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}

// Define source and destination paths
$source = __DIR__ . '/storage/app/public';
$destination = __DIR__ . '/public/storage';

if (!is_dir($source)) {
    die('❌ Source folder not found: ' . $source);
}

copyDirectory($source, $destination);

echo "✅ All files copied from <strong>storage/app/public</strong> to <strong>public/storage</strong> successfully!";
?>
