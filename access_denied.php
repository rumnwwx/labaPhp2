<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступ запрещен</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #343a40;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error-code {
            font-size: 5rem;
            font-weight: bold;
            margin: 0;
            color: #dc3545;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="error-code">403</div>
    <h1>Доступ запрещен</h1>
    <p>У вас недостаточно прав для просмотра этой страницы.</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php" class="btn">Вернуться в профиль</a>
    <?php else: ?>
        <a href="login.php" class="btn">Войти в систему</a>
    <?php endif; ?>
</div>
</body>
</html>