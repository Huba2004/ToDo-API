#To-Do API
PHP: 8.3.3
Composer: 2.7.1

#Require slim/slim (4.0) //https://www.slimframework.com
#Nyholm PSR-7 Factory + ServerRequest Creator
#Sqlite3

#A CORS megoldás(
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE);
header('Access-Control-Allow-Headers: Content-Type, Authorization');
MÁRKUS ANDRÁSTÓL származik.
Live Server használata kötelező ennek értelmében.
#Indítás: php -S localhost:8000 app.php

ENDPOINTS:
GET /todok —> Összes To-DO kilistázása, lekérése az adatbázisból
POST /ujtodok —> Új To-Do felvételének lehetősége
DELETE /todok —> törlési lehetőség

A szerkesztés törlés és újra felvesz elv megvalósítása alapján történik.

#Felhasznált linkek, segédletek:
https://releases.jquery.com/
https://www.php.net/manual/en/book.sqlite3.php
https://www.slimframework.com/
https://www.w3schools.com/
https://github.com/jamalhassouni/Todo-List-API
