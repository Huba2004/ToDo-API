<?php

use Slim\Factory\AppFactory;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();

$adatbazisUtvonal = __DIR__ . "/data/todo.db";

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

    $a = $adatbazis->query("SELECT * FROM todok");
    while ($sor = $a->fetchArray(SQLITE3_ASSOC)) {
        $todok[] = $sor;
    }

    return $response->withJson($todok); // Válaszként JSON-t küldünk vissza
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

    return $response->withJson(["uzenet" => "Új teendő létrehozva"]);
});
//MÓDOSÍTÁS
$app->put("/todok/{id}", function ($request, $response, $args) use (
    $adatbazis
) {
    $id = $args["id"];
    $adatok = $request->getParsedBody();

    $lekerdezes = $adatbazis->prepare(
        "UPDATE todok SET teendo = :teendo, kategoria = :kategoria WHERE id = :id"
    );
    $lekerdezes->bindValue(":teendo", $adatok["teendo"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();

    return $response->withJson(["uzenet" => "Teendő módosítva"]);
});

//TÖRLÉS
$app->run();
