<?php
require_once 'config.php';
require_once 'functions.php';

checkAuth();

$abonentId = $_SESSION['user_id'];
$abonentInfo = getAbonentInfo($abonentId);
$phones = getAbonentPhones($abonentId);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --card-padding: 25px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1, h2, h3 {
            color: var(--primary-color);
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: var(--card-padding);
            margin-bottom: 25px;
        }

        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .info-value {
            padding: 10px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
        }

        .phone-card {
            border-left: 4px solid var(--accent-color);
            margin-bottom: 30px;
        }

        .service-list {
            list-style: none;
            padding: 0;
        }

        .service-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .service-active {
            color: var(--success-color);
            font-weight: 600;
        }

        .service-inactive {
            color: #999;
        }

        .payment-form {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .payment-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .btn-admin {
            background-color: #6c757d;
        }

        .btn-admin:hover {
            background-color: #5a6268;
        }

        .btn-logout {
            background-color: #dc3545;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }

        .status-active {
            color: var(--success-color);
            font-weight: 600;
        }

        .status-inactive {
            color: #dc3545;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Добро пожаловать, <?= htmlspecialchars($abonentInfo['first_name']) ?>!</h1>
        <div>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employee'): ?>
                <a href="admin_dashboard.php" class="btn btn-admin">Админ-панель</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-logout">Выйти</a>
        </div>
    </div>

    <div class="card">
        <h2>Личные данные</h2>
        <div class="user-info">
            <div class="info-item">
                <div class="info-label">Имя</div>
                <div class="info-value"><?= htmlspecialchars($abonentInfo['first_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Фамилия</div>
                <div class="info-value"><?= htmlspecialchars($abonentInfo['last_name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><?= htmlspecialchars($abonentInfo['email']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Роль</div>
                <div class="info-value"><?= ucfirst(htmlspecialchars($abonentInfo['role'])) ?></div>
            </div>
        </div>
    </div>

    <h2>Ваши телефоны</h2>
    <?php if (count($phones) > 0): ?>
        <?php foreach ($phones as $phone): ?>
            <div class="card phone-card">
                <h3>Номер: <?= htmlspecialchars($phone['phone_number']) ?></h3>
                <div class="user-info">
                    <div class="info-item">
                        <div class="info-label">Дата регистрации</div>
                        <div class="info-value"><?= $phone['registration_date'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Статус</div>
                        <div class="info-value <?= $phone['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $phone['is_active'] ? 'Активен' : 'Неактивен' ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Баланс</div>
                        <div class="info-value"><?= getPhoneBalance($phone['phone_id']) ?> руб.</div>
                    </div>
                </div>

                <h4>Подключенные услуги</h4>
                <?php $services = getConnectedServices($phone['phone_id']); ?>
                <?php if (count($services) > 0): ?>
                    <ul class="service-list">
                        <?php foreach ($services as $service): ?>
                            <li class="service-item">
                                <span><?= htmlspecialchars($service['service_name']) ?> (<?= $service['monthly_fee'] ?> руб./мес.)</span>
                                <span class="<?= $service['is_active'] ? 'service-active' : 'service-inactive' ?>">
                                        <?= $service['is_active'] ? 'Активна' : 'Неактивна' ?>
                                    </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Нет подключенных услуг</p>
                <?php endif; ?>

                <form action="add_payment.php" method="POST" class="payment-form">
                    <input type="hidden" name="phone_id" value="<?= $phone['phone_id'] ?>">
                    <input type="number" name="amount" min="1" required placeholder="Сумма пополнения">
                    <button type="submit" class="btn">Пополнить счет</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p>У вас нет зарегистрированных телефонов</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>