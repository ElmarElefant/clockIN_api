<?php

class Database {
    protected ?PDO $Connection = null;

    public function __construct(string $database, string $server, string $user, string $password) {
        $dsn = "mysql:dbname=$database;host=$server";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->Connection = $conn;
    }

    public function getConnection() : PDO {
        return $this->Connection;
    }


    /**
     * Gibt Anzahl der Datensätze in der gewünschten Table an
     * 
     * @param   string  $table  Name der Tabelle
     * 
     * @return  int|null    Die Anzahl Datensäte oder null bei einem Fehler.
     */
    function getTableCount(string $table) : int|null {
        if(is_null($this->Connection)){
            return null;
        }
        
        try {
            $sql = "SELECT COUNT(*) FROM `$table`;";
            $sth = $this->Connection->prepare($sql);
            $sth->execute();
            $res = $sth->fetchColumn(0);
            return $res;
        } catch (\Throwable $th) {
            return null;            
        }
    }


    /**
     * Alle Datensätze einer Tabelle als Array zurückgeben.
     * 
     * @param   string  $table   Name der Tabelle
     * @param   int     $limit   Anzahl der zurückgegebenen Datensätze
     * @param   int     $offset  Offset wenn nicht Datensätze von Anfang an ausgegeben werden sollen.
     * 
     * @return  array|null    Null bei einem Fehler, ansonsten die Datensätze als Array.
     */
    function getAll(string $table, int $limit = -1, int $offset = 0) : array|null {
        if(is_null($this->Connection)){
            return null;
        }
        $sql = "SELECT * FROM `$table`";
        $param = array();
        if($limit >= 0){
            $sql .= " LIMIT :limit";
            $param['limit'] = [$limit, PDO::PARAM_INT];
            if($offset > 0){
                $sql .= " OFFSET :offset";
                $param['offset'] = [$offset, PDO::PARAM_INT];
            }
        }
        $sql .= ";";
        try {
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $param);
            $sth->execute();
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            $sth->closeCursor();
            return $res;
        } catch (\Throwable $ex) {
            return null;
        }
    }


    /**
     * Den einten Datensatz mit der angefragten ID aus der gewünschten Table zurückgeben.
     * 
     * @param   string  $table   Name der Tabelle
     * @param   int     $id      ID (Primarykey) des gewünschten Datensatzes.
     * 
     * @return  array|null    Array des gefundenen Datensatzes. Wird keiner gefunden ein leeres Array. Null bei einem Fehler.
     */
    function getId(string $table, int $id) : array|null {
        if(is_null($this->Connection)){
            return null;
        }
        $sql = "SELECT * FROM `$table` WHERE `id` = :id;";
        try {
            $sth = $this->Connection->prepare($sql);
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            $sth->closeCursor();
            return $res;
        } catch (\Throwable $ex) {
            return null;
        }
    }


    /**
     * Ein SQL Code ausführen. Rückgabewert mit fetchAll
     * 
     * @param   string      $sql        SQL zum Ausführen.
     * @param   array       $params     Die Parameter als Array. Folgende Schemas sind zulässig: Als KEY muss immer der Name des Parameters angegeben werden.
     *                                  Als Value kann dann entweder direkt eine Wert angegeben werden.
     *                                  Es ist auch möglich als Value ein Array anzugeben. Dann kommt als erstes der Wert und als zweites die Definition des Typs für PDO.
     *                                  Bsp: ['key' => 12]  oder ['key' => [12]]  oder ['key' => [12, PDO::PARAM_INT]]
     * 
     * @return  ?array  Array als Resultat aus dem fetch, oder null bei einem Fehler.
     */
    function fetchAllSQL(string $sql, array $params) : array|null {
        if(is_null($this->Connection)){
            return null;
        }
        try {
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $params);
            $sth->execute();
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            $sth->closeCursor();
            if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
            return $res;
        } catch (\Throwable $ex) {
            // print_r($ex);
            return null;
        }
    }


    /**
     * Datensätze zu einer Tabelle hinzufügen.
     * 
     * @param   string      $table      Name der Tabelle.
     * @param   array       $values     Die Werte. Ein Array von Objekten. Jedes Objekt muss die entsprechenden Spaltennamen als Keys aufweisen.
     * 
     * @return  ?array      Eine Auflistung der ID's aller neuer Datensätze als Array. Oder null wenn ein Fehler aufgetretten ist.
     */
    function insertInto(string $table, array $values) : array|null {
        if(is_null($this->Connection)){
            return null;
        }

        $this->Connection->beginTransaction();

        $insertedIds = array();

        try {
            foreach ($values as &$dataset) {
                $sql1 = "INSERT INTO `$table` (";
                $sql2 = " VALUES (";
                $seperator = "";
                $params = array();
                foreach ($dataset as $key => &$value) {
                    $sql1 .=  $seperator . "`$key`"; 
                    $sql2 .=  $seperator . ":$key"; 
                    $seperator = ", ";
                    $params[$key] = $value;
                }
                $sql1 .= ")";
                $sql2 .= ");";
                $sql = $sql1 . $sql2;
                
                $sth = $this->Connection->prepare($sql);
                $this->bindParam($sth, $params);
                $res = $sth->execute();
                if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
                array_push($insertedIds, $this->Connection->lastInsertId());
                $sth->closeCursor();
            }
        } catch (\Throwable $ex) {
            //Fehler, alles rückgängig machen.
            $this->Connection->rollBack();
            return null;
        }

        //Datensätze erfolgreich eingefügt.
        $this->Connection->commit();
        return $insertedIds;
    }


    /**
     * Ein anhand einer id identifizierter Datensatz ändern.
     * 
     * @param   string          $table  Name der Tabelle
     * @param   int             $id     Id des zu ändernden Datensatzes
     * @param   object|array    Ein object (oder associative Array). Jedes Property/Key muss exakt einem Column-Name entsprechen. Der Wert wird dann dieser Column zugewiesen. Es müssen nur zu ändernde Werte angegeben werden.
     * 
     * @return  bool    True wenn alles funktioniert hat, ansonsten halt False.
     */
    function updateById(string $table, int $id, object|array $values) : bool {
        if(is_null($this->Connection)){
            return false;
        }
        $this->Connection->beginTransaction();
        try {
            $sql = "UPDATE `$table` SET ";
            $seperator = "";
            $params = array();
            foreach ($values as $key => &$value) {
                    $sql .=  $seperator . "`$key` = :$key";
                    $seperator = ", ";
                    $params[$key] = $value;
            }
            $sql .= " WHERE `id` = :id;";
            $params['id'] = $id;
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $params);
            $res = $sth->execute();
            if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
            $sth->closeCursor();
        } catch (\Throwable $ex) {
            //Fehler, alles rückgängig machen.
            $this->Connection->rollBack();
            return false;
        }
        //Datensätze erfolgreich eingefügt.
        $this->Connection->commit();
        return true;
    }


    /**
     * Einer oder mehrere Datensätze ändern.
     * 
     * @param   string  $table      Name der Tabelle
     * @param   array   $values     Die Werte als Array. Für jeden zu ändernden Datensatz muss ein Objekt (darf KEIN Array sein) im Array enthalten sein.
     *                              Dieses muss minimal das Property `id` aufweisen, welches den Primarykey des zu ändernden Datensatz enthält.
     *                              Die restlichen Properties entsprechen exakt den Column-Namen und enthalten die zu ändernden Werte. Es müsse nur zu ändernde Werte angegeben werden.
     * 
     * @return  ?array  Ein Array mit einer Auflistung der Primarykeys aller geänderten Datensätze oder null bei einem Fehler.
     */
    function update(string $table, array $values) : array|null {
        if(is_null($this->Connection)){
            return null;
        }
        $this->Connection->beginTransaction();
        $updatedIds = array();

        try {
            foreach ($values as &$dataset) {
                if( !property_exists($dataset, 'id') ){ throw new Exception("ID ist nicht festgelegt in diesem Datensatz.", 1); }
                $sql = "UPDATE `$table` SET ";
                $seperator = "";
                $params = array();
                foreach ($dataset as $key => &$value) {
                        $sql .=  $seperator . "`$key` = :$key";
                        $seperator = ", ";
                        $params[$key] = $value;
                }
                $sql .= " WHERE `id` = :id;";
                $params['id'] = $dataset->id;
                $sth = $this->Connection->prepare($sql);
                $this->bindParam($sth, $params);
                $res = $sth->execute();
                if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
                $sth->closeCursor();
                array_push($updatedIds, $dataset->id);
            }
        } catch (\Throwable $ex) {
            //Fehler, alles rückgängig machen.
            $this->Connection->rollBack();
            return null;
        }
        //Datensätze erfolgreich eingefügt.
        $this->Connection->commit();
        return $updatedIds;
    }


    /**
     * Ein anhand einer id identifizierter Datensatz löschen.
     * 
     * @param   string  $table  Name der Tabelle
     * @param   int     $id     Id des Datensatzes der gelöscht wird.
     * 
     * @return  bool    Die Anzahl der gelöschten Datensätzte oder null bei einem Fehler.
     */
    function deleteById(string $table, int $id) : null|int {
        if(is_null($this->Connection)){
            return null;
        }
        $deleted = 0;
        try {
            $sql = "DELETE FROM `$table` WHERE `id` = :id;";
            $params['id'] = $id;
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $params);
            $res = $sth->execute();
            if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
            $deleted = $sth->rowCount();
            $sth->closeCursor();
        } catch (\Throwable $ex) {
            //Fehler, alles rückgängig machen.
            return null;
        }
        //Datensätze erfolgreich eingefügt.
        return $deleted;
    }


    /**
     * Einer oder mehrere Datensätze löschen.
     * 
     * @param   string  $table      Name der Tabelle
     * @param   array   $values     Eine Liste mit Ids, die den Primarykeys entsprechen die gelöscht werden sollen.
     * 
     * @return  ?int  Die Anzahl der effektiv gelöschten Datensätze oder null bei einem Fehler.
     */
    function delete(string $table, array $values) : null|int {
        if(is_null($this->Connection)){
            return null;
        }
        $deleted = 0;
        try {
            $sql = "DELETE FROM `$table` WHERE `id` IN (";
            $seperator = "";
            $params = array();
            for ($i=0; $i < count($values); $i++) {
                $sql .= $seperator . ":id" . $i;
                $params['id' . $i] = $values[$i];
                $seperator = ", ";
            }
            $sql .= ");";
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $params);
            $res = $sth->execute();
            if( !$res ){ throw new Exception("Unbekannter Datenbank Ausführungsfehler.", 1); }
            $deleted = $sth->rowCount();
            $sth->closeCursor();
        } catch (\Throwable $ex) {
            //Fehler
            return null;
        }
        //Datensätze erfolgreich eingefügt.
        return $deleted;
    }


    /**
     * Ein SQL Code ausführen. Kein fetch, nur execute.
     * 
     * @param   string      $sql        SQL zum Ausführen.
     * @param   array       $params     Die Parameter als Array. Folgende Schemas sind zulässig: Als KEY muss immer der Name des Parameters angegeben werden.
     *                                  Als Value kann dann entweder direkt eine Wert angegeben werden.
     *                                  Es ist auch möglich als Value ein Array anzugeben. Dann kommt als erstes der Wert und als zweites die Definition des Typs für PDO.
     *                                  Bsp: ['key' => 12]  oder ['key' => [12]]  oder ['key' => [12, PDO::PARAM_INT]]
     * 
     * @return  boll  False bei einem Fehler, ansonsten True.
     */
    function executeSQL(string $sql, array $params) : bool {
        if(is_null($this->Connection)){
            return false;
        }
        try {
            $sth = $this->Connection->prepare($sql);
            $this->bindParam($sth, $params);
            // $u1 = "1";
            // $u2 = "Supperarschloch";
            // $sth->bindParam("userId", $u1);
            // $sth->bindParam("feld2", $u2);
            $res = $sth->execute();
            $sth->closeCursor();
            return $res;
        } catch (\Throwable $ex) {
            print_r($ex);
            return false;
        }
    }


    /**
     * Erstellt aus einem Array die Bindings für eine Statement.
     * 
     * @param   PDOStatement     $sth       Das Statement. Darf nicht null sein, sonst wird eine Exeption ausgelöst.
     * @param   array            $params    Die Parameter als Array. Folgende Schemas sind zulässig: Als KEY muss immer der Name des Parameters angegeben werden.
     *                                      Als Value kann dann entweder direkt eine Wert angegeben werden.
     *                                      Es ist auch möglich als Value ein Array anzugeben. Dann kommt als erstes der Wert und als zweites die Definition des Typs für PDO.
     *                                      Bsp: ['key' => 12]  oder ['key' => [12]]  oder ['key' => [12, PDO::PARAM_INT]]
     * 
     */
    private function bindParam(PDOStatement $sth, ?array $params) {
        if(is_null($params) || count($params) == 0 ){
            return;
        }
        foreach ($params as $key => &$value) {
            if(is_array($value)){
                if(count($value) == 2){
                    $sth->bindParam($key, $value[0], $value[1]);
                }else{
                    $sth->bindParam($key, $value[0]);
                }
            }else{
                // $u1 = array($value);
                $sth->bindParam($key, $value);
            }
        }
    }


}


