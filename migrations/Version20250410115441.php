<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410115441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etagers (id INT AUTO_INCREMENT NOT NULL, num INT NOT NULL, nbr_places INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produits ADD etagers_id INT DEFAULT NULL, ADD stock INT NOT NULL, ADD description VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE produits ADD CONSTRAINT FK_BE2DDF8C10FAD9C3 FOREIGN KEY (etagers_id) REFERENCES etagers (id)');
        $this->addSql('CREATE INDEX IDX_BE2DDF8C10FAD9C3 ON produits (etagers_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produits DROP FOREIGN KEY FK_BE2DDF8C10FAD9C3');
        $this->addSql('DROP TABLE etagers');
        $this->addSql('DROP INDEX IDX_BE2DDF8C10FAD9C3 ON produits');
        $this->addSql('ALTER TABLE produits DROP etagers_id, DROP stock, DROP description');
    }
}
