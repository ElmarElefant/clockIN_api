<?php

/**
 * Gibt die Namen aller Tabellen zurück
 * 
 * @param PDO $conn  PDO-Verbindung zur Datenbank
 * 
 * @return array    Array mit den Namen.
 */
function getTableNames(PDO $conn) : array | null {
    if (!$conn){ return null; }
    $sth = $conn->prepare('SHOW TABLE STATUS;');
    $sth->execute();
    $res = $sth->fetchAll(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    return array_map(Function($arg) {return $arg['Name'];}, $res);
}


/**
 * Löscht alle Fremdschlüssel in der Datenbank und die dazugehörigen Indizes
 * 
 * @param PDO $conn  PDO-Verbindung zur Datenbank
 * 
 * @return bool    True wenn erfolgreich, False bei einem Fehler.
 */
function dropAllForeignKeys(PDO $conn) : bool {
    try {
    //Liste alle Foreign-Keys dieser Database:
        $sql = <<<SQL
            SELECT
                `TABLE_NAME` AS `table`,
                `CONSTRAINT_NAME` AS `name`
            FROM information_schema.table_constraints
            WHERE constraint_type='FOREIGN KEY'
            AND table_schema = DATABASE();
        SQL;
        $sth = $conn->prepare($sql);
        $sth->execute();
        $foreignKeys = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        //Alle Keys und Indizies löschen.
        $sql = implode(';',
            array_map(function($arg){
                return "ALTER TABLE `" . $arg['table'] . "` DROP FOREIGN KEY `" . $arg['name'] . "`;" .
                       "ALTER TABLE `" . $arg['table'] . "` DROP INDEX `" . $arg['name'] . "`";
            }, $foreignKeys)
        );

        if(!empty($sql)){
            $sth = $conn->prepare($sql);
            $sth->execute();
            $sth->closeCursor();
        }

        return true;
    } catch (\Throwable $ex) {
        print_r($ex);
        return false;
    }
}


/**
 * Alle nicht verwendeten Tabellen die in der DB bestehen aber nicht im Schema löschen 
 * 
 * @param PDO       $conn       PDO-Verbindung zur Datenbank
 * @param object    $schemes    Classe aller Schemas der Datenbank. Wichtig! Nicht das Array, sondern das Object.
 * 
 * @return bool    True wenn erfolgreich, False bei einem Fehler. 
 */
function dropUnusedTables(PDO $conn, object $scheme) : bool {

    try {
        //Alle Table-Names aus der Datenbank
        $dbTableNames = getTableNames($conn);
        if( is_null($dbTableNames)){
            return false;
        }
        foreach ($dbTableNames as &$tableName) {
            if(!$scheme->containsTable( $tableName )){
                $sql = "DROP TABLE `$tableName`;";
                $sth = $conn->prepare($sql);
                $sth->execute();
                $sth->closeCursor();
                echo "<br>Nicht benötigte Tabelle $tableName gelöscht.";
            }
        }
        return true;
    } catch (\Throwable $ex) {
        return false;
    }
}

/**
 * Alle nicht verwendeten Spalten die in der DB bestehen aber nicht im Schema löschen
 * 
 * @param PDO       $conn       PDO-Verbindung zur Datenbank
 * @param object    $schemes    Classe aller Schemas der Datenbank. Wichtig! Nicht das Array, sondern das Object.
 * 
 * @return bool    True wenn erfolgreich, False bei einem Fehler. 
 */
function dropUnusedColumns(PDO $conn, object $scheme) : bool {
    try {
        //Alle Table-Names aus der Datenbank
        $dbTableNames = getTableNames($conn);
        if( is_null($dbTableNames)){
            return false;
        }        
        foreach ($dbTableNames as &$tableName) {
            //Alle Spalten aus der Datenbank
            $sql = "SHOW COLUMNS FROM `$tableName`;";
            $sth = $conn->prepare($sql);
            $sth->execute();
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            $sth->closeCursor();
            $colums = array_map(Function($arg) {return $arg['Field'];}, $res);
            foreach ($colums as &$column) {
                if(!$scheme->containsColumn( $tableName, $column )){
                    $sql = "ALTER TABLE `$tableName` DROP COLUMN `$column`;";
                    $sth = $conn->prepare($sql);
                    $sth->execute();
                    $sth->closeCursor();
                    echo "<br>Nicht benötigte Spalte $column in Tabelle $tableName gelöscht.";
                }
            }
        }
        return true;
    } catch (\Throwable $ex) {
        return false;
    }
}


/**
 * Alle Tabellen und gespeicherten Funktionen löschen.
 * Fremdschlüssel sollten vorgängig gelöscht werden!
 * 
 * @param PDO       $conn       PDO-Verbindung zur Datenbank
 * 
 * @return bool    True wenn erfolgreich, False bei einem Fehler. 
 */
function dropAll(PDO $conn) : bool {
    try {
        //Alle Tabellen löschen
        $tables = getTableNames($conn);
        if( is_null($tables) ){
            return false;
        }
        $sql = "SET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($tables as &$table) {
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $conn->query($sql);

        //Alle Funktionen löschen.
        $sql = <<<SQL
            SHOW FUNCTION STATUS 
            WHERE Db = DATABASE()
        SQL;
        $sth = $conn->query($sql);
        $res = $sth->fetchAll();
        $functions = array_map(Function($arg) {return $arg['Name'];}, $res);
        foreach ($functions as &$function) {
            $sql = "DROP FUNCTION IF EXISTS `$function`;";
            $conn->query($sql);
        }
        
        //Alle Prozeduren löschen.
        $sql = <<<SQL
            SHOW PROCEDURE STATUS 
            WHERE Db = DATABASE()
        SQL;
        $sth = $conn->query($sql);
        $res = $sth->fetchAll();
        $procedures = array_map(Function($arg) {return $arg['Name'];}, $res);
        foreach ($procedures as &$procedure) {
            $sql = "DROP PROCEDURE IF EXISTS `$procedure`;";
            $conn->query($sql);
        }
        return true;
    } catch (\Throwable $th) {
        return false;
    }
}