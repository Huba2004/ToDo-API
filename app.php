<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();

$container = $app->getContainer();
$container["db"] = function ($container) {
    $dbPath = __DIR__ . "/todos.db";
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS todos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tema TEXT NOT NULL,
            leiras TEXT,
            kategoria TEXT,
            felveteli_ido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    return $pdo;
};

$app->run();
