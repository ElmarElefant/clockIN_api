<?php

abstract class BaseController{

    protected ?Database $Database = null;

    /**
     * Abgeleitete Klassen übergeben den Tabellenname an diese Basisklasse.
     */
    abstract function tableName() : string;

    /**
     * Ein Array mit den Namen der Actions auf die ohne API-Token zugegriffen werden kann.
     * Ohne Angaben sind alle Actions privat uns es muss ein gültiger API-Token angegeben werden.
     * 
     * @return  array   Ein Array mit den Namen der Action-Funktionen als String (Bsp. selectAllAction).
     */
    abstract function publicActions() : array;

    /**
     * Ein Array mit den Namen der Actions auf die nur mit einem API-Token dass zu einem Admin-Konto gehört zugegriffen werden kann.
     * 
     * @return  array   Ein Array mit den Namen der Action-Funktionen als String (Bsp. insertAction).
     */
    abstract function adminActions() : array;


    public function __construct(Database $Database) {
        $this->Database = $Database;
    }

    /**
     * Kontrolliert, ob die benötigten Berechtigungen vorliegt.
     * 
     * @param   string          $actionName     Name der Function. Kann mit __FUNCTION__ abgefragt werden.
     * @param   Authentication  $auth           Object mit der Authetification.
     * @param   Output          $output         ByRef: Hier wird ein Output-Objekt zurückgegeben, wenn die nötigen Berechtigungen fehlen.
     * 
     * @return  bool    True wenn die Berechtigung zum Ausführen der Funktion vorliegt, False wenn diese fehlt. Bei False wird ein $output ausgegeben.
     */
    private function checkAuth(string $actionName, Authentication $auth, ?Output &$output) : bool {
        if(is_null($auth)){
            $output = new Output(null, eState::Error, eStatusCode::Error, 'Authetifikation konnte nicht durchgeführt werden.');
            return false;
        }
        if( in_array( $actionName, $this->adminActions()) ){
            //Actions nur mit Admin-Rechten
            if($auth->getIsValidAdminToken()){
                return true;
            }else{
                $output = new Output(null, eState::Error, eStatusCode::Unauthorized, 'Für diese Funktion ist ein gueltiger API-Token mit Admin-Rechten notwendig.');
                return false;
            }
        }
        if( in_array( $actionName, $this->publicActions()) ){
            //Frei zugängliche Action
            return true;
        }
        if( in_array( '*', $this->adminActions()) ){
            //Alle Actions sind Admin-Actions, ausser Sie sind explizit als Public definiert.
            if($auth->getIsValidAdminToken()){
                return true;
            }else{
                $output = new Output(null, eState::Error, eStatusCode::Unauthorized, 'Für diese Funktion ist ein gueltiger API-Token mit Admin-Rechten notwendig.');
                return false;
            }
        }
        //Actions die weder Admin noch Public sind, sind Private und benötigen einen API-Token:
        if($auth->getIsValidToken()){
            return true;
        }else{
            $output = new Output(null, eState::Error, eStatusCode::Unauthorized, 'Für diese Funktion ist ein gueltiger API-Token notwendig.');
            return false;
        }
    }

    /**
     * Kontrolliert, ob die Methode (GET, POST, etc.) zulässig ist.
     * 
     * @param   string          $method         Zu prüfende Methode
     * @param   array           $validMethods   Ein Array mit den zulässigen Methoden als String
     * @param   Output          $output         ByRef: Hier wird ein Output-Objekt zurückgegeben, wenn die nötigen Berechtigungen fehlen.
     * 
     * @return  bool    True wenn die Methode im Array enthalten ist, ansonsten False. Bei False wird ein $output ausgegeben.
     */
    private function checkMethod(string $method, array $validMethods,?Output &$output ) : bool {
        if( $method == "" || !is_array($validMethods) ){
            $output = new Output(null, eState::Error, eStatusCode::MethodNotAllowed, 'Methode für diese Action nicht zulässig.');
            return false;
        }
        if( in_array( $method, $validMethods ) ){
            return true;
        }else{
            $output = new Output(null, eState::Error, eStatusCode::MethodNotAllowed, 'Methode für diese Action nicht zulässig.');
            return false;
        }
    }


