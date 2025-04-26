<?php
if (!isset($_GET['cnpj'])) {
    http_response_code(400);
    echo json_encode(['status' => 'ERROR', 'message' => 'CNPJ não informado']);
    exit;
}

$cnpj = preg_replace('/\D/', '', $_GET['cnpj']);
$url = "https://www.receitaws.com.br/v1/cnpj/{$cnpj}";

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0"
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents($url, false, $context);

header('Content-Type: application/json');
echo $response;
