<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

$client = new ImageAnnotatorClient();
echo "✅ Google Vision Client loaded successfully!\n";
?>