<?php
class schemes{

    public function TableSchemes() : array {
        return [
            $this->usersTable,
            $this->ticketsTable,
            $this->projectsTable,
            $this->peoplesTable,
            $this->companiesTable,
            $this->remindersTable,
            $this->tasksTable,
            $this->notesTable,
            $this->attachmentsTable,
            $this->tagsTable,
            $this->emailsTable,
            $this->to_TagTable,
            $this->to_EmailTable,
            $this->to_NoteTable,
            $this->to_AttachmentTable,
        ];
    }


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

    public array $notesTable = [
        "name" => "notes",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "note",
                "type" => "varchar(60000)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "people",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "company",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "phone",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "email",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "userid",
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
                "name" => "fk_notes_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $attachmentsTable = [
        "name" => "attachments",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "filepath",
                "type" => "varchar(1000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL"
            ],
            [
                "name" => "extension",
                "type" => "varchar(10)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL"
            ],
            [
                "name" => "filetype",
                "type" => "varchar(60)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL"
            ],
            [
                "name" => "description",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_attachments_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $emailsTable = [
        "name" => "emails",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "body",
                "type" => "varchar(60000)",
                "definition" => "COLLATE utf8mb4_unicode_ci"   
            ],
            [
                "name" => "from",
                "type" => "varchar(100)",
                "definition" => "COLLATE utf8mb4_unicode_ci"
            ],
            [
                "name" => "subject",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "received",
                "type" => "timestamp"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_emails_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $tagsTable = [
        "name" => "tags",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "tag",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' UNIQUE"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_tags_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $ticketsTable = [
        "name" => "tickets",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_tickets_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $projectsTable = [
        "name" => "projects",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "description",
                "type" => "varchar(2000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "closed",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_projects_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $companiesTable = [
        "name" => "companies",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "description",
                "type" => "varchar(2000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "address",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "plz",
                "type" => "varchar(50)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "city",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "country",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "inactiv",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_companies_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ]
        ]
    ];

    public array $peoplesTable = [
        "name" => "peoples",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "description",
                "type" => "varchar(2000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "email",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "phone",
                "type" => "varchar(60)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "function",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "resigned",
                "type" => "tinyint(1)",
                "definition" => "NOT NULL DEFAULT 0"
            ],
            [
                "name" => "successorid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "userid",
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
                "name" => "fk_peoples_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_peoples_successorid",
                "column" => "successorid",
                "reference" => "`peoples`(`id`)",
                "delete" => "SET NULL"
            ]
        ]
    ];

    public array $remindersTable = [
        "name" => "reminders",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "description",
                "type" => "varchar(2000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "date",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ],
            [
                "name" => "responsibleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_reminders_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_reminders_responsibleid",
                "column" => "responsibleid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_reminders_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],[
                "name" => "fk_reminders_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];

    public array $tasksTable = [
        "name" => "tasks",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "name",
                "type" => "varchar(128)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "description",
                "type" => "varchar(2000)",
                "definition" => "COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
            ],
            [
                "name" => "date",
                "type" => "timestamp",
                "definition" => "NOT NULL DEFAULT CURRENT_TIMESTAMP"
            ],
            [
                "name" => "responsibleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"
            ],
            [
                "name" => "userid",
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
                "name" => "fk_tasks_userid",
                "column" => "userid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_tasks_responsibleid",
                "column" => "responsibleid",
                "reference" => "`users`(`id`)",
                "delete" => "RESTRICT"
            ],[
                "name" => "fk_tasks_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],[
                "name" => "fk_tasks_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];



    
    public array $to_NoteTable = [
        "name" => "to_note",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "noteid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "ticketid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "peopleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "companyid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "reminderid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_tonote_noteid",
                "column" => "noteid",
                "reference" => "`notes`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_ticketid",
                "column" => "ticketid",
                "reference" => "`tickets`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_peopleid",
                "column" => "peopleid",
                "reference" => "`peoples`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_companyid",
                "column" => "companyid",
                "reference" => "`companies`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_reminderid",
                "column" => "reminderid",
                "reference" => "`reminders`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_tonote_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];

    public array $to_AttachmentTable = [
        "name" => "to_attachment",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "attachmentid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "ticketid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "peopleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "companyid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "reminderid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_toattachment_attachmentid",
                "column" => "attachmentid",
                "reference" => "`attachments`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_ticketid",
                "column" => "ticketid",
                "reference" => "`tickets`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_peopleid",
                "column" => "peopleid",
                "reference" => "`peoples`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_companyid",
                "column" => "companyid",
                "reference" => "`companies`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_reminderid",
                "column" => "reminderid",
                "reference" => "`reminders`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toattachment_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];

    public array $to_EmailTable = [
        "name" => "to_email",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "emailid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "ticketid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "peopleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "companyid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "reminderid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_toemail_mailid",
                "column" => "emailid",
                "reference" => "`emails`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_ticketid",
                "column" => "ticketid",
                "reference" => "`tickets`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_peopleid",
                "column" => "peopleid",
                "reference" => "`peoples`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_companyid",
                "column" => "companyid",
                "reference" => "`companies`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_reminderid",
                "column" => "reminderid",
                "reference" => "`reminders`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_toemail_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];

    public array $to_TagTable = [
        "name" => "to_tag",
        "columns" => [
            [
                "name" => "id",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL AUTO_INCREMENT"
            ],
            [
                "name" => "tagid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED NOT NULL"   
            ],
            [
                "name" => "ticketid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "projectid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "peopleid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "companyid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "reminderid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "taskid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "attachmentid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "noteid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ],
            [
                "name" => "emailid",
                "type" => "bigint(20)",
                "definition" => "UNSIGNED"   
            ]
        ],
        "primary" => "id",
        "foreigns" => [
            [
                "name" => "fk_totag_tagid",
                "column" => "tagid",
                "reference" => "`tags`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_ticketid",
                "column" => "ticketid",
                "reference" => "`tickets`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_projectid",
                "column" => "projectid",
                "reference" => "`projects`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_peopleid",
                "column" => "peopleid",
                "reference" => "`peoples`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_companyid",
                "column" => "companyid",
                "reference" => "`companies`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_reminderid",
                "column" => "reminderid",
                "reference" => "`reminders`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_taskid",
                "column" => "taskid",
                "reference" => "`tasks`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_attachmentid",
                "column" => "attachmentid",
                "reference" => "`attachments`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_noteid",
                "column" => "noteid",
                "reference" => "`notes`(`id`)",
                "delete" => "CASCADE"
            ],
            [
                "name" => "fk_totag_emailid",
                "column" => "emailid",
                "reference" => "`emails`(`id`)",
                "delete" => "CASCADE"
            ]
        ]
    ];


}