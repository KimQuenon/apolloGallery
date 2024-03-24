<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324181827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE movement (id INT AUTO_INCREMENT NOT NULL, movement_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movement_artwork (movement_id INT NOT NULL, artwork_id INT NOT NULL, INDEX IDX_A71D99AF229E70A7 (movement_id), INDEX IDX_A71D99AFDB8FFA4 (artwork_id), PRIMARY KEY(movement_id, artwork_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE movement_artwork ADD CONSTRAINT FK_A71D99AF229E70A7 FOREIGN KEY (movement_id) REFERENCES movement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movement_artwork ADD CONSTRAINT FK_A71D99AFDB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artwork (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movement_artwork DROP FOREIGN KEY FK_A71D99AF229E70A7');
        $this->addSql('ALTER TABLE movement_artwork DROP FOREIGN KEY FK_A71D99AFDB8FFA4');
        $this->addSql('DROP TABLE movement');
        $this->addSql('DROP TABLE movement_artwork');
    }
}
