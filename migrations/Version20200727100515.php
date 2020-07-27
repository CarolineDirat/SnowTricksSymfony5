<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200727100515 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E7FDDDCB8');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E7FDDDCB8 FOREIGN KEY (first_picture_id) REFERENCES picture (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E7FDDDCB8');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E7FDDDCB8 FOREIGN KEY (first_picture_id) REFERENCES picture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
