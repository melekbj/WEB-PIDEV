<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315100954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A2FB5A7A4D60759 ON categorie_store (libelle)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93151B82A4D60759 ON event_type (libelle)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A2FB5A7A4D60759 ON categorie_store');
        $this->addSql('DROP INDEX UNIQ_93151B82A4D60759 ON event_type');
    }
}
