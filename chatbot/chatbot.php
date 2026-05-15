<?php
include '../config.php';

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo 'Ban muon tim source ve chu de gi?';
    exit;
}

function call_ai(string $message): ?string
{
    $openAiKey = getenv('OPENAI_API_KEY');
    $geminiKey = getenv('GEMINI_API_KEY');

    if ($openAiKey) {
        $payload = json_encode([
            'model' => getenv('OPENAI_MODEL') ?: 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Ban la chatbot tieng Viet cho website chia se source code. Tra loi ngan gon, goi y tu khoa/source phu hop.'],
                ['role' => 'user', 'content' => $message],
            ],
            'temperature' => 0.4,
        ]);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $openAiKey],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response ?: '', true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    if ($geminiKey) {
        $payload = json_encode([
            'contents' => [[
                'parts' => [[
                    'text' => 'Tra loi ngan gon bang tieng Viet, goi y source code phu hop: ' . $message,
                ]],
            ]],
        ]);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . urlencode($geminiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response ?: '', true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    return null;
}

$ai = function_exists('curl_init') ? call_ai($message) : null;
if ($ai) {
    echo e(mb_substr($ai, 0, 500));
    exit;
}

$lower = function_exists('mb_strtolower') ? mb_strtolower($message, 'UTF-8') : strtolower($message);
if (strpos($lower, 'php') !== false) {
    echo 'Ban co the tim PHP, Laravel, MVC, CRUD, quan ly sinh vien hoac ban hang.';
} elseif (strpos($lower, 'java') !== false) {
    echo 'Thu tim Java Swing, Spring Boot, quan ly thu vien, ban hang hoac chat realtime.';
} elseif (strpos($lower, 'python') !== false) {
    echo 'Goi y Python: Django, Flask, AI chatbot, nhan dien anh, crawl du lieu.';
} else {
    echo 'Hay thu cac tu khoa nhu PHP, Laravel, Java, Python, NodeJS hoac mo ta bai toan ban dang lam.';
}
