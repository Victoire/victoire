<?php

namespace Victoire\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160510154806 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->skipIf(!empty($this->connection->fetchAll('SHOW TABLES LIKE "vic_view_translations"')), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE `vic_view_translations` TO `vic_view_translations_legacy`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->skipIf(!empty($this->connection->fetchAll('SHOW TABLES LIKE "vic_view_translations_legacy"')), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE `vic_view_translations_legacy` TO `vic_view_translations`');
    }
}
