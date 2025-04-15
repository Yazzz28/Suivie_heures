<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415131337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work ADD day_of TINYINT(1) DEFAULT NULL, ADD day_of_whitout_solde TINYINT(1) DEFAULT NULL, CHANGE start_time start_time TIME DEFAULT NULL, CHANGE end_time end_time TIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work DROP day_of, DROP day_of_whitout_solde, CHANGE start_time start_time TIME NOT NULL, CHANGE end_time end_time TIME NOT NULL
        SQL);
    }
}
