
set @inj='';

-- Falls weder neuer noch alter Name existiert, die Column anlegen.
set @snip='ADD $newname $type $definition';
-- Existiert der ALTE Name (bei COLUMN_NAME eingeben), dann Name aendern
set @var=if((SELECT count(*) FROM information_schema.COLUMNS WHERE
                TABLE_SCHEMA      = DATABASE() AND
                TABLE_NAME        = 'notes' AND
                COLUMN_NAME       = $oldname ) = 1,
                    "set @snip='CHANGE COLUMN $oldname $newname $type $definition';",
                    "SELECT 1;");
prepare stmt from @var;
execute stmt;
deallocate prepare stmt;
-- Existiert der NEUE Name (bei COLUMN_NAME eingeben), dann Column anpassen
set @var=if((SELECT count(*) FROM information_schema.COLUMNS WHERE
                TABLE_SCHEMA      = 'xuhitoso_crm' AND
                TABLE_NAME        = 'notes' AND
                COLUMN_NAME       = $newname ) = 1,
                    "set @snip='MODIFY COLUMN $newname $type $definition;",
                    "SELECT 1;");
prepare stmt from @var;
execute stmt;
deallocate prepare stmt;




set @inj=CONCAT_WS(',',@inj,@snip)


set @func = CONCAT('ALTER TABLE `notes` ', @inj);
PREPARE stmt FROM @func;
execute stmt;
deallocate prepare stmt;



