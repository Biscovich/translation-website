<?php
// send.php

// 1) Налаштування
$botToken = '8252521304:AAHIuWAyY2iHhDv-C5pD-q12zj7X1koCg6I';
$chatId   = '755731322';
$emailTo  = '5923758@ukr.net';

// 2) Очищення вхідних даних
function clean($v) {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}
$name    = clean($_POST['name']    ?? '');
$email   = clean($_POST['email']   ?? '');
$message = clean($_POST['message'] ?? '');
$phone   = clean($_POST['phone']   ?? '');

// 2.1) Додаткова валідація email
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = '';
}

// 3) Формуємо текст
$text  = "📩 <b>Нова заявка з сайту</b>\n";
$text .= "Ім’я: {$name}\n";
$text .= "Email: {$email}\n";
if ($phone)   $text .= "Телефон: {$phone}\n";
if ($message) $text .= "Повідомлення: {$message}\n";

// 4) Відправка в Telegram
$telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$payload = [
    'chat_id'    => $chatId,
    'text'       => $text,
    'parse_mode' => 'HTML',
];
$ch = curl_init($telegramUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
if ($result === false) {
    error_log('Telegram cURL error: ' . curl_error($ch));
}
curl_close($ch);

// 5) Відправка на email
$subject = "Нова заявка з сайту";
$body    = strip_tags(str_replace("\n", "\r\n", $text));
$headers = [
    "From: no-reply@{$_SERVER['HTTP_HOST']}",
    "Reply-To: " . ($email ?: 'no-reply@' . $_SERVER['HTTP_HOST'])
];
mail($emailTo, $subject, $body, implode("\r\n", $headers));

// 6) Редірект
header('Location: thank-you.html');
exit;
