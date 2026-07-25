<?php
// Simple QR proxy: returns PNG from qrserver for given data (basic validation)
if (empty($_GET['data'])) {
    http_response_code(400);
    echo 'Missing data';
    exit;
}
$data = (string) $_GET['data'];
// limit length
if (strlen($data) > 2000) {
    http_response_code(400);
    echo 'Data too long';
    exit;
}
$size = isset($_GET['size']) ? preg_replace('/[^0-9x]/', '', $_GET['size']) : '200x200';
$url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . urlencode($size) . '&data=' . urlencode($data);
$opts = ["http"=>["method"=>"GET","timeout"=>5]];
$context = stream_context_create($opts);
$img = @file_get_contents($url, false, $context);
if ($img === false) {
    http_response_code(502);
    echo 'Unable to fetch QR';
    exit;
}
header('Content-Type: image/png');
echo $img;
exit;
