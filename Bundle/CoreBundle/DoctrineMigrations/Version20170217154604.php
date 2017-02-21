<?php

namespace Victoire\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170217154604 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vic_view ADD CONSTRAINT FK_FAA91F341341DB46 FOREIGN KEY (entityProxy_id) REFERENCES vic_entity_proxy_new (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE vic_widget ADD CONSTRAINT FK_57DF2B232B7846BB FOREIGN KEY (related_business_entity_id) REFERENCES vic_business_entity (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE vic_widget ADD CONSTRAINT FK_57DF2B231341DB46 FOREIGN KEY (entityProxy_id) REFERENCES vic_entity_proxy_new (id) ON DELETE CASCADE;');
        $this->addSql('DROP TABLE vic_entity_proxy;');
        $this->addSql('RENAME TABLE vic_entity_proxy_new TO vic_entity_proxy;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vic_view DROP FOREIGN KEY FK_FAA91F341341DB46;');
        $this->addSql('ALTER TABLE vic_widget DROP FOREIGN KEY FK_57DF2B232B7846BB;');
        $this->addSql('ALTER TABLE vic_widget DROP FOREIGN KEY FK_57DF2B231341DB46;');
        $this->addSql('RENAME TABLE vic_entity_proxy TO vic_entity_proxy_new;');
        $this->addSql('CREATE TABLE `vic_entity_proxy` (`id` int(11) NOT NULL AUTO_INCREMENT, `article_id` int(11) DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `UNIQ_2E15B1BA7294869C` (`article_id`),  CONSTRAINT `FK_2E15B1BA7294869C` FOREIGN KEY (`article_id`) REFERENCES `vic_article` (`id`) ON DELETE CASCADE) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
    }
}
