<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/../database-test.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        document TEXT
    );

    CREATE TABLE provider_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        provider TEXT,
        operation TEXT,
        payment_id INT,
        FOREIGN KEY(payment_id) REFERENCES artist(payments)
    );

    CREATE TABLE payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL,
        type TEXT,
        country TEXT,
        status TEXT,
        customer_id INT,
        FOREIGN KEY(customer_id) REFERENCES artist(customers)
    );
");

echo "Comando SQL rodado com sucesso!" . PHP_EOL;
