<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261017120457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_config (id INT AUTO_INCREMENT NOT NULL, setting_key VARCHAR(100) NOT NULL, setting_value LONGTEXT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_318942FC5FA1E697 (setting_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, lead_id INT DEFAULT NULL, INDEX IDX_FE38F84455458D (lead_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE appointment_availability (id INT AUTO_INCREMENT NOT NULL, day_of_week INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, slot_duration INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lab_station (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, icon VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, border_color VARCHAR(7) NOT NULL, uptime VARCHAR(100) DEFAULT NULL, metric_label VARCHAR(100) DEFAULT NULL, metric_value VARCHAR(100) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `lead` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE push_subscription (id INT AUTO_INCREMENT NOT NULL, endpoint LONGTEXT NOT NULL, p256dh VARCHAR(255) NOT NULL, auth VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, admin_id INT NOT NULL, INDEX IDX_562830F3642B8210 (admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE scan_result (id INT AUTO_INCREMENT NOT NULL, scan_id VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, raw_output JSON DEFAULT NULL, duration INT DEFAULT NULL, error_message VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CFDBE4ED2827AAD3 (scan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE system_log (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(20) NOT NULL, category VARCHAR(50) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, google_authenticator_secret VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84455458D FOREIGN KEY (lead_id) REFERENCES `lead` (id)');
        $this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F3642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84455458D');
        $this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F3642B8210');
        $this->addSql('DROP TABLE app_config');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE appointment_availability');
        $this->addSql('DROP TABLE lab_station');
        $this->addSql('DROP TABLE `lead`');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE push_subscription');
        $this->addSql('DROP TABLE scan_result');
        $this->addSql('DROP TABLE system_log');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
