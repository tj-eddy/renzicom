<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117201915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE distribution (id INT AUTO_INCREMENT NOT NULL, status INT DEFAULT NULL, quantity INT NOT NULL, destination VARCHAR(255) DEFAULT NULL, create_at DATETIME NOT NULL, note LONGTEXT DEFAULT NULL, prepared_at DATETIME DEFAULT NULL, shipped_at DATETIME DEFAULT NULL, deliverd_at DATETIME DEFAULT NULL, product_id INT DEFAULT NULL, user_id INT DEFAULT NULL, rack_id INT DEFAULT NULL, INDEX IDX_A44837814584665A (product_id), INDEX IDX_A4483781A76ED395 (user_id), INDEX IDX_A44837818E86A33E (rack_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE distribution ADD CONSTRAINT FK_A44837814584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE distribution ADD CONSTRAINT FK_A4483781A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE distribution ADD CONSTRAINT FK_A44837818E86A33E FOREIGN KEY (rack_id) REFERENCES rack (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A44837814584665A');
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A4483781A76ED395');
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A44837818E86A33E');
        $this->addSql('DROP TABLE distribution');
    }
}
