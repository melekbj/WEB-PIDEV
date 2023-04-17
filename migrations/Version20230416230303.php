<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230416230303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
      
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rating_produit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, produit_id INT DEFAULT NULL, rate INT DEFAULT NULL, INDEX IDX_CAE9155FA76ED395 (user_id), INDEX IDX_CAE9155FF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rating_produit ADD CONSTRAINT FK_CAE9155FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rating_produit ADD CONSTRAINT FK_CAE9155FF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE user ADD reset_token VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649444F97DD ON user (phone)');
    }

    public function down(Schema $schema): void
    {
        
   
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rating_produit DROP FOREIGN KEY FK_CAE9155FA76ED395');
        $this->addSql('ALTER TABLE rating_produit DROP FOREIGN KEY FK_CAE9155FF347EFB');
        $this->addSql('DROP TABLE rating_produit');
        $this->addSql('DROP INDEX UNIQ_8D93D649444F97DD ON user');
        $this->addSql('ALTER TABLE user DROP reset_token, DROP ville, CHANGE image image VARCHAR(255) NOT NULL');
    }
}
