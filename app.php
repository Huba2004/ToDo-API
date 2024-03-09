<?php

use Slim\Factory\AppFactory;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();

$adatbazisUtvonal = __DIR__ . "/todo.db";

if (!file_exists($adatbazisUtvonal)) {
    $adatbazis = new SQLite3($adatbazisUtvonal);

    $adatbazis->exec(
        "CREATE TABLE IF NOT EXISTS todok (id INTEGER PRIMARY KEY, teendo TEXT, kategoria TEXT)"
    );

    $adatbazis->exec(
        'INSERT INTO todok (teendo, kategoria) VALUES ("Bevásárlás", "Fontos")'
    );

    $adatbazis->exec(
        'INSERT INTO todok (teendo, kategoria) VALUES ("Tanulás", "Sürgős")'
    );
} else {
    $adatbazis = new SQLite3($adatbazisUtvonal);
}

// ÖSSZES
$app->get("/todok", function ($request, $response, $args) use ($adatbazis) {
    $todok = [];
    $lekerdez = $adatbazis->query("SELECT * FROM todok");

    while ($sor = $lekerdez->fetchArray(SQLITE3_ASSOC)) {
        array_push($todok, $sor);
    }

    $response = $response->withJson($todok);
    return $response;
});

// ÚJ ELEM
$app->post("/ujtodok", function ($request, $response, $args) use ($adatbazis) {
    $adatok = $request->getParsedBody();

    $lekerdezes = $adatbazis->prepare(
        "INSERT INTO todok (teendo, kategoria) VALUES (:teendo, :kategoria)"
    );

    $lekerdezes->bindValue(":teendo", $adatok["teendo"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->execute();

    $response = $response->withJson(["uzenet" => "Új teendő létrehozva"]);
    return $response;
});

// MÓDOSÍTÁS
$app->put("/todok/:id", function ($request, $response, $args) use ($adatbazis) {
    $id = $args["id"];
    $adatok = $request->getParsedBody();

    $lekerdezes = $adatbazis->prepare(
        "UPDATE todok SET teendo = :teendo, kategoria = :kategoria WHERE id = :id"
    );

    $lekerdezes->bindValue(":teendo", $adatok["teendo"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();

    $response = $response->withJson(["uzenet" => "Teendő módosítva"]);
    return $response;
});

// TÖRLÉS
$app->delete("/todok/:id", function ($request, $response, $args) use (
    $adatbazis
) {
    $id = $args["id"];

    $lekerdezes = $adatbazis->prepare("DELETE FROM todok WHERE id = :id");
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();

    $response = $response->withJson(["uzenet" => "Teendő törölve"]);
    return $response;
});

$app->run();
