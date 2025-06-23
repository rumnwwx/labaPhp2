<?php
// Настройки подключения к БД
$host = 'localhost';
$dbname = 'laba';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Инициализация сессии
session_start();

// Функция проверки авторизации
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Функция проверки роли (обновленная)
function checkRole($requiredRoles) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Если передана строка, преобразуем в массив
    if (is_string($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }

    // Получаем актуальную роль из БД для надежности
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT role FROM Abonents WHERE abonent_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userRole = $stmt->fetchColumn();

        if (!$userRole || !in_array($userRole, $requiredRoles)) {
            header('Location: access_denied.php');
            exit();
        }

        // Обновляем роль в сессии на случай изменений в БД
        $_SESSION['role'] = $userRole;

    } catch (PDOException $e) {
        die("Ошибка проверки роли: " . $e->getMessage());
    }
}

// Дополнительная функция для проверки администратора
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Включение отображения ошибок (для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>