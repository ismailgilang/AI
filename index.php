<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = $_POST['message'] ?? '';
    $apiKey = 'AIzaSyDxYqHU2fiBu9OvA8Hw9y3NlV1gcahrH-o'; // Ganti dengan API key milikmu

    $customPrompt = $userMessage . "\n\nTolong berikan jawaban lengkap berdasarkan jurnal ilmiah setelah tahun 2020 dan cantumkan tautan DOI atau link asli jurnalnya jika tersedia.";

    $postData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $customPrompt]
                ]
            ]
        ]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['reply' => 'Error: ' . $error]);
    } else {
        $responseData = json_decode($response, true);
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Tidak ada balasan.';
        echo json_encode(['reply' => $reply]);
    }
    exit;
}
