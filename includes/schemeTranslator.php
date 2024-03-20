<?php
class SchemeTranslator{

    /**
     * Gibt den SQL-Code zum erstellen der Tabelle anhand des Schemas zurück.
     * Inkl. Primary- jedoch OHNE Fremdschlüssel.
     * 
     * @param array $scheme  Das Schema einer Tabelle als Array.
     * 
     * @return string    SQL zum Ausführen in MySQL-Datenbank
     */
    public function createTable(array $scheme ) : string {
        $sql = "CREATE TABLE `" . $scheme['name'] . "` (";
        // Columns
        $cols = $scheme['columns'];
        $sql .= implode(',',
            array_map(function($arg){
                return "`" . $arg['name'] . "` " . $arg['type'] . " " . $arg['definition'];
            }, $cols)
        );
        // Keys
        if(array_key_exists('primary', $scheme)){
            $sql .= ", PRIMARY KEY (`" . $scheme['primary'] . "`)";
        }
        return $sql . ");";
    }


    /**
     * Gibt SQL zu entfernen der Tabelle des gewünschten Schemas zurück 
     * 
     * @param array $scheme  Das Schema einer Tabelle als Array.
     * 
     * @return string    SQL zum Ausführen in MySQL-Datenbank
     */
    public function dropTable(array $scheme) : string {
        return "DROP TABLE IF EXISTS `" . $scheme['name'] . "`;";
    }


    /**
     * Gibt SQL zurück, um die Tabelle nach dem übergebenen Schema zu ändern. Existiert die Tabelle nicht, wird NICHTS geändert (Tabelle wird NICHT angelegt)
     * Fremdschlüssel werden NICHT angelegt.
     * 
     * @param array $scheme  Das Schema einer Tabelle als Array.
     * 
     * @return string    SQL zum Ausführen in MySQL-Datenbank
     */
    public function alterTable(array $scheme ) : string {
        $sql = "";
        $tableName = $scheme['name'];
        $cols = $scheme['columns'];
        foreach ($cols as &$col) {
            $newname = $col['name'];
            // Falls weder neuer noch alter Name existieren würden, die Column anlegen.
            $sql .= "set @snip=\"ADD `$newname` {$col['type']} {$col['definition']}\"; \n";
            //In der Definition kommt manchmal ein einfach Anführungszeichen vor. Dieses muss mit einem zweiten ' als Escape-Zeichen versehen werden, da es selber in einem String mit einfachen Hochkommas eingesetzt wird.
            $def = str_replace("'", "''", $col['definition']);
            if(array_key_exists('oldname', $col)){
                $oldname =  $col['oldname'];
                // Existiert der ALTE Name (bei COLUMN_NAME eingeben), dann den Name aendern
                $sql .= <<<SQL
                    set @var=if((SELECT count(*) FROM information_schema.COLUMNS WHERE
                                    TABLE_SCHEMA      = DATABASE() AND
                                    TABLE_NAME        = '{$tableName}' AND
                                    COLUMN_NAME       = '{$oldname}' ) = 1,
                                        "set @snip='CHANGE COLUMN `$oldname` `$newname` {$col['type']} {$def}';",
                                        "SELECT 1;");
                    prepare stmt from @var;
                    execute stmt;
                    deallocate prepare stmt;
                SQL;
            }
            // Existiert der NEUE Name (bei COLUMN_NAME eingeben), dann Column anpassen
            $sql .= <<<SQL
                    set @var=if((SELECT count(*) FROM information_schema.COLUMNS WHERE
                                    TABLE_SCHEMA      = DATABASE() AND
                                    TABLE_NAME        = '{$tableName}' AND
                                    COLUMN_NAME       = '{$newname}' ) = 1,
                                        "set @snip='MODIFY COLUMN `{$newname}` {$col['type']} {$def}';",
                                        "SELECT 1;");
                    prepare stmt from @var;
                    execute stmt;
                    deallocate prepare stmt;
            SQL;
            if($col == $cols[array_key_first($cols)] ){
                $sql .= "SET @inj = CONCAT(@snip, ',');\n";
            }elseif($col == $cols[array_key_last($cols)]) {
                $sql .= "SET @inj = CONCAT(@inj, @snip);\n";
            }else{
                $sql .= "SET @inj = CONCAT(@inj, @snip, ',');\n";
            }
        }
        $sql .= <<<SQL
            SET @sql = CONCAT('ALTER TABLE `$tableName` ', @inj, ';');
            prepare stmt from @sql;
            execute stmt;
            deallocate prepare stmt;
        SQL;
        return $sql;
    }


    /**
     * Erstellt aller Fremdschlüssel eines Datenbank-Schemas. Wichtig ist, dass alle Tabellen und Spalten schon erstellt wurden, ansonsten kommt es zu einem Fehler.
     * 
     * @param array $schemes  Array aller Table-Schemes. Also ein Array von allen Table-Schemas-Array dieser Datenbank.
     * 
     * @return bool    True wenn erfolgreich, False bei einem Fehler.
     */
    public function crateAllForeignKeys(array $schemes) : string {
        $sql = "";
        foreach ($schemes as &$scheme) {
            if(array_key_exists('foreigns', $scheme)){
                $foreigns = $scheme['foreigns'];
                foreach ($foreigns as &$foreign) {
                    $onDelete = isset($foreign['delete']) ? " ON DELETE {$foreign['delete']}" : "";
                    $onUpdate = isset($foreign['update']) ? " ON UPDATE {$foreign['update']}" : "";
                    $sql .= " set @var=if((SELECT true FROM information_schema.TABLE_CONSTRAINTS WHERE" .
                            "   TABLE_SCHEMA      = DATABASE() AND" .
                            "   TABLE_NAME        = '{$scheme['name']}' AND" .
                            "   CONSTRAINT_NAME   = '{$foreign['name']}' AND" .
                            "   CONSTRAINT_TYPE   = 'FOREIGN KEY') = false," .
                            "       'select 1;', " .
                            "       'ALTER TABLE `{$scheme['name']}` ADD CONSTRAINT `{$foreign['name']}` FOREIGN KEY (`{$foreign['column']}`) REFERENCES {$foreign['reference']} $onDelete $onUpdate;');" .
                            " prepare stmt from @var;" .
                            " execute stmt;" .
                            " deallocate prepare stmt;";

                    // $sql .= "ALTER TABLE `{$scheme['name']}` ADD CONSTRAINT `{$foreign['name']}`
                    //          FOREIGN KEY (`{$foreign['column']}`) REFERENCES {$foreign['reference']};";
                }
            }
        }
        return $sql;
    }

}