<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230116082416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE auction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE auction (id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, reserve_price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN auction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE buyer ADD auction_id INT NOT NULL');
        $this->addSql('ALTER TABLE buyer ADD CONSTRAINT FK_84905FB357B8F0DE FOREIGN KEY (auction_id) REFERENCES auction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_84905FB357B8F0DE ON buyer (auction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE buyer DROP CONSTRAINT FK_84905FB357B8F0DE');
        $this->addSql('DROP SEQUENCE auction_id_seq CASCADE');
        $this->addSql('DROP TABLE auction');
        $this->addSql('DROP INDEX IDX_84905FB357B8F0DE');
        $this->addSql('ALTER TABLE buyer DROP auction_id');
    }
}
