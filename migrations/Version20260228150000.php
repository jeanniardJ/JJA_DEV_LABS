<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Create lab_station table
 */
final class Version20260228150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lab_station table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lab_station (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, icon VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, border_color VARCHAR(7) NOT NULL, uptime VARCHAR(100) DEFAULT NULL, metric_label VARCHAR(100) DEFAULT NULL, metric_value VARCHAR(100) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lab_station');
    }
}
