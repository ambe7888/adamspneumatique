<?php
$ch = curl_init('http://localhost/whatsclick/adamspneumatique/admin/api.php?type=locations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "Locations: " . $res . "\n";
