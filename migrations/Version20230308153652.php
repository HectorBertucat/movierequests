<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308153652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE movie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE movie (id INT NOT NULL, imdbid INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, rating DOUBLE PRECISION DEFAULT NULL, release_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE request (id INT NOT NULL, movies_id INT NOT NULL, status INT NOT NULL, date_created DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3B978F9F53F590A4 ON request (movies_id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F53F590A4 FOREIGN KEY (movies_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE movie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE request_id_seq CASCADE');
        $this->addSql('ALTER TABLE request DROP CONSTRAINT FK_3B978F9F53F590A4');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE request');
    }
}
