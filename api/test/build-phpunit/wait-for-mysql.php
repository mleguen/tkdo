<?php

for ($tentative = 1;; $tentative++) {
    try {
        $dbh = new PDO(
            'mysql:host=' .  getenv('MYSQL_HOST') . ';port=' .  getenv('MYSQL_PORT') . ';dbname=' . getenv('MYSQL_DATABASE'),
            getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD'),
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        );
        print("\n");
        // Déconnecte
        $dbh = null;
        break;
    } catch (PDOException $e) {
        if ($tentative >= 30) {
            print("\n");
            throw new Exception("Impossible de se connecter à la base de données");
        }
    }
    print('.');
    sleep(1);
}
