<?php
// Убедимся, что запрос пришёл методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit;
}

// Получаем и очищаем поля
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Массив для ошибок
$errors = [];

// Валидация телефона (минимум 5 цифр)
$digits = preg_replace('/\D/', '', $phone);
if (strlen($digits) < 5) {
    $errors['phone'] = 'Введите корректный номер телефона (минимум 5 цифр)';
}

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный email';
}

// Если есть ошибки – возвращаем их
if (!empty($errors)) {
    http_response_code(422); // Unprocessable Entity
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Адрес получателя
$to = 'nash-urist@ya.ru';

// Тема письма
$subject = '=?UTF-8?B?' . base64_encode('Новая заявка с сайта Виртуальный номер') . '?=';

// Тело письма (можно HTML или обычный текст)
$body = "Новая заявка:\n";
$body .= "Телефон: $phone\n";
$body .= "Email: $email\n";
$body .= "Комментарий: " . ($message ?: 'Не указан') . "\n";
$body .= "\n---\nОтправлено с сайта " . $_SERVER['HTTP_HOST'];

// Заголовки
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= "From: no-reply@{$_SERVER['HTTP_HOST']}\r\n";
$headers .= "Reply-To: $email\r\n";

// Попытка отправки
$mailSent = mail($to, $subject, $body, $headers);

if ($mailSent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма']);
}