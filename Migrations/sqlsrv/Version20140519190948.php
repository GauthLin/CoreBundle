<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/05/19 07:09:56
 */
class Version20140519190948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD exchange_token NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28524100ED73 ON claro_user (exchange_token) 
            WHERE exchange_token IS NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP COLUMN exchange_token
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'UNIQ_EB8D28524100ED73'
            ) 
            ALTER TABLE claro_user 
            DROP CONSTRAINT UNIQ_EB8D28524100ED73 ELSE 
            DROP INDEX UNIQ_EB8D28524100ED73 ON claro_user
        ");
    }
}