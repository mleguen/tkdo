import {MigrationInterface, QueryRunner} from "typeorm";

export class feature5ConsultationTirages1573174642863 implements MigrationInterface {
    name = 'feature5ConsultationTirages1573174642863'

    public async up(queryRunner: QueryRunner): Promise<any> {
        await queryRunner.query("CREATE TABLE `utilisateur` (`id` int NOT NULL AUTO_INCREMENT, `login` varchar(255) NOT NULL, `nom` varchar(255) NOT NULL, UNIQUE INDEX `IDX_8aabfb85f405be6b712a966a1e` (`login`), PRIMARY KEY (`id`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("CREATE TABLE `tirage` (`id` int NOT NULL AUTO_INCREMENT, `titre` varchar(255) NOT NULL, `date` varchar(255) NOT NULL, UNIQUE INDEX `IDX_245623bccab349fe7c7ab2c110` (`titre`), PRIMARY KEY (`id`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("CREATE TABLE `tirage_participants_utilisateur` (`tirageId` int NOT NULL, `utilisateurId` int NOT NULL, INDEX `IDX_c7a482565bac68c81406dfe585` (`tirageId`), INDEX `IDX_5cc455ec619fb3bd40af727a39` (`utilisateurId`), PRIMARY KEY (`tirageId`, `utilisateurId`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("ALTER TABLE `tirage_participants_utilisateur` ADD CONSTRAINT `FK_c7a482565bac68c81406dfe585d` FOREIGN KEY (`tirageId`) REFERENCES `tirage`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION", undefined);
        await queryRunner.query("ALTER TABLE `tirage_participants_utilisateur` ADD CONSTRAINT `FK_5cc455ec619fb3bd40af727a392` FOREIGN KEY (`utilisateurId`) REFERENCES `utilisateur`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION", undefined);
    }

    public async down(queryRunner: QueryRunner): Promise<any> {
        await queryRunner.query("ALTER TABLE `tirage_participants_utilisateur` DROP FOREIGN KEY `FK_5cc455ec619fb3bd40af727a392`", undefined);
        await queryRunner.query("ALTER TABLE `tirage_participants_utilisateur` DROP FOREIGN KEY `FK_c7a482565bac68c81406dfe585d`", undefined);
        await queryRunner.query("DROP INDEX `IDX_5cc455ec619fb3bd40af727a39` ON `tirage_participants_utilisateur`", undefined);
        await queryRunner.query("DROP INDEX `IDX_c7a482565bac68c81406dfe585` ON `tirage_participants_utilisateur`", undefined);
        await queryRunner.query("DROP TABLE `tirage_participants_utilisateur`", undefined);
        await queryRunner.query("DROP INDEX `IDX_245623bccab349fe7c7ab2c110` ON `tirage`", undefined);
        await queryRunner.query("DROP TABLE `tirage`", undefined);
        await queryRunner.query("DROP INDEX `IDX_8aabfb85f405be6b712a966a1e` ON `utilisateur`", undefined);
        await queryRunner.query("DROP TABLE `utilisateur`", undefined);
    }

}
