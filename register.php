<?php
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

// Доступные роли для выбора
$availableRoles = ['client', 'employee', 'admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'client'; // По умолчанию 'client'

    // Валидация
    $errors = [];

    if (empty($firstName)) $errors[] = 'Имя обязательно';
    if (empty($lastName)) $errors[] = 'Фамилия обязательна';
    if (empty($email)) $errors[] = 'Email обязателен';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (strlen($password) < 6) $errors[] = 'Пароль должен быть не менее 6 символов';
    if ($password !== $confirmPassword) $errors[] = 'Пароли не совпадают';
    if (!in_array($role, $availableRoles)) $errors[] = 'Некорректная роль';

    if (empty($errors)) {
        try {
            // Проверка существования email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Abonents WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetchColumn() > 0) {
                $error = 'Пользователь с таким email уже существует';
            } else {
                // Регистрация с указанной ролью
                if (registerAbonent($firstName, $lastName, $email, $password, $role)) {
                    $success = 'Регистрация прошла успешно!';
                    // Очистка полей
                    $firstName = $lastName = $email = '';
                } else {
                    $error = 'Ошибка при регистрации';
                }
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Регистрация</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="first_name">Имя:</label>
            <input type="text" id="first_name" name="first_name"
                   value="<?= htmlspecialchars($firstName ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="last_name">Фамилия:</label>
            <input type="text" id="last_name" name="last_name"
                   value="<?= htmlspecialchars($lastName ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль (минимум 6 символов):</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-group">
            <label for="role">Роль:</label>
            <select id="role" name="role">
                <?php foreach ($availableRoles as $r): ?>
                    <option value="<?= $r ?>" <?= ($role ?? 'client') === $r ? 'selected' : '' ?>>
                        <?= ucfirst($r) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
</div>
</body>
</html>