<?php
require_once 'config.php';
checkRole('admin');

// Обработка всех действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // [Сохраните все обработчики POST-запросов из предыдущего кода]
        // ... (ваша существующая логика обработки)
    } catch (PDOException $e) {
        $error = "Ошибка базы данных: " . $e->getMessage();
    }
}

// [Сохраните все SQL-запросы для получения данных]
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
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 6px;
            --shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        /* Базовые стили */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #212529;
            line-height: 1.6;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Шапка */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .admin-title {
            color: var(--dark);
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        /* Карточки */
        .admin-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .card-title {
            color: var(--dark);
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 500;
        }

        /* Формы */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: #fff;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Кнопки */
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 10px 20px;
            font-size: 16px;
            line-height: 1.5;
            border-radius: var(--border-radius);
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-danger {
            color: #fff;
            background-color: var(--danger);
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-success {
            color: #fff;
            background-color: var(--success);
            border-color: var(--success);
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-logout {
            color: #fff;
            background-color: var(--secondary);
            border-color: var(--secondary);
        }

        .btn-logout:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Уведомления */
        .alert {
            position: relative;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Таблицы */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .admin-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .admin-table tr:nth-child(even) {
            background-color: rgba(0,0,0,0.02);
        }

        .admin-table tr:hover {
            background-color: rgba(0,0,0,0.04);
        }

        /* Бейджи ролей */
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .role-admin {
            background-color: #f8d7da;
            color: #721c24;
        }

        .role-employee {
            background-color: #fff3cd;
            color: #856404;
        }

        .role-client {
            background-color: #d4edda;
            color: #155724;
        }

        /* Вкладки */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .tab {
            padding: 12px 24px;
            cursor: pointer;
            background: #e9ecef;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
            font-weight: 500;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .tab:hover {
            background: #d1d7e0;
        }

        .tab.active {
            background: #fff;
            font-weight: 600;
            border-color: #dee2e6;
            border-bottom-color: #fff;
            margin-bottom: -1px;
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Переключатель статуса */
        .status-toggle {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            padding: 5px;
            transition: var(--transition);
        }

        .status-toggle:hover {
            text-decoration: underline;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .admin-table {
                display: block;
                overflow-x: auto;
            }

            .tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
        }

        /* Дополнительные элементы */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        .text-muted {
            color: var(--gray);
        }
    </style>
</head>
<body>
<!-- [Сохраните всю HTML-разметку из предыдущего кода] -->
<!-- ... (ваша существующая разметка с вкладками и формами) ... -->

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

    // Инициализация первой вкладки
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.tab').click();
    });
</script>
</body>
</html>