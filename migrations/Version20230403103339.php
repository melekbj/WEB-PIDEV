<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230403103339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B26681E8A43901A ON evenement (titre_ev)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93151B82A4D60759 ON event_type (libelle)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_B26681E8A43901A ON evenement');
        $this->addSql('DROP INDEX UNIQ_93151B82A4D60759 ON event_type');
    }
}
