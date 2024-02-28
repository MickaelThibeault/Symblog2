<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215141946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD thumbnail_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DFDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES thumbnail (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8DFDFF2E92 ON post (thumbnail_id)');
        $this->addSql('ALTER TABLE thumbnail DROP FOREIGN KEY FK_C35726E64B89032C');
        $this->addSql('DROP INDEX UNIQ_C35726E64B89032C ON thumbnail');
        $this->addSql('ALTER TABLE thumbnail DROP post_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DFDFF2E92');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8DFDFF2E92 ON post');
        $this->addSql('ALTER TABLE post DROP thumbnail_id');
        $this->addSql('ALTER TABLE thumbnail ADD post_id INT NOT NULL');
        $this->addSql('ALTER TABLE thumbnail ADD CONSTRAINT FK_C35726E64B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C35726E64B89032C ON thumbnail (post_id)');
    }
}
