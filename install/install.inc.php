<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Clock IN API</title>
    <style>
        body{
            font-family: sans-serif;
        }
        p{
            margin: 0;
        }
        .err{
            color: red;
        }
    </style>
</head>
<body>

<?php

if (isset($_POST['submit']) === false) {
    header("location: index.php");
    exit();
}

echo "<h4>API Installation gestartet.</h4>";

//Der Parent-Ordner zu diesem File suchen.
//Da wir im install-Subfolder sind, ist dies die Seiten-URL.
define("SITE_URL", dirname($_SERVER['PHP_SELF'], 2));

//Kontrolle ob ../config Folder existiert, ansonsten erstellen
$configDir = "../config";
if ( !file_exists( $configDir ) && !is_dir( $configDir ) ) {
    mkdir( $configDir );
}
echo "<p>Config-Ordner angelegt.</p>";

//Config Datei schreiben.
$configFile = $configDir ."/config.ini";
$myfile = fopen( $configFile, "w") or die("Unable to open file!");
if ($myfile===false) {
    //Probleme beim Config-Datei schreiben.
    die("<p  class='err'>Config Datei konnte nicht erstellt werden.</p>");
}

fwrite($myfile, "SITE_URL = " . '"' . SITE_URL . '"' . "\n");
fwrite($myfile, "UPLOADS_URL = " . '"' . $uploadsDir . '"' . "\n");
fwrite($myfile, "SERVERNAME = " . '"' . $_POST['servername'] . '"' . "\n");
fwrite($myfile, "DATABASENAME = " . '"' . $_POST['databasename'] . '"' . "\n");
fwrite($myfile, "DATABASEUSERNAME = " . '"' . $_POST['databaseusername'] . '"' . "\n");
fwrite($myfile, "DATABASEUSERPASSWORD = " . '"' . $_POST['databaseuserpassword'] . '"' . "\n");
fclose($myfile);

//Berechtigung der Config Datei ändern.
$res = chmod($configFile, 0600);
if ($res === false) {
    die("<p  class='err'>Berechtigung der Config-Datei können nicht angepasst werden.</p>");
}

echo "<p>Config Datei erfolgreich angelegt.</p>";


//Htaccess schreiben.
$htaccesFile = "../.htaccess";
$myfile = fopen( $htaccesFile, "w") or die("Unable to open file!");
if ($myfile===false) {
    //Probleme beim Config-Datei schreiben.
    die("<p  class='err'>htaccess Datei konnte nicht erstellt werden.</p>");
}

fwrite($myfile, "RewriteEngine On \n");
fwrite($myfile, "RewriteCond %{REQUEST_FILENAME} !-d \n");
fwrite($myfile, "RewriteCond %{REQUEST_FILENAME} !-f \n");
fwrite($myfile, "RewriteRule ^(.*)$ index.php?endpoint=$1 [NC, QSA,L] \n");
fclose($myfile);

//Berechtigung der Config Datei ändern.
$res = chmod($htaccesFile, 0600);
if ($res === false) {
    die("<p  class='err'>Berechtigung der htaccess-Datei können nicht angepasst werden.</p>");
}

echo "<p>htaccess Datei erfolgreich angelegt.</p>";




require_once '../includes/scheme.php';
require_once '../includes/schemeTranslator.php';
require_once '../includes/dbCoreFunctions.php';

$schemes = new schemes();
$schemeTranslator = new SchemeTranslator();

$dsn = "mysql:dbname={$_POST['databasename']};host={$_POST['servername']}";
$conn = new PDO($dsn, $_POST['databaseadmin'], $_POST['databaseadminpassword']);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//zuerst alle Fremdschlüssel (Foreign Keys) die in der Datenbank existieren löschen (nicht nur die aus dem Schema, alle vorhandenen)!
$res = dropAllForeignKeys($conn);
if(!$res){
    echo "<p class='err'>Fehler beim Entfernen der Fremdschlüssel.</p>";
    die();
}else{
    echo "<p>Alle Fremdschlüssel gelöscht.</p>";
}

