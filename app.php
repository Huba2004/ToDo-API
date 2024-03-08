<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/vendor/autoload.php";

// Adatbázis csatlakozás és táblák létrehozása
$adatbazis = new PDO("sqlite:" . __DIR__ . "/todos.db");
$adatbazis->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$adatbazis->exec("CREATE TABLE IF NOT EXISTS todos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kategoria TEXT NOT NULL,
    leiras TEXT NOT NULL,
    kesz BOOLEAN NOT NULL DEFAULT 0
)");

$app = AppFactory::create();

// Kérések kezelése
// Minden TODO lekérdezése
$app->get("/todos/osszes", function (Request $keres, Response $valasz) use (
    $adatbazis
) {
    $utasitas = $adatbazis->query("SELECT * FROM todos");
    $todos = $utasitas->fetchAll();
    $valasz->getBody()->write(json_encode($todos));
    return $valasz->withHeader("Content-Type", "application/json");
});

// Új TODO hozzáadása
$app->post("/todos", function (Request $keres, Response $valasz) use (
    $adatbazis
) {
    $adat = $keres->getParsedBody();
    $utasitas = $adatbazis->prepare(
        "INSERT INTO todos (kategoria, leiras, kesz) VALUES (:kategoria, :leiras, :kesz)"
    );
    $utasitas->bindParam(":kategoria", $adat["kategoria"]);
    $utasitas->bindParam(":leiras", $adat["leiras"]);
    $kesz = $adat["kesz"] ?? 0; // Alapértelmezett érték, ha nem adták meg
    $utasitas->bindParam(":kesz", $kesz);
    $utasitas->execute();
    return $valasz->withStatus(201);
});

// TODO frissítése
$app->put("/todos/{id}", function (
    Request $keres,
    Response $valasz,
    $argumentumok
) use ($adatbazis) {
    $adat = $keres->getParsedBody();
    $utasitas = $adatbazis->prepare(
        "UPDATE todos SET kategoria = :kategoria, leiras = :leiras, kesz = :kesz WHERE id = :id"
    );
    $utasitas->bindParam(":kategoria", $adat["kategoria"]);
    $utasitas->bindParam(":leiras", $adat["leiras"]);
    $utasitas->bindParam(":kesz", $adat["kesz"]);
    $utasitas->bindParam(":id", $argumentumok["id"]);
    $utasitas->execute();
    return $valasz->withStatus(200);
});

// TODO törlése
$app->delete("/todos/{id}", function (
    Request $keres,
    Response $valasz,
    $argumentumok
) use ($adatbazis) {
    $utasitas = $adatbazis->prepare("DELETE FROM todos WHERE id = :id");
    $utasitas->bindParam(":id", $argumentumok["id"]);
    $utasitas->execute();
    return $valasz->withStatus(200);
});

$app->run();
