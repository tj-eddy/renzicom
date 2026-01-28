<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128160852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stock_movement (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, type VARCHAR(20) NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, warehouse_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_BB1BC1B54584665A (product_id), INDEX IDX_BB1BC1B55080ECDE (warehouse_id), INDEX IDX_BB1BC1B5A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B54584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B55080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hotel DROP type, DROP installation_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B54584665A');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B55080ECDE');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5A76ED395');
        $this->addSql('DROP TABLE stock_movement');
        $this->addSql('ALTER TABLE hotel ADD type VARCHAR(100) DEFAULT NULL, ADD installation_date DATETIME DEFAULT NULL');
    }
}