if(isset($_POST['dropall'])){
    //Inhalt der Datenbank löschen, wenn gewünscht.
    $res = dropAll( $conn );
    if($res){
        echo "<p>Inhalt der Datenbank gelöscht.</p>";
    }else{
        echo "<p class='err'>Inhalt der Datenbank konnte nicht gelöscht werden.</p>";
        die();
    }
}

$tableNames = getTableNames($conn);
$tableSchemes = $schemes->TableSchemes();

foreach ($tableSchemes as $scheme) {
    $name = $scheme['name'];
    if (in_array($name, $tableNames)) {
        //Tabelle ist schon vorhanden
        $sql = $schemeTranslator->alterTable($scheme);
        try {
            $sth = $conn->prepare($sql);
            $sth->execute();
            //Die nachfolgenden Zeilen beheben ein Problem des SQL-Codes oben. Wieso ist nicht bekannt.
            $sql = "SELECT 1;";
            $sth = $conn->prepare($sql);
            $sth->execute();
            $sth->fetchAll();
            $sth->closeCursor();
            echo "<p>Tabelle `$name` geändert.</p>";
        } catch (\Throwable $ex) {
            echo "<p class='err'> Fehler beim Aendern der Tabelle `$name`.</p>";
            die();
        }
    }else{
        //Tabelle noch nicht vorhanden
        $sql = $schemeTranslator->createTable($scheme);
        try {
            $sth = $conn->prepare($sql);
            $sth->execute();
            $sth->fetchAll();
            $sth->closeCursor();
            echo "<p>Tabelle `$name` erstellt.</p>";
        } catch (\Throwable $ex) {
            print_r($ex);
            echo "<p class='err'> Fehler beim Erstellen der Tabelle `$name`.</p>";
            die();
        }
    }
}

//Nicht mehr verwendete Tabellen löschen!
if(dropUnusedTables($conn, $schemes)) {
    echo "<p> Nach nicht mehr benötigten Tabellen gesucht.</p>";
}else{
    echo "<p class='err'> Fehler beim Löschen von nicht mehr benötigten Tabellen.</p>";
    die();
}

//Nicht mehr verwendete Columns löschen.
if(dropUnusedColumns($conn, $schemes)) {
    echo "<p> Nach nicht mehr benötigten Spalten gesucht.</p>";
}else{
    echo "<p class='err'> Fehler beim Löschen von nicht mehr benötigten Spalten.</p>";
    die();
}

//Alle Fremdschlüssel anlegen. Falls ein Schlüssel schon existiert, wird dieser überprungen.
$sql = $schemeTranslator->crateAllForeignKeys($tableSchemes);
if(!empty($sql)){
    try {
        $sth = $conn->prepare($sql);
        $sth->execute();
        $sth->closeCursor();
        echo "<p>Fremdschlüssel erstellt.</p>";
    } catch (\Throwable $th) {
        print_r($th);
        echo "<p class='err'> Fehler beim Erstellen der Fremdschlüssel.</p>";
        die();
    }

}

//Admin-User erstellen:
$n = 2;
$apitoken = bin2hex(random_bytes($n)) . "-" . bin2hex(random_bytes($n)) . "-" . bin2hex(random_bytes($n));
$apitoken = strtoupper($apitoken);
$hashedPassword = password_hash($_POST['adminpassword'], PASSWORD_DEFAULT);
$username =  $_POST['adminname'];
$email = $_POST['adminemail'];
$admin = 1;
$sql = "INSERT INTO users(username, password, apitoken, email, administrator) VALUES (:username, :password, :apitoken, :email, :admin)";
try {
    $sth = $conn->prepare($sql);
    $sth->execute([
        'username' => $username,
        'password' => $hashedPassword,
        'apitoken' => $apitoken,
        'email' => $email,
        'admin' => $admin
    ]);
    echo "<p>Admin-User hinzugefügt.</p>";
} catch (\Throwable $th) {
    echo "<p class='err'> Fehler beim Anlegen des Admin-Users.</p>";
    die();
}

//Installation abgeschlossen
echo "<h4>Installation erfolgreich abgeschlossen.</h4>"

?>
</body>
</html>
