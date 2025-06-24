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
    $role = $_POST['role'] ?? 'client';

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
                // Хеширование пароля
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Вставка в базу данных
                $stmt = $pdo->prepare("INSERT INTO Abonents (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$firstName, $lastName, $email, $passwordHash, $role]);

                $success = 'Пользователь успешно зарегистрирован!';
                // Очистка полей
                $firstName = $lastName = $email = '';
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
        :root {
            --primary: #4361ee;
            --primary-light: #edf2ff;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --border-radius: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 500px;
            width: 100%;
            padding: 0 15px; /* Добавляем отступы по бокам */
            box-sizing: border-box; /* Учитываем padding в ширине */
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box; /* Учитываем padding в ширине */
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 30px;
            margin-top: 0; /* Убираем верхний отступ */
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            box-sizing: border-box; /* Учитываем padding в ширине */
        }

        input:focus, select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            margin-top: 10px; /* Добавляем отступ сверху */
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #e6fffa;
            color: #065f46;
        }

        .alert-error {
            background-color: #fff5f5;
            color: #b91c1c;
        }

        .text-center {
            text-align: center;
        }

        .login-link {
            margin-top: 20px;
            color: var(--secondary);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Регистрация</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="first_name">Имя</label>
                <input type="text" id="first_name" name="first_name"
                       value="<?= htmlspecialchars($firstName ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Фамилия</label>
                <input type="text" id="last_name" name="last_name"
                       value="<?= htmlspecialchars($lastName ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль (минимум 6 символов)</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Подтвердите пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="role">Роль</label>
                <select id="role" name="role" required>
                    <?php foreach ($availableRoles as $r): ?>
                        <option value="<?= $r ?>" <?= ($role ?? 'client') === $r ? 'selected' : '' ?>>
                            <?= ucfirst($r) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>

        <div class="login-link text-center">
            Уже есть аккаунт? <a href="login.php">Войдите</a>
        </div>
    </div>
</div>
</body>
</html>