import {MigrationInterface, QueryRunner} from "typeorm";

export class prochaineVersion1574006693181 implements MigrationInterface {
    name = 'prochaineVersion1574006693181'

    public async up(queryRunner: QueryRunner): Promise<any> {
        await queryRunner.query("CREATE TABLE `utilisateur` (`id` int NOT NULL AUTO_INCREMENT, `login` varchar(255) NOT NULL, `nom` varchar(255) NOT NULL, UNIQUE INDEX `IDX_8aabfb85f405be6b712a966a1e` (`login`), PRIMARY KEY (`id`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("CREATE TABLE `tirage` (`id` int NOT NULL AUTO_INCREMENT, `titre` varchar(255) NOT NULL, `date` varchar(255) NOT NULL, `statut` varchar(16) NOT NULL DEFAULT 'CREE', `organisateurId` int NOT NULL, UNIQUE INDEX `IDX_245623bccab349fe7c7ab2c110` (`titre`), PRIMARY KEY (`id`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("CREATE TABLE `participation` (`tirageId` int NOT NULL, `participantId` int NOT NULL, `offreAId` int NULL, PRIMARY KEY (`tirageId`, `participantId`)) ENGINE=InnoDB", undefined);
        await queryRunner.query("ALTER TABLE `tirage` ADD CONSTRAINT `FK_8acbc2bfbad293a5b427e57b235` FOREIGN KEY (`organisateurId`) REFERENCES `utilisateur`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION", undefined);
        await queryRunner.query("ALTER TABLE `participation` ADD CONSTRAINT `FK_ed7f6f6b15a8be6c84d0a36f46d` FOREIGN KEY (`tirageId`) REFERENCES `tirage`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION", undefined);
        await queryRunner.query("ALTER TABLE `participation` ADD CONSTRAINT `FK_f48126fafb856a48373bbd7bcc8` FOREIGN KEY (`participantId`) REFERENCES `utilisateur`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION", undefined);
        await queryRunner.query("ALTER TABLE `participation` ADD CONSTRAINT `FK_a26340c7e28ca86a52960bae8d7` FOREIGN KEY (`offreAId`) REFERENCES `utilisateur`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION", undefined);
    }

    public async down(queryRunner: QueryRunner): Promise<any> {
        await queryRunner.query("ALTER TABLE `participation` DROP FOREIGN KEY `FK_a26340c7e28ca86a52960bae8d7`", undefined);
        await queryRunner.query("ALTER TABLE `participation` DROP FOREIGN KEY `FK_f48126fafb856a48373bbd7bcc8`", undefined);
        await queryRunner.query("ALTER TABLE `participation` DROP FOREIGN KEY `FK_ed7f6f6b15a8be6c84d0a36f46d`", undefined);
        await queryRunner.query("ALTER TABLE `tirage` DROP FOREIGN KEY `FK_8acbc2bfbad293a5b427e57b235`", undefined);
        await queryRunner.query("DROP TABLE `participation`", undefined);
        await queryRunner.query("DROP INDEX `IDX_245623bccab349fe7c7ab2c110` ON `tirage`", undefined);
        await queryRunner.query("DROP TABLE `tirage`", undefined);
        await queryRunner.query("DROP INDEX `IDX_8aabfb85f405be6b712a966a1e` ON `utilisateur`", undefined);
        await queryRunner.query("DROP TABLE `utilisateur`", undefined);
    }

}
