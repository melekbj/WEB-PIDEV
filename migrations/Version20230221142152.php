<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230221142152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail_commande (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE store_produit (store_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_30ADCDC0B092A811 (store_id), INDEX IDX_30ADCDC0F347EFB (produit_id), PRIMARY KEY(store_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE store_produit ADD CONSTRAINT FK_30ADCDC0B092A811 FOREIGN KEY (store_id) REFERENCES store (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE store_produit ADD CONSTRAINT FK_30ADCDC0F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE store_produit DROP FOREIGN KEY FK_30ADCDC0B092A811');
        $this->addSql('ALTER TABLE store_produit DROP FOREIGN KEY FK_30ADCDC0F347EFB');
        $this->addSql('DROP TABLE detail_commande');
        $this->addSql('DROP TABLE store_produit');
    }
}
