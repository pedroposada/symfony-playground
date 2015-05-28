<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150526161946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE firstq_project (id INT AUTO_INCREMENT NOT NULL, guid VARCHAR(255) NOT NULL, bc_client_id VARCHAR(200) NOT NULL COMMENT \'Bigcommerce client id\', bc_client_name VARCHAR(200) NOT NULL COMMENT \'Bigcommerce name of the client\', bc_product_id VARCHAR(200) NOT NULL COMMENT \'id of the Bigcommerce product created\', form_data_raw LONGTEXT NOT NULL COMMENT \'result of the form submission, as json or as associative array\', sheet_data_raw LONGTEXT NOT NULL COMMENT \'result of the Google Spreadsheet as json or as associative array\', state VARCHAR(100) NOT NULL COMMENT \'last error or success code. Use to keep track of the request\', created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE firstq_project');
    }
}
