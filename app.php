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
} else {
    $adatbazis = new SQLite3($adatbazisUtvonal);
}

//Route-ok

//ÖSSZES
$app->get("/todok", function ($request, $response, $args) use ($adatbazis) {
    $todok = [];

    $a = $adatbazis->query("SELECT * FROM teendo");
    while ($sor = $a->fetchArray(SQLITE3_ASSOC)) {
        $todok[] = $sor;
    }

    return $response->withJson($todok); //JSON-ban adatok visszad.
});

$app->post("/teendok", function ($request, $response, $args) use ($adatbazis) {
    $adatok = $request->getParsedBody();

    $lekerdezes = $adatbazis->prepare(
        "INSERT INTO teendo (feladat, kategoria) VALUES (:feladat, :kategoria)"
    );
    $lekerdezes->bindValue(":feladat", $adatok["feladat"]);
    $lekerdezes->bindValue(":kategoria", $adatok["kategoria"]);
    $lekerdezes->execute();

    return $response->withJson(["uzenet" => "Új teendő létrehozva"]);
});

$app->run();
