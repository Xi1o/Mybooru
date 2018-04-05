<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180302164303 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE image_tag (image_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_5B6367D03DA5256D (image_id), INDEX IDX_5B6367D0BAD26311 (tag_id), PRIMARY KEY(image_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_image (tag_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_E7D11D59BAD26311 (tag_id), INDEX IDX_E7D11D593DA5256D (image_id), PRIMARY KEY(tag_id, image_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image_tag ADD CONSTRAINT FK_5B6367D03DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_tag ADD CONSTRAINT FK_5B6367D0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_image ADD CONSTRAINT FK_E7D11D59BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_image ADD CONSTRAINT FK_E7D11D593DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image DROP tags');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image_tag DROP FOREIGN KEY FK_5B6367D0BAD26311');
        $this->addSql('ALTER TABLE tag_image DROP FOREIGN KEY FK_E7D11D59BAD26311');
        $this->addSql('DROP TABLE image_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_image');
        $this->addSql('ALTER TABLE image ADD tags LONGBLOB DEFAULT NULL');
    }
}
