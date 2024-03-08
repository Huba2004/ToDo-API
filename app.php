<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();
//ADATBÁZIS létrehozása, táblák létrhozása [SQLite]
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

//TESZT ADATOKKAL VALÓ FELTÖLTÉS
$todoAdatok = [
    [
        "Házi feladat elkészítése",
        "Matematika házi feladat",
        "Iskola",
        "2024-03-08 10:00:00",
    ],
    [
        "Bevásárolás",
        "Tej, kenyér, tojás",
        "Bevásárlás ALDI-ban",
        "2024-03-08 15:30:00",
    ],
];
//Elemek feltöltése a todoAdatok
foreach ($todoAdatok as $todo) {
    $stmt = $pdo->prepare(
        "INSERT INTO todos (tema, leiras, kategoria, felveteli_ido) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute($todo);
}
//Elemek lekérdezése, visszadás JSON formátumban
$app->get("/osszes", function (Request $request, Response $response) use (
    $pdo
) {
    $stmt = $pdo->query("SELECT * FROM todos");
    $osszes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($osszes));
    return $response->withHeader("Content-Type", "application/json");
});
