<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122204709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE display (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, hotel_id INT NOT NULL, INDEX IDX_CD172A33243BB18 (hotel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE distribution (id INT AUTO_INCREMENT NOT NULL, quantity INT DEFAULT 0 NOT NULL, status VARCHAR(50) DEFAULT \'preparing\' NOT NULL, destination LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_A4483781A76ED395 (user_id), INDEX IDX_A44837814584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE hotel (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(500) DEFAULT NULL, contact_name VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, quantity_added INT DEFAULT 0 NOT NULL, photo_before VARCHAR(500) DEFAULT NULL, photo_after VARCHAR(500) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, distribution_id INT NOT NULL, rack_id INT NOT NULL, INDEX IDX_D11814AB6EB6DDB5 (distribution_id), INDEX IDX_D11814AB8E86A33E (rack_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(500) DEFAULT NULL, year_edition INT DEFAULT NULL, language VARCHAR(10) DEFAULT NULL, variant JSON DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rack (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, position INT DEFAULT 0 NOT NULL, required_quantity INT DEFAULT 0 NOT NULL, current_quantity INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, display_id INT NOT NULL, product_id INT DEFAULT NULL, INDEX IDX_3DD796A851A2DF33 (display_id), INDEX IDX_3DD796A84584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantity INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, note LONGTEXT DEFAULT NULL, warehouse_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_4B3656605080ECDE (warehouse_id), INDEX IDX_4B3656604584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, role VARCHAR(50) DEFAULT NULL, is_active TINYINT DEFAULT 1 NOT NULL, password VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE display ADD CONSTRAINT FK_CD172A33243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE distribution ADD CONSTRAINT FK_A4483781A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE distribution ADD CONSTRAINT FK_A44837814584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB6EB6DDB5 FOREIGN KEY (distribution_id) REFERENCES distribution (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB8E86A33E FOREIGN KEY (rack_id) REFERENCES rack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rack ADD CONSTRAINT FK_3DD796A851A2DF33 FOREIGN KEY (display_id) REFERENCES display (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rack ADD CONSTRAINT FK_3DD796A84584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656605080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE display DROP FOREIGN KEY FK_CD172A33243BB18');
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A4483781A76ED395');
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A44837814584665A');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB6EB6DDB5');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB8E86A33E');
        $this->addSql('ALTER TABLE rack DROP FOREIGN KEY FK_3DD796A851A2DF33');
        $this->addSql('ALTER TABLE rack DROP FOREIGN KEY FK_3DD796A84584665A');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656605080ECDE');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604584665A');
        $this->addSql('DROP TABLE display');
        $this->addSql('DROP TABLE distribution');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE rack');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE warehouse');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
