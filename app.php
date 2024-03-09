<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();

// Adatbázis létrehozása és inicializálása
$adatbazisUtvonal = __DIR__ . "/todo.db";
$adatbazisLetezik = file_exists($adatbazisUtvonal);

$adatbazis = new SQLite3($adatbazisUtvonal);
if (!$adatbazisLetezik) {
    $adatbazis->exec(
        "CREATE TABLE IF NOT EXISTS todok (id INTEGER PRIMARY KEY, teendo TEXT, kategoria TEXT)"
    );
    $adatbazis->exec(
        'INSERT INTO todok (teendo, kategoria) VALUES ("Bevásárlás", "Fontos")'
    );
    $adatbazis->exec(
        'INSERT INTO todok (teendo, kategoria) VALUES ("Tanulás", "Sürgős")'
    );
}

// Összes TODO lekérdezése
$app->get("/todok", function (Request $request, Response $response) use (
    $adatbazis
) {
    $todok = [];
    $lekerdez = $adatbazis->query("SELECT * FROM todok");
    while ($sor = $lekerdez->fetchArray(SQLITE3_ASSOC)) {
        $todok[] = $sor;
    }
    $payload = json_encode($todok);
    $response->getBody()->write($payload);
    return $response->withHeader("Content-Type", "application/json");
});

// Új TODO hozzáadása
$app->post("/ujtodok", function (Request $request, Response $response) use (
    $adatbazis
) {
    $adatok = (array) $request->getParsedBody();
    $lekerdezes = $adatbazis->prepare(
        "INSERT INTO todok (teendo, kategoria) VALUES (:teendo, :kategoria)"
    );
    $lekerdezes->bindValue(":teendo", $adatok["teendo"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->execute();
    return $response->withJson(["uzenet" => "Új teendő létrehozva"]);
});

// TODO módosítása
$app->put("/todok/{id}", function (
    Request $request,
    Response $response,
    array $args
) use ($adatbazis) {
    $id = $args["id"];
    $adatok = (array) $request->getParsedBody();
    $lekerdezes = $adatbazis->prepare(
        "UPDATE todok SET teendo = :teendo, kategoria = :kategoria WHERE id = :id"
    );
    $lekerdezes->bindValue(":teendo", $adatok["teendo"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();
    return $response->withJson(["uzenet" => "Teendő módosítva"]);
});

// TODO törlése
$app->delete("/todok/{id}", function (
    Request $request,
    Response $response,
    array $args
) use ($adatbazis) {
    $id = $args["id"];
    $lekerdezes = $adatbazis->prepare("DELETE FROM todok WHERE id = :id");
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();
    return $response->withJson(["uzenet" => "Teendő törölve"]);
});

$app->run();
