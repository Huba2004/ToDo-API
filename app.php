<?php
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . "/vendor/autoload.php";

$app = AppFactory::create();

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
//ÖSSZES
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

// Új TODO
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

// MÓDOSÍTÁS
$app->put("/todok/{id}", function (
    Request $request,
    Response $response,
    array $args
) use ($adatbazis) {
    $id = $args["id"];

    $input = json_decode(file_get_contents('php://input'), true);

    $lekerdezes = $adatbazis->prepare(
        "UPDATE todok SET teendo = :teendo, kategoria = :kategoria WHERE id = :id"
    );
    $lekerdezes->bindValue(":teendo", $input["teendo"]);
    $lekerdezes->bindValue(":kategoria", $input["kategoria"]);
    $lekerdezes->bindValue(":id", $id);
    $lekerdezes->execute();
    $response->getBody()->write(json_encode(["uzenet" => "Teendő módosítva"]));
    return $response->withHeader('Content-Type', 'application/x-www-form-urlencoded');
    
});


// TÖRLÉS
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
