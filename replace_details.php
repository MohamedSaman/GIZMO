<?php
$directory = new RecursiveDirectoryIterator('resources/views');
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

$replacements = [
    'GIZMO COVERING' => 'GIZMO ELECTRONICS',
    '237 KKS ROAD, JAFFNA' => 'No-10 Keyzer Street, Colombo 11',
    'NO: 237 KKS ROAD, JAFFNA | NO: 37 NEW MARKET JAFFNA' => 'No-10 Keyzer Street, Colombo 11',
    '0761919650' => '0777005897 / 0112337242',
    '(077) 9752950' => '0777005897',
    '( 076) 9085252' => '0777005897 / 0112337242',
    '(076) 9085252' => '0777005897 / 0112337242',
    '021 222 85 89' => '0112337242',
    'Gizmolanka@gmail.com' => 'gizmoelectronicsofficial@gmail.com',
    'GIZMOLANKA@GMAIL.COM' => 'gizmoelectronicsofficial@gmail.com',
];

$count = 0;
foreach ($regex as $file) {
    $filePath = $file[0];
    $content = file_get_contents($filePath);
    $newContent = strtr($content, $replacements);
    if ($content !== $newContent) {
        file_put_contents($filePath, $newContent);
        echo "Updated: $filePath\n";
        $count++;
    }
}
echo "Total files updated: $count\n";
