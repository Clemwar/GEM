<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200111153316 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE media_ateliers');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE media_ateliers (media_id INT NOT NULL, ateliers_id INT NOT NULL, INDEX IDX_B6E92BD3EA9FDD75 (media_id), INDEX IDX_B6E92BD3B1409BC9 (ateliers_id), PRIMARY KEY(media_id, ateliers_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE media_ateliers ADD CONSTRAINT FK_B6E92BD3B1409BC9 FOREIGN KEY (ateliers_id) REFERENCES ateliers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_ateliers ADD CONSTRAINT FK_B6E92BD3EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
    }
}
