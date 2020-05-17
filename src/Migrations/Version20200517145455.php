<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200517145455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX word ON term');
        $this->addSql('ALTER TABLE term ADD CONSTRAINT FK_A50FE78DBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('CREATE INDEX IDX_A50FE78DBAD26311 ON term (tag_id)');
        $this->addSql('DROP INDEX email ON user');
        $this->addSql('ALTER TABLE user RENAME INDEX username TO UNIQ_8D93D649F85E0677');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_389B783A76ED395 ON tag (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783A76ED395');
        $this->addSql('DROP INDEX IDX_389B783A76ED395 ON tag');
        $this->addSql('ALTER TABLE term DROP FOREIGN KEY FK_A50FE78DBAD26311');
        $this->addSql('DROP INDEX IDX_A50FE78DBAD26311 ON term');
        $this->addSql('CREATE UNIQUE INDEX word ON term (word, translation)');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email)');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649f85e0677 TO username');
    }
}
