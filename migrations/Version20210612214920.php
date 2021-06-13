<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210612214920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664105431225F');
        $this->addSql('DROP INDEX UNIQ_FE8664105431225F ON facture');
        $this->addSql('ALTER TABLE facture CHANGE confirmed_order_id order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664108D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE8664108D9F6D38 ON facture (order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664108D9F6D38');
        $this->addSql('DROP INDEX UNIQ_FE8664108D9F6D38 ON facture');
        $this->addSql('ALTER TABLE facture CHANGE order_id confirmed_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664105431225F FOREIGN KEY (confirmed_order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE8664105431225F ON facture (confirmed_order_id)');
    }
}
