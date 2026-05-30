<?php
$file = 'resources/views/pos/index.blade.php';
$content = file_get_contents($file);
// %C3%83%C2%97 is the urlencoded form of the double-encoded UTF-8 for multiply sign (Ã—)
$search = urldecode('%C3%83%C2%97');
if (strpos($content, $search) !== false) {
    $content = str_replace($search, '&times;', $content);
    file_put_contents($file, $content);
    echo "Fixed index.blade.php\n";
} else {
    echo "Not found in index.blade.php\n";
}
