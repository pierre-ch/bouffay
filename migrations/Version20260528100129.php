<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528100129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message_key VARCHAR(255) NOT NULL, params JSON DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_item_status_history (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, order_item_id INT NOT NULL, INDEX IDX_F1C19848E415FB15 (order_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_item_status_history ADD CONSTRAINT FK_F1C19848E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE order_item_status_history DROP FOREIGN KEY FK_F1C19848E415FB15');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE order_item_status_history');
    }
}
