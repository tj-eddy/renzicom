<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117074719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE warehouse_image (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, uploaded_at DATETIME NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_445C78925080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE warehouse_image ADD CONSTRAINT FK_445C78925080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE warehouse ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP image, CHANGE address address VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE warehouse_image DROP FOREIGN KEY FK_445C78925080ECDE');
        $this->addSql('DROP TABLE warehouse_image');
        $this->addSql('ALTER TABLE warehouse ADD image VARCHAR(255) NOT NULL, DROP created_at, DROP updated_at, CHANGE address address VARCHAR(255) NOT NULL');
    }
}