    /**
     * Sucht nach einem Datensatz mit der gewünschten ID in der angegebene Tabelle
     * 
     * @param   string  $table  Name der Tabelle in der nach der ID gesucht wird.
     * @param   int     $id     ID des Datensatzes
     * 
     * @return  Output  Output mit dem gefundenen Datensatz. Wenn nichts gefunden wird, wird als result ein leeres Array ausgegeben. Bei einem Fehler die Fehlerbeschreibung.
     */
    protected function selectIdByTable(string $table, int $id) : Output {
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $res = $this->Database->getId($table, $id);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Fehler bei Datenbankabfrage.");
        }else{
            $payload = [
                'table' => $table,
                'count' => count($res),
                'id' => $id
            ];
            return new Output($res, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }


    /**
     * Die werte eines Datensatzes anhand des Table-Namens und der ID ändern.
     * 
     * @param   string  $table  Name der Tabelle in der nach der ID gesucht wird.
     * @param   int     $id     ID des Datensatzes der geändert wird
     * @param   ?object $values Werte die geändert werden sollen. Ein Object mit jeweils dem Key der dem Spaltenname entspricht und als value der neue Wert
     * 
     * @return  Output  Output mit Informationen. Bei einem Fehler die Fehlerbeschreibung.
     */
    protected Function updateIdByTable(string $table, int $id, ?object $values) : Output {
        if( !is_object($values) ){
            return new Output(null, eState::Error, eStatusCode::Conflict, 'Die zu ändernden Werte müssen als Object angegeben werden.');
        }
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $res = $this->Database->updateById($table, $id, $values);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Der Datensatz konnten nicht geändert werden.");
        }{
            $payload = [
                'table' => $table,
                'id' => $id
            ];            
            return new Output(null, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }
    

    /**
     * Mehrere Datensätze einer Tabelle ändern.
     * 
     * @param   string  $table  Name der Tabelle in der die Datensätze geändert werden.
     * @param   ?array  $values Array von Objecten. Jedes Object muss ein 'id' Property mit der id des zu ändernden Datensatzes enthalten. Ausserdem entspricht jedes Property des Objectes einer Column der $table.
     *                          Es müssen nur die Columns angegeben werden, die geändert werden sollen.
     * 
     * @return  Output  Output mit den Resultaten oder Infos bei einem Fehler.
     */
    protected Function updateByTable(string $table, ?array $values) : Output {
        if( !is_array($values) || count($values) == 0 ){
            return new Output(null, eState::Error, eStatusCode::Conflict, 'Values müssen als Array angegeben werden, mit einem Objekt für jeden zu änderden Datensatz der ein Property id mit der Indentifikation des Datensatztes enthält.');
        }
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $res = $this->Database->update($table, $values);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Ein Fehler ist aufgetretten. Datensätze konnten nicht geändert werden.");
        }{
            $payload = [
                'table' => $table,
                'updated' => $res
            ];            
            return new Output(null, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }


    /**
     * Gibt alle Dantensätze der gewünschten Tabelle zurück
     * 
     * @param   string  $table  Name der Tabelle in der nach der ID gesucht wird.
     * @param   int     $limit  Begrenzung der ausgegebenen Datensätze. Bei Limit -1 werden alle ausgegeben!
     * @param   int     $offset Es wird nicht ab dem Ersten sondern erst der n-te Datensatz zurückgegeben. Bei 0 wird ab dem ersten ausgegeben.
     * 
     * @return  Output   Die gefundenen Datensätze als Output.
     */
    protected function selectAllByTable(string $table, int $limit = -1, int $offset = 0) : Output {
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $tableCount = $this->Database->getTableCount($table);
        if( is_null($tableCount)){
            return new Output(null, eState::Error, eStatusCode::Error, "Konnte Anzahl Datensätze der Tabelle nicht abrufen.");
        }
        if($offset >= $tableCount){
            //Der Anfang der abgefragten Datensätze ist grösser als der effektive Count. Wir verschieden den Offset.
            $offset = max(0, $tableCount - $limit);
        }
        $payload = [
            'table' => $table,
            'limit' => $limit,
            'offset' => $offset,
            'tablecount' => $tableCount
        ];
        if( $tableCount == 0){
            //Keine Datensätze in der Tabelle.
            $payload['count'] = 0;
            return new Output([], eState::Success, eStatusCode::Ok, null, $payload);
        }
        if( $limit > 0 ){
            if ($offset + $limit < $tableCount){
                $payload['next'] = API_URL . $table . "?limit=$limit&offset=" . min($offset+$limit, $tableCount-1);
            }
            if( $offset > 0 ){
                $payload['previous'] = API_URL .  $table . "?limit=$limit&offset=" . max($offset-$limit, 0);
            }
        }
        $res = $this->Database->getAll($table, $limit, $offset);
        if(is_null($res)){
            return new Output(null, eState::Error, eStatusCode::Error, "Fehler bei Datenbankabfrage.");
        }else{
            $payload['count'] = count($res);
            return new Output($res, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }

    
    /**
     * Erstellt Datensätze in einer Tabelle
     * 
     * @param   string  $table      Name der Tabelle.
     * @param   array   $values     Die Werte. Ein Array von Objekten. Jedes Objekt muss die entsprechenden Spaltennamen als Keys aufweisen.
     * 
     * @return  Output   Informationen zur Durchführung als Output.
     */
    protected Function insertByTable(string $table, ?array $values) : Output {
        //Im Payload muss ein Array an Datensätzen vorliegen (Datensätze als Object), die erstellt werden sollen.
        if( !is_array($values) || count($values) == 0 ){
            return new Output(null, eState::Error, eStatusCode::Conflict, 'Values müssen als Array angegeben werden, mit einem Objekt für jeden zu erstellenden Datensatz.');
        }
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }

        $res = $this->Database->insertInto($table, $values);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Die Datensätze konnten nicht erstellt werden.");
        }{
            $payload = [
                'table' => $table,
                'new' => $res,
                'count' => count($res)
            ];            
            return new Output(null, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }


    /**
     * Datensatz anhand einer ID löschen
     * 
     * @param   string  $table  Name der Tabelle in der nach der ID gesucht wird.
     * @param   int     $id     ID des Datensatzes der geändert wird
     * 
     * @return  Output  Output mit Informationen. Bei einem Fehler die Fehlerbeschreibung.
     */
    protected Function deleteIdByTable(string $table, int $id) : Output {
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $res = $this->Database->deleteById($table, $id);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Der Datensatz konnten nicht gelöscht werden.");
        }{
            $payload = [
                'table' => $table,
                'id' => $id,
                'count' => $res
            ];            
            return new Output(null, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }


    /**
     * Mehrere Datensätze einer Tabelle löschen.
     * 
     * @param   string  $table  Name der Tabelle in der die Datensätze gelöscht werden.
     * @param   ?array  $values Array mit Id's die gelöscht werden sollen.
     * 
     * @return  Output  Output mit den Resultaten oder Infos bei einem Fehler.
     */
    protected Function deleteByTable(string $table, ?array $values) : Output {
        if( !is_array($values) || count($values) == 0 ){
            return new Output(null, eState::Error, eStatusCode::Conflict, 'Values müssen als Array angegeben werden, mit der Id für jeden zu löschenden Datensatz.');
        }
        if( is_null($this->Database)){
            return new Output(null, eState::Error, eStatusCode::Error, "Interner Fehler. Controller Datenbank nicht definiert.");
        }
        $res = $this->Database->delete($table, $values);
        if( is_null($res) ){
            return new Output(null, eState::Error, eStatusCode::Error, "Ein Fehler ist aufgetretten. Datensätze konnten nicht gelöscht werden.");
        }{
            $payload = [
                'table' => $table,
                'deleted' => $values,
                'count' => $res
            ];            
            return new Output(null, eState::Success, eStatusCode::Ok, null, $payload);
        }
    }







#region -----Actions

    /**
     * Sucht nach der in den Breadcrumbs angebenen ID und gibt das Resulta als Output zurück.
     * GET ist als Methode zulässig
     * 
     * @param   array   $breadcrumbs     Die einzelnen Teile der URI als Array. Der LETZTE Teil muss die ID sein! Bsp. api/table/1
     * @param   array   $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed   $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON 
     * @param   Authentication  $auth    Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     * 
     * @return  Output  Output-Classe mit den gefundenen Werten.
     */
    public function selectIdAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['GET'], $output) ){ return $output; }

        if( count($breadcrumbs) > 0 && is_numeric($breadcrumbs[array_key_last($breadcrumbs)]) ){
            return $this->selectIdByTable( $this->tableName(), $breadcrumbs[array_key_last($breadcrumbs)] );
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }
 
    /**
     * Gibt alle Datensätze zurück als Output-Classe
     * GET ist als Methode zulässig
     * 
     * @param   array           $breadcrumbs     Die einzelnen Teile der URI als Array.
     * @param   array           $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed           $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON 
     * @param   Authentication  $auth            Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     * 
     * @return  Output  Output-Classe mit den gefundenen Werten.
     */
    public function selectAllAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['GET'], $output) ){ return $output; }

        $limit = isset($args['limit']) ? $args['limit'] : -1;
        $offset = isset($args['offset']) ? $args['offset'] : 0;
        return $this->selectAllByTable( $this->tableName(), $limit, $offset );
    }
    

    /**
     * Erstellt einen oder mehrere Datensätze
     * POST ist als Methode zulässig.
     * 
     * @param   array           $breadcrumbs     Die einzelnen Teile der URI als Array.
     * @param   array           $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed           $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON 
     * @param   Authentication  $auth            Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string          $method          Aufrufmethode (GET, POST, PUT, DELETE)
     * 
     * @return  Output  Output-Classe mit den gefundenen Werten.
     */
    public function insertAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['POST'], $output) ){ return $output; }
        
        if( is_array($payload)){
            return $this->insertByTable( $this->tableName(), $payload );
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }


    /**
     * Aendert einen Datensatz, identifiziert durch eine Id.
     * PUT, PATCH sind als Methoden zulässig.
     * 
     * @param   array   $breadcrumbs     Die einzelnen Teile der URI als Array. Der LETZTE Teil muss die ID sein! Bsp. api/table/1
     * @param   array   $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed   $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON. Dieser muss in PHP als Object decodiert worden sein.
     * @param   Authentication  $auth    Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     */
    public function updateIdAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['PUT', 'PATCH'], $output) ){ return $output; }
        
        if( count($breadcrumbs) > 0 && is_numeric($breadcrumbs[array_key_last($breadcrumbs)]) && is_object( $payload ) ){
            return $this->updateIdByTable( $this->tableName(), $breadcrumbs[array_key_last($breadcrumbs)], $payload );
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }


    /**
     * Aendert mehrere Datensätz, identifiziert jeweils durch die angegebene Id.
     * PUT, PATCH sind als Methoden zulässig.
     * 
     * @param   array   $breadcrumbs     Die einzelnen Teile der URI als Array.
     * @param   array   $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed   $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON.
     *                                   Dieser muss in PHP als Array von Objecten decodiert worden sein. Jedes Object muss ein 'id' Property mit der id des zu ändernden Datensatzes enthalten.
     * @param   Authentication  $auth    Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     */
    public function updateAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['PUT', 'PATCH'], $output) ){ return $output; }

        if( is_array( $payload ) ){
            return $this->updateByTable( $this->tableName(), $payload);
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }


    /**
     * Löschen einen Datensatz, identifiziert durch eine Id.
     * DELETE sind als Methoden zulässig.
     * 
     * @param   array   $breadcrumbs     Die einzelnen Teile der URI als Array. Der LETZTE Teil muss die ID sein! Bsp. api/table/1
     * @param   array   $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed   $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON. Dieser muss in PHP als Object decodiert worden sein.
     * @param   Authentication  $auth    Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     */
    public function deleteIdAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['DELETE'], $output) ){ return $output; }
        
        if( count($breadcrumbs) > 0 && is_numeric($breadcrumbs[array_key_last($breadcrumbs)]) ){
            return $this->deleteIdByTable( $this->tableName(), $breadcrumbs[array_key_last($breadcrumbs)] );
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }


    /**
     * Löschen mehrere Datensätz, identifiziert jeweils durch die angegebene Id.
     * POST sind als Methoden zulässig.
     * 
     * @param   array   $breadcrumbs     Die einzelnen Teile der URI als Array.
     * @param   array   $args            Die mit der Anfrage übergebenen GET oder POST-Parameter
     * @param   mixed   $payload         Decodierter, mit dem Body der Anfrage mitgelieferter JSON.
     *                                   Dieser muss in PHP als Array von Objecten decodiert worden sein. Jedes Object muss ein 'id' Property mit der id des zu ändernden Datensatzes enthalten.
     * @param   Authentication  $auth    Verwaltet die Authentifikation, mit dem API-Token.
     * @param   string  $method          Aufrufmethode (GET, POST, PUT, DELETE)
     */
    public function deleteAction(array $breadcrumbs, array $args, mixed $payload, Authentication $auth, string $method) : Output {
        if( !$this->checkAuth(__FUNCTION__, $auth, $output) ){ return $output; }
        if( !$this->checkMethod($method, ['POST'], $output) ){ return $output; }
        if( is_array( $payload ) ){
            return $this->deleteByTable( $this->tableName(), $payload);
        }else{
            return new Output(null, eState::Error, eStatusCode::NotImplemented, 'Diese Action kann mit diesen Argumenten nicht ausgeführt werden.');
        }
    }


#endregion

}