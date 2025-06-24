<?php
require_once 'config.php';
checkRole('admin');

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создание пользователя
    if (isset($_POST['create_user'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Abonents (first_name, last_name, birth_date, passport_number, address, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['birth_date'] ?? null,
                $_POST['passport_number'] ?? null,
                $_POST['address'] ?? null,
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role']
            ]);
            $_SESSION['success'] = "Пользователь успешно создан";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при создании пользователя: " . $e->getMessage();
        }
    }

    // Удаление пользователя
    if (isset($_POST['delete_user'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Abonents WHERE abonent_id = ?");
            $stmt->execute([$_POST['user_id']]);
            $_SESSION['success'] = "Пользователь успешно удален";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при удалении пользователя: " . $e->getMessage();
        }
    }

    // Добавление телефона
    if (isset($_POST['add_phone'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Phones (abonent_id, phone_number, registration_date) VALUES (?, ?, CURDATE())");
            $stmt->execute([
                $_POST['abonent_id'],
                $_POST['phone_number']
            ]);
            $_SESSION['success'] = "Телефон успешно добавлен";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при добавлении телефона: " . $e->getMessage();
        }
    }

    // Обновление статуса телефона
    if (isset($_POST['update_phone_status'])) {
        try {
            $stmt = $pdo->prepare("UPDATE Phones SET is_active = ? WHERE phone_id = ?");
            $stmt->execute([
                $_POST['is_active'],
                $_POST['phone_id']
            ]);
            $_SESSION['success'] = "Статус телефона успешно обновлен";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при обновлении статуса телефона: " . $e->getMessage();
        }
    }

    // Удаление телефона
    if (isset($_POST['delete_phone'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Phones WHERE phone_id = ?");
            $stmt->execute([$_POST['phone_id']]);
            $_SESSION['success'] = "Телефон успешно удален";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при удалении телефона: " . $e->getMessage();
        }
    }

    // Добавление услуги
    if (isset($_POST['add_service'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Services (service_name, description, monthly_fee) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['service_name'],
                $_POST['description'] ?? null,
                $_POST['monthly_fee']
            ]);
            $_SESSION['success'] = "Услуга успешно добавлена";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при добавлении услуги: " . $e->getMessage();
        }
    }

    // Удаление услуги
    if (isset($_POST['delete_service'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Services WHERE service_id = ?");
            $stmt->execute([$_POST['service_id']]);
            $_SESSION['success'] = "Услуга успешно удалена";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при удалении услуги: " . $e->getMessage();
        }
    }

    // Подключение услуги
    if (isset($_POST['connect_service'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Connected_Services (phone_id, service_id, connection_date) VALUES (?, ?, CURDATE())");
            $stmt->execute([
                $_POST['phone_id'],
                $_POST['service_id']
            ]);
            $_SESSION['success'] = "Услуга успешно подключена";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при подключении услуги: " . $e->getMessage();
        }
    }

    // Отключение услуги
    if (isset($_POST['disconnect_service'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Connected_Services WHERE connection_id = ?");
            $stmt->execute([$_POST['connection_id']]);
            $_SESSION['success'] = "Услуга успешно отключена";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при отключении услуги: " . $e->getMessage();
        }
    }

    // После обработки POST-запроса перенаправляем на эту же страницу, чтобы избежать повторной отправки формы
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Получаем список пользователей
$users = [];
$usersQuery = $pdo->query('SELECT * FROM `Abonents`');
if ($usersQuery) {
    $users = $usersQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем список телефонов
$phones = [];
$phonesQuery = $pdo->query('SELECT p.*, a.first_name, a.last_name FROM `Phones` p JOIN `Abonents` a ON p.abonent_id = a.abonent_id');
if ($phonesQuery) {
    $phones = $phonesQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем список услуг
$services = [];
$servicesQuery = $pdo->query("SELECT * FROM `Services`");
if ($servicesQuery) {
    $services = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем список подключенных услуг
$connectedServices = [];
$connectedServicesQuery = $pdo->query("SELECT cs.connection_id, p.phone_number, s.service_name, cs.connection_date 
                                     FROM `Connected_Services` cs
                                     JOIN `Phones` p ON cs.phone_id = p.phone_id
                                     JOIN `Services` s ON cs.service_id = s.service_id");
if ($connectedServicesQuery) {
    $connectedServices = $connectedServicesQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем сообщения из сессии
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Очищаем сообщения после использования
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
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
            color: var(--primary);
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #d1143e;
        }

        .btn-logout {
            background-color: var(--secondary);
        }

        .btn-logout:hover {
            background-color: #5a6268;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status-active {
            color: var(--success);
            font-weight: 600;
        }

        .status-inactive {
            color: var(--danger);
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-light);
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            transition: var(--transition);
        }

        .tab:hover {
            background-color: var(--primary-light);
        }

        .tab.active {
            background-color: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Панель администратора</h1>
        <a href="logout.php" class="btn btn-logout">Выйти</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="tabs">
        <div class="tab active" onclick="openTab('users')">Пользователи</div>
        <div class="tab" onclick="openTab('phones')">Телефоны</div>
        <div class="tab" onclick="openTab('services')">Услуги</div>
        <div class="tab" onclick="openTab('connections')">Подключения</div>
    </div>

    <!-- Вкладка пользователей -->
    <div id="users" class="tab-content active">
        <div class="card">
            <h2>Создать пользователя</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" required>
                            <option value="client">Клиент</option>
                            <option value="employee">Сотрудник</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="create_user" class="btn">Создать</button>
            </form>
        </div>

        <div class="card">
            <h2>Все пользователи</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['abonent_id'] ?></td>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td>
                            <?php if ($user['abonent_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['abonent_id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">Удалить</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Вкладка телефонов -->
    <div id="phones" class="tab-content">
        <div class="card">
            <h2>Добавить телефон</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Владелец</label>
                        <select name="abonent_id" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['abonent_id'] ?>">
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Номер телефона</label>
                        <input type="text" name="phone_number" required>
                    </div>
                </div>
                <button type="submit" name="add_phone" class="btn">Добавить</button>
            </form>
        </div>

        <div class="card">
            <h2>Список телефонов</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Номер</th>
                    <th>Владелец</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($phones as $phone): ?>
                    <tr>
                        <td><?= $phone['phone_id'] ?></td>
                        <td><?= htmlspecialchars($phone['phone_number']) ?></td>
                        <td><?= htmlspecialchars($phone['first_name'] . ' ' . $phone['last_name']) ?></td>
                        <td class="<?= $phone['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $phone['is_active'] ? 'Активен' : 'Неактивен' ?>
                        </td>
                        <td class="action-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="phone_id" value="<?= $phone['phone_id'] ?>">
                                <input type="hidden" name="is_active" value="<?= $phone['is_active'] ? 0 : 1 ?>">
                                <button type="submit" name="update_phone_status" class="btn">
                                    <?= $phone['is_active'] ? 'Деактивировать' : 'Активировать' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="phone_id" value="<?= $phone['phone_id'] ?>">
                                <button type="submit" name="delete_phone" class="btn btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Вкладка услуг -->
    <div id="services" class="tab-content">
        <div class="card">
            <h2>Добавить услугу</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Название</label>
                        <input type="text" name="service_name" required>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Абон. плата (руб.)</label>
                        <input type="number" name="monthly_fee" min="0" step="0.01" required>
                    </div>
                </div>
                <button type="submit" name="add_service" class="btn">Добавить</button>
            </form>
        </div>

        <div class="card">
            <h2>Список услуг</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Стоимость</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= $service['service_id'] ?></td>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= htmlspecialchars($service['description'] ?? '—') ?></td>
                        <td><?= number_format($service['monthly_fee'], 2) ?> руб.</td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                <button type="submit" name="delete_service" class="btn btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Вкладка подключений -->
    <div id="connections" class="tab-content">
        <div class="card">
            <h2>Подключить услугу</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Телефон</label>
                        <select name="phone_id" required>
                            <?php foreach ($phones as $phone): ?>
                                <option value="<?= $phone['phone_id'] ?>">
                                    <?= htmlspecialchars($phone['phone_number']) ?> (<?= htmlspecialchars($phone['first_name'] . ' ' . $phone['last_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Услуга</label>
                        <select name="service_id" required>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['service_id'] ?>">
                                    <?= htmlspecialchars($service['service_name']) ?> (<?= number_format($service['monthly_fee'], 2) ?> руб.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="connect_service" class="btn">Подключить</button>
            </form>
        </div>

        <div class="card">
            <h2>Активные подключения</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Телефон</th>
                    <th>Услуга</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($connectedServices as $conn): ?>
                    <tr>
                        <td><?= $conn['connection_id'] ?></td>
                        <td><?= htmlspecialchars($conn['phone_number']) ?></td>
                        <td><?= htmlspecialchars($conn['service_name']) ?></td>
                        <td><?= $conn['connection_date'] ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="connection_id" value="<?= $conn['connection_id'] ?>">
                                <button type="submit" name="disconnect_service" class="btn btn-danger">Отключить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openTab(tabName) {
        // Скрыть все вкладки
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Деактивировать все табы
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Показать выбранную вкладку
        document.getElementById(tabName).classList.add('active');

        // Активировать выбранный таб
        event.currentTarget.classList.add('active');
    }
</script>
</body>
</html>