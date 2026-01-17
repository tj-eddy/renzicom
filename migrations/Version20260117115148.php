<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117115148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rack DROP FOREIGN KEY `FK_3DD796A8FE25E29A`');
        $this->addSql('DROP INDEX IDX_3DD796A8FE25E29A ON rack');
        $this->addSql('ALTER TABLE rack CHANGE warehouse_id_id warehouse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rack ADD CONSTRAINT FK_3DD796A85080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('CREATE INDEX IDX_3DD796A85080ECDE ON rack (warehouse_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rack DROP FOREIGN KEY FK_3DD796A85080ECDE');
        $this->addSql('DROP INDEX IDX_3DD796A85080ECDE ON rack');
        $this->addSql('ALTER TABLE rack CHANGE warehouse_id warehouse_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rack ADD CONSTRAINT `FK_3DD796A8FE25E29A` FOREIGN KEY (warehouse_id_id) REFERENCES warehouse (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3DD796A8FE25E29A ON rack (warehouse_id_id)');
    }
}
