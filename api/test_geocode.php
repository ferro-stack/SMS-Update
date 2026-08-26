<?php

$address = "Maasin City, Southern Leyte, Philippines";

$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
    'q' => $address,
    'format' => 'json',
    'limit' => 1
]);

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: SMRES-Scholarship-System/1.0\r\n",
        'timeout' => 10
    ]
];

$context = stream_context_create($opts);

$response = file_get_contents($url, false, $context);

echo "<pre>";
var_dump($response);
echo "</pre>";