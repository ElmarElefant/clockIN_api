<?php
class schemes{

    /**
     * Gibt an, ob im Schema eine Tabelle mit dem angegebenen Namen existiert.
     * 
     * @param   string  Name    Name der Table, nach der gesucht wird.
     * 
     * @return  bool    True wenn eine Table mit dem Namen existiert, ansonsten False
     */
    public function containsTable(string $name) : bool {
        $schemes = $this->TableSchemes();
        return count( array_filter( $schemes, function($arg) use ($name) {return  strtolower($arg['name']) == strtolower($name);} ) ) > 0;
    }


    /**
     * Gibt an, ob im Schema in der angegebenen Table die Spalte/Column existiert.
     * 
     * @param   string  table    Name der Table, in der nach der Column gesucht wird.
     * @param   string  column   Name der Spalte
     * 
     * @return  bool    True wenn die Spalte mit dem Namen existiert, ansonsten False. Falls die Tabelle nicht exitiert wird auch False ausgegeben.
     */
    public function containsColumn(string $table, string $column) : bool {
        $schemes = $this->TableSchemes();
        $tableFiltered = array_filter( $schemes, function($arg) use ($table) {return  strtolower($arg['name']) == strtolower($table);} );
        if( count($tableFiltered) != 1 || !isset($tableFiltered[array_key_first($tableFiltered)]['columns']) ){
            return false;
        }
        return count( array_filter( $tableFiltered[array_key_first($tableFiltered)]['columns'], function($arg) use ($column) {return  strtolower($arg['name']) == strtolower($column);} ) ) > 0;     
    }



    /**
     * Auflistung aller Tabellen-Diffinitionen die berücksichtigt werden.
     */
    public function TableSchemes() : array {
        return [
            $this->usersTable,
            $this->employeesTable,
            $this->comensationsTable,
            $this->absencemattersTable,
            $this->absencesTable,
            $this->stampsTable
        ];
    }


    /**
     * Benutzer die Zugriff auf das Backend haben
     */
    public array $usersTable = [
        "name" => "users",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "username",
                "type" => "varchar(50)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' UNIQUE"
            ],
            [
                "name" => "password",
                "type" => "varchar(200)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "apitoken",
                "type" => "varchar(14)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"   
            ],
            [
                "name" => "email",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "administrator",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "disabled",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id"
    ];


    /**
     * Mitarbeiter die Zeit erfassen können.
     */
    public array $employeesTable = [
        "name" => "employees",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(50)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "firstname",
                "type" => "varchar(50)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "number",
                "type" => "varchar(3)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' UNIQUE"
            ],
            [
                "name" => "password",
                "type" => "varchar(4)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "apitoken",
                "type" => "varchar(14)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "pensum",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "workinghours",
                "type" => "decimal(5,2)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "holiday",
                "type" => "decimal(4,1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "monday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "tuesday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "wednesday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "thursday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "friday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "saturday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "sunday",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "entryday",
                "type" => "date",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ],
            [
                "name" => "separationday",
                "type" => "date",
                "definition" => "DEFAULT NULL"
            ],
            [
                "name" => "compensationquota",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "disabled",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id"
    ];

    
    /**
     * Defnition der Vorholzeit
     */
    public array $comensationsTable = [
        "name" => "compensations",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "year",
                "type" => "year",
                "definition" => "NOT NULL DEFAULT (YEAR(CURDATE()))"
            ],
            [
                "name" => "comensationtime",
                "type" => "decimal(5,1)",
                "definition" => "NOT NULL DEFAULT 0"   
            ],
            [
                "name" => "descritpion",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id"
    ];

    /**
     * Abwesenheits Gründe
     */
    public array $absencemattersTable = [
        "name" => "absencematters",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "matter",
                "type" => "varchar(124)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id"
    ];

    /**
     * Protokoll der Abwesenheiten
     */
    public array $absencesTable = [
        "name" => "absences",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "stamp",
                "type" => "date",
                "definition" => "NOT NULL DEFAULT (curdate())"
            ],
            [
                "name" => "credit",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 100"
            ],
            [
                "name" => "absencematterid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"
            ],
            [
                "name" => "employeeid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_absencess_absencematterid",
                "column" => "absencematterid",
                "reference" => "`absencematters`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_absencess_employeeid",
                "column" => "employeeid",
                "reference" => "`employees`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];


    /**
     * Protokoll der erfassten Arbeitszeiten
     */
    public array $stampsTable = [
        "name" => "stamps",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "start",
                "type" => "datetime(0)",
                "definition" => "NOT NULL DEFAULT (curdate())"
            ],
            [
                "name" => "end",
                "type" => "datetime(0)",
                "definition" => "DEFAULT NULL"   
            ],
            [
                "name" => "employeeid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"
            ],
            [
                "name" => "created",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_stamps_employeeid",
                "column" => "employeeid",
                "reference" => "`employees`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];

}