<?php

/**
 * Folgende Anfragen sind möglich:
 * (!) Bei Angaben für Tabellen- oder Column(Spalten)-Namen muss IMMER die Gross-/Kleinschreibung beachtet werden. Sowohl beim Endpoint wie auch in JSON/Payload.
 * (i) #return: Bei allen Anfragen wird ein JSON-Object zurückgegeben. Dieses enthält immer das `status` Property. Hier sind folgende Ausgaben möglich -1: Unbekannt 0: Error 1: Success 2: Warning
 *              Ausserdem wird bei dem meisten Abfragen ein `result` ausgegeben, welches die angeforderten Datenenthält. Tritt ein Fehler auf, wird oft mit `error` eine Info zum Fehler ausgegeben.
 *              Dazu kommen je nach Action weitere Zusatzinformationen.
 * 
 *      ../api/$endpoint/
 *                  $endpoint ist der exakte Tabellenname.
 *          [GET]   --Es werden alle Datensätze zurückgegeben.
 *                  ?limit=n    Es werden nur die Anzahl n Datensätze ausgegeben.
 *                  ?offset=n   Nur in Kombination mit ?limit. Es wird nicht ab dem ersten Datensatz sondern um n versetzt Daten ausgegeben.
 *                  #return     $result     array       Ein Array mit für jeden gefundenen Datensatz ein Object mit allen Columns. Wir kein Datensatz gefunden, ein leeres Array.
 *                              $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $limit      int         Es wird immer ein Limit ausgegeben. Wurde keines angegeben wird -1 ausgeben.
 *                              $offset     int         Es wird auch immer ein Offset ausgegeben, dass ohne Angabe 0 ist. Wird ein Offset angegeben, welches grösser ist als die enthaltenen Datensätze wird dieses angepasst. Somit kann das Rückgabe-$offset von dem verlangten ?offset abweichen.
 *                              $tablecount int         Gibt an, wie viele Datensätze die $table enthält.
 *                              $count      int         Gibt an, wie viele Datensätze unter $result ausgegeben werden.
 *                              $next       string      Ein URI um die nächsten $limit Datensätze abzufragen. Wird nur dann angezeigt, wenn es noch mehr Daten zum Anzeigen gibt.
 *                              $previous   string      Ein URI um die vorhergehenden $limit Datensätze anzuzeigen. Ist nur dann enthalten, wenn es vor den angefragten Daten noch weitere Datensätze gibt.
 *          [POST]  --Einen oder mehrere Datensätze erstellen
 *                  #payload    Als Payload wird nur JSON akzeptiert. Format ist immer ein Array (auch wenn nur ein Datensatz erstellt werden soll). Für jeden zu erstellenden Datensatz ein Objekt. Propertyname muss dem exakten Column-Name entsprechen.
 *                              Bsp: [{"note": "Testnote 1", "userId": 2},{"note": "Testnote 2", "userId": 4}]
 *                              Es müssen alle benötigten Angaben gemacht werden. Bei einer Fehlangabe wird KEIN Datensatz eingefügt. Auch die mit korrekten Angaben.
 *                  #return     $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $new        array       Gibt ein Array mit den Id's der neu erstellten Datensätze zurück. Für jeden neu erstellten Datensatz ein Eintrag im Array mit dem Wert des Primarykey. Dieser als string
 *                              $count      int         Gibt an, wei viele neue Datensätze erstellt wurden.
 *          [PUT][PATCH]    --Einen oder mehrere Datensätze ändern.
 *                  #payload    Als Payload wird nur JSON akzeptiert. Format ist ein Array. Für jeden zu ändernden Datensatz ein Objekt.
 *                              Es muss ein Property mit dem Namen `id`existieren. Dieses gibt an, welcher Datensatz geändert wird. Die übrigen Propertynamen müssen dem exakten Column-Name entsprechen.
 *                              Bsp: [{"id": 42, "note": "Testnote 4", "userId": 1},{"id": 11, "note": "Testnote 6"}]
 *                              Es müssen nur die zu ändernden Werte angegeben werden.
 *                  #return     $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $updated    array       Ein Array mit den Id's aller geänderten Datensätze. Für jeden geänderten Datensatz ist die ID (Primarykey) angeben als Integer.
 *      ../api/$endpoint/$id
 *                  $endpoint ist der exakte Tabellenname.
 *                  $id ist eine Ganzzahl mit welcher der Datensatz anhand des Primarykey (id-column) indentifiziert werden kann.
 *          [GET]   --Es wird der entsprechende Datensatz ausgegeben. Existiert dieser nicht, wird kein Fehler sondern ein leeres Array zurückgegeben.
 *                  #return     $result     array       Ein Array mit dem gefundenen Datensatz als Object mit allen Columns. Wir kein Datensatz gefunden, ein leeres Array.
 *                              $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $count      int         Anzahl der gefundenen Datensätze, also entweder 1 oder 0.
 *                              $id         int         Entspricht der angefragten ID.
 *          [PUT][PATCH]    --Der mit $id identifizierte Datensatz wird geändert.
 *                  #payload    Es wird nur JSON akzeptiert. Ein Objekt, Property-Namen entsprechen den Column-Namen. Es müssen nur die zu ändernden Werte abgefragt werden.
 *                  #return     $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $id         int         Entspricht der angefragten ID.
 *          [DELETE]    --Der mit $id identifizierte Datensatz wird gelöscht. Existiert der Datensatz nicht, wird trotzdem $status Success ausgegeben, jedoch als $count wird 0 zurückgegeben.
 *                  #return     $table      string      Name der Table aus der die Daten sind. Entspricht dem $endpoint.
 *                              $id         int         Entspricht der angefragten ID.
 *                              $count      int         Anzahl der gelöschten Datensätze, also 0 oder 1.
 *      ../api/$endpoint/delete
 *                  $endpoint ist der exakte Tabellenname.
 *                  $action ist delete
 *          [POST]   --Es werden alle mit einer ID angegebenen Datensätze gelöscht.
 *                  #payload    Es wird nur JSON akzeptiert. Ein Array mit allen zu löschenden Ids. Bsp. [12, 44, 1245]
 *                  #return     $table      string      Name der Table. Entspricht dem $endpoint.
 *                              $count      int         Anzahl der wirklich gelöschten Datensätze. Existiert eine Id nicht, wird kein Fehler ausgegeben. Jedoch reduziert sich der Count um 1.
 *                              $deleted    array       Entspricht exakt dem #payload. Auch Ids die nicht vorhanden waren, sind hier aufgeführt. Count kann also vom $count abweichen.
 * 
 *  
 */




//Security
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: content-type, authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Ressourcen laden.
require __DIR__ . "/includes/bootstrap.inc.php";

//Datenbank initialisieren.
try {
    $database = new Database(DB_NAME, DB_SERVER, DB_USER, '9pu%6Py&6d');
} catch (\Throwable $ex) {
    // die(outputString("Fehler beim Verbinden mit der Datenbank.", estate::Error));
    Output::sendNow($ex);
    die();
}

//Classe zur Auswertung der Authentifikation.
$auth = new Authentication($database);

//Methode (GET/POST/PUT oder DELETE)
$method = $_SERVER['REQUEST_METHOD'];

$contentType = $_SERVER["CONTENT_TYPE"];
if($method != 'GET' && $contentType != "" && $contentType != "application/json"){
    //Content-Type. Es wird nur application/json unterstützt
    Output::sendNow("Es wird nur application/json als Content-Type unterstützt!", eState::Error, eStatusCode::UnsupportedMediaType);
    die();
}
$payload = $contentType == "application/json" ? json_decode( file_get_contents('php://input'), false) : null;

//Endpoint festlegen.
//Bsp: ../api/users/14?arg=45
//            |     L>14 ist eine Nummer, deshalb wird die selectIdAction im Controller aufgerufen. Ansonsten wäre dies die Action die im Controller aufgerufen wird.
//            L>Endpoint 'users' führt zum UsersController
//  Sind weitere Breadcrumbs angegeben, werden diese an die Action übergeben. Jede Action muss also erstes Argument ein Array annehmen.
//Bsp: ../api/users/login?username=hans&password=
//            |     L>login ist die Action. Es wird im Controller die Funktion LoginAction($breadcrums, $get) aufgerufen.
//            L>Endpoint 'users' führt zum UsersController
//  $_GET wird ebenfalls als zweites Argument an die Action übergeben.
if( isset($_GET['endpoint'])){
    $breadcrumbs = explode("/", $_GET['endpoint']);
    $breadcrumbs = array_filter($breadcrumbs, fn($value) => !is_null($value) && $value !== '');
    $endpoint = current($breadcrumbs);
    if(count($breadcrumbs) > 1){
        $breadcrumb = next($breadcrumbs);
        $action = is_numeric($breadcrumb) ? $breadcrumb : $breadcrumb . "Action";
    }else{
        $action = null;
    }
    
    //Controller aus dem Endpoint erstellen.
    try {
        $type = ucfirst($endpoint) . "Controller";
        $controller = new $type($database);

    } catch (\Throwable $ex) {
        Output::sendNow("Kein solcher Endpoint gefunden!", eState::Error, eStatusCode::NotImplemented);
        die();
    }

    //Controller-Action ausführen.
    try {
        $args = array_merge($_GET, $_POST);
        if( !is_null($action) && is_numeric($action) && $method == 'GET' ){
            //Einen Datensatz aufgrund einer ID suchen und ausgeben.
            $output = $controller->selectIdAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }elseif( !is_null($action) && is_numeric($action) && in_array($method, ['PUT', 'PATCH']) ){
            //Einen Datensatz aufgrund einer ID ändern
            $output = $controller->updateIdAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }elseif( !is_null($action) && is_numeric($action)  && $method == 'DELETE' ){
            //Einen Datensatz aufgrund einer ID löschen
            $output = $controller->deleteIdAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }elseif ( is_null($action) && $method == 'GET' ) {
            //Alle Datensätze ausgeben.
            $output = $controller->selectAllAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }elseif ( is_null($action) && $method == 'POST' ) {
            //Einen oder mehrere neuen Datensätze erstellen.
            $output = $controller->insertAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }elseif ( is_null($action) && in_array($method, ['PUT', 'PATCH']) ) {
            //Einen oder mehrere Datensätze ändern.
            $output = $controller->updateAction($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }else{
// echo "<br>ACTION: $action";
// echo "<br>METHOD: $method";
            $output = $controller->$action($breadcrumbs, $args, $payload, $auth, $method);
            $output->send();
        }

    } catch (\Throwable $ex) {
        print_r($ex);
        Output::sendNow("Endpoint unterstützt die Funktion nicht!", eState::Error, eStatusCode::NotImplemented);
        die();
    }
    


    // echo "<br><br>";
    // print_r($endpoint);
    // echo "<br>" . $action;
    // echo "<br>";
    // print_r($breadcrumbs);
    // echo "<br>";
    // print_r($_GET);


}else{
    Output::sendNow("Kein Endpunkt angegeben.", eState::Error, eStatusCode::NotImplemented);
    die();
}
