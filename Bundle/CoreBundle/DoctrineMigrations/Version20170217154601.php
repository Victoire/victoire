<?php

namespace Victoire\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170217154601 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vic_business_entity (id INT AUTO_INCREMENT NOT NULL, endpoint_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, availableWidgets LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, resource VARCHAR(255) DEFAULT NULL, getMethod LONGTEXT DEFAULT NULL, listMethod LONGTEXT DEFAULT NULL, pagerParameter LONGTEXT DEFAULT NULL, class VARCHAR(255) DEFAULT NULL, INDEX IDX_C445185621AF7E36 (endpoint_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('CREATE TABLE vic_business_property (id INT AUTO_INCREMENT NOT NULL, business_entity_id INT DEFAULT NULL, types LONGTEXT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, choices LONGTEXT DEFAULT NULL, listMethod LONGTEXT DEFAULT NULL, filterMethod LONGTEXT DEFAULT NULL, INDEX IDX_A8EAD01198417B22 (business_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('RENAME TABLE vic_entity_proxy TO vic_entity_proxy_old;');
        $this->addSql('CREATE TABLE vic_entity_proxy (id INT AUTO_INCREMENT NOT NULL, business_entity_id INT DEFAULT NULL, ressource_id VARCHAR(255) DEFAULT NULL, additionnal_properties LONGTEXT DEFAULT NULL, INDEX IDX_7BB88CBD98417B22 (business_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('CREATE TABLE vic_api_endpoint (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, host LONGTEXT NOT NULL, token LONGTEXT DEFAULT NULL, tokenType VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE vic_business_entity ADD CONSTRAINT FK_C445185621AF7E36 FOREIGN KEY (endpoint_id) REFERENCES vic_api_endpoint (id) ON DELETE SET NULL;');
        $this->addSql('ALTER TABLE vic_business_property ADD CONSTRAINT FK_A8EAD01198417B22 FOREIGN KEY (business_entity_id) REFERENCES vic_business_entity (id);');
        $this->addSql('ALTER TABLE vic_entity_proxy ADD CONSTRAINT FK_7BB88CBD98417B22 FOREIGN KEY (business_entity_id) REFERENCES vic_business_entity (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE vic_view DROP FOREIGN KEY FK_FAA91F341341DB46;');
        $this->addSql('ALTER TABLE vic_view ADD related_business_entity_id INT DEFAULT NULL;');
        $this->addSql('ALTER TABLE vic_view ADD CONSTRAINT FK_FAA91F342B7846BB FOREIGN KEY (related_business_entity_id) REFERENCES vic_business_entity (id) ON DELETE CASCADE;');
        $this->addSql('CREATE INDEX IDX_FAA91F342B7846BB ON vic_view (related_business_entity_id);');
        $this->addSql('ALTER TABLE vic_widget DROP FOREIGN KEY FK_57DF2B231341DB46;');
        $this->addSql('ALTER TABLE vic_widget ADD related_business_entity_id INT DEFAULT NULL;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE vic_business_entity;');
        $this->addSql('DROP TABLE vic_business_property;');
        $this->addSql('DROP TABLE vic_api_endpoint;');
        $this->addSql('ALTER TABLE vic_business_entity DROP FOREIGN KEY FK_C445185621AF7E36;');
        $this->addSql('ALTER TABLE vic_business_property DROP FOREIGN KEY FK_A8EAD01198417B22;');
        $this->addSql('ALTER TABLE vic_entity_proxy DROP FOREIGN KEY FK_7BB88CBD98417B22 ;');
        $this->addSql('ALTER TABLE vic_view ADD FOREIGN KEY FK_FAA91F341341DB46 FOREIGN KEY (entityProxy_id) REFERENCES vic_entity_proxy (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE vic_view DROP COLUMN related_business_entity_id;');
        $this->addSql('ALTER TABLE vic_view DROP FOREIGN KEY FK_FAA91F342B7846BB;');
        $this->addSql('ALTER TABLE vic_view DROP INDEX IDX_FAA91F342B7846BB;');
        $this->addSql('ALTER TABLE vic_widget ADD FOREIGN KEY FK_57DF2B231341DB46 FOREIGN KEY (entityProxy_id) REFERENCES vic_entity_proxy (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE vic_widget DROP COLUMN related_business_entity_id;');
    }
}
