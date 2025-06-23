<?php
require_once 'config.php';

// Регистрация нового абонента
function registerAbonent($firstName, $lastName, $email, $password, $role = 'client') {
    global $pdo;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO Abonents (first_name, last_name, email, password_hash, role) 
                          VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$firstName, $lastName, $email, $passwordHash, $role]);
}

// Аутентификация пользователя
function login($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Abonents WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['abonent_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        return true;
    }
    return false;
}

// Получение информации об абоненте
function getAbonentInfo($abonentId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Abonents WHERE abonent_id = ?");
    $stmt->execute([$abonentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получение телефонов абонента
function getAbonentPhones($abonentId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Phones WHERE abonent_id = ?");
    $stmt->execute([$abonentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение подключенных услуг для телефона
function getConnectedServices($phoneId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT s.service_name, s.monthly_fee, cs.connection_date, cs.is_active 
                          FROM Connected_Services cs
                          JOIN Services s ON cs.service_id = s.service_id
                          WHERE cs.phone_id = ?");
    $stmt->execute([$phoneId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение баланса телефона
function getPhoneBalance($phoneId) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT balance FROM Accounts WHERE phone_id = ?");
    $stmt->execute([$phoneId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['balance'] : 0;
}



// Пополнение счета
function addPayment($phoneId, $amount) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Получаем account_id
        $stmt = $pdo->prepare("SELECT account_id FROM Accounts WHERE phone_id = ?");
        $stmt->execute([$phoneId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            // Создаем новый счет, если его нет
            $stmt = $pdo->prepare("INSERT INTO Accounts (phone_id, balance) VALUES (?, ?)");
            $stmt->execute([$phoneId, 0]);
            $accountId = $pdo->lastInsertId();
        } else {
            $accountId = $account['account_id'];
        }

        // Обновляем баланс
        $stmt = $pdo->prepare("UPDATE Accounts SET balance = balance + ?, last_payment_date = NOW(), 
                              last_payment_amount = ? WHERE account_id = ?");
        $stmt->execute([$amount, $amount, $accountId]);

        // Добавляем транзакцию
        $stmt = $pdo->prepare("INSERT INTO Transactions (account_id, amount, transaction_date, description)
                              VALUES (?, ?, NOW(), 'Пополнение счета')");
        $stmt->execute([$accountId, $amount]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Подключение услуги
function connectService($phoneId, $serviceId) {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO Connected_Services (phone_id, service_id, connection_date) 
                          VALUES (?, ?, NOW())");
    return $stmt->execute([$phoneId, $serviceId]);
}

// Отключение услуги
function disconnectService($phoneId, $serviceId) {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE Connected_Services SET is_active = FALSE WHERE phone_id = ? AND service_id = ?");
    return $stmt->execute([$phoneId, $serviceId]);
}

// Получение списка всех услуг
function getAllServices() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Services");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Для сотрудников: получение списка всех абонентов
function getAllAbonents() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Abonents");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Для сотрудников: добавление нового телефона
function addPhone($abonentId, $phoneNumber) {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO Phones (abonent_id, phone_number, registration_date) 
                          VALUES (?, ?, NOW())");
    return $stmt->execute([$abonentId, $phoneNumber]);
}
?>