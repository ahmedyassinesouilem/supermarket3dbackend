<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505134147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etagers ADD rayon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE etagers ADD CONSTRAINT FK_4CEB5095D3202E52 FOREIGN KEY (rayon_id) REFERENCES rayon (id)');
        $this->addSql('CREATE INDEX IDX_4CEB5095D3202E52 ON etagers (rayon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etagers DROP FOREIGN KEY FK_4CEB5095D3202E52');
        $this->addSql('DROP INDEX IDX_4CEB5095D3202E52 ON etagers');
        $this->addSql('ALTER TABLE etagers DROP rayon_id');
    }
}
