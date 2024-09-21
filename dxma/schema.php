<?php
if (!defined('DXMA_VERSION')) {
    die();
}

DEFINE("DBVERSION", 4);

$migrations = array_fill(0, DBVERSION + 1, null);

$migrations[0] = function ($db) {
    $db->migrateQuery(
        <<<'COMMIT'
    CREATE TABLE IF NOT EXISTS SchemaVersion (
        `version` INTEGER PRIMARY KEY NOT NULL
    );
COMMIT
    );
};

$migrations[1] = function ($db) {
    $db->migrateQuery(
        <<<'COMMIT'
    CREATE TABLE IF NOT EXISTS `User` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `username` VARCHAR(32) NOT NULL,
        `passhash` VARCHAR(256) NOT NULL,
        `email` VARCHAR(256),
        `realname` VARCHAR(256) NOT NULL,
        `website` VARCHAR(256) NOT NULL DEFAULT "",
        `description` VARCHAR(256) NOT NULL DEFAULT "",
        `joined` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS `Mission` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `title` VARCHAR(100) NOT NULL,
        `version` VARCHAR(40) NOT NULL,
        `user` INTEGER NOT NULL REFERENCES User(id) ON DELETE CASCADE,
        `author` VARCHAR(128) NOT NULL,
        `description` TEXT,
        `mode` INTEGER NOT NULL,
        `game` INTEGER NOT NULL,
        `levels` INTEGER NOT NULL DEFAULT 1,
        `playersMin` INTEGER NOT NULL DEFAULT 1,
        `playersMax` INTEGER NOT NULL DEFAULT 1,
        `released` DATE NOT NULL,
        `filename` VARCHAR(256) NOT NULL,
        `screenshot` VARCHAR(256),
        `created` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
        `updated` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
        `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS `Favorite` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `user` INTEGER NOT NULL REFERENCES User(id) ON DELETE CASCADE,
        `mission` INTEGER NOT NULL REFERENCES Mission(id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS `Rating` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `user` INTEGER NOT NULL REFERENCES User(id) ON DELETE CASCADE,
        `mission` INTEGER NOT NULL REFERENCES Mission(id) ON DELETE CASCADE,
        `rating` INTEGER NOT NULL
    );
    
    CREATE INDEX idx_user ON User (id);
    CREATE INDEX idx_mission ON Mission (id);
    CREATE INDEX idx_ratings_mission ON Rating (mission);
COMMIT
    );
};

$migrations[2] = function ($db) {
    $db->migrateQuery(
        <<<'COMMIT'
    ALTER TABLE User
    ADD COLUMN `forgotcode` VARCHAR(32) AFTER email,
    ADD COLUMN `forgotexpiry` TIMESTAMP DEFAULT '0000-00-00 00:00:00' AFTER forgotcode;
    
COMMIT
    );
};

$migrations[3] = function ($db) {
    $db->migrateQuery(
        <<<'COMMIT'
    CREATE TABLE IF NOT EXISTS `Author` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `mission` INTEGER NOT NULL REFERENCES Mission(id) ON DELETE CASCADE,
        `order` INTEGER NOT NULL,
        `name` VARCHAR(128),
        `userid` INTEGER REFERENCES User(id) ON DELETE SET NULL
    );
    
    CREATE INDEX idx_author_mission ON Author (mission);
COMMIT
    );
    $migr = $db->query("SELECT id, author FROM Mission")->all();
    foreach ($migr as &$result) {
        $uid = $db->query("SELECT User.id FROM User WHERE User.username = ?", $result["author"])->one();
        if (!is_null($uid)) {
            $uid = $uid["id"];
        }
        $db->execute("INSERT INTO `Author` (`mission`, `order`, `name`, `userid`) VALUES (?, 1, ?, ?)", $result["id"], $result["author"], $uid);
    }
    $db->migrateQuery("ALTER TABLE Mission DROP COLUMN `author`");
};

$migrations[4] = function ($db) {
    $db->migrateQuery(
        <<<'COMMIT'
    ALTER TABLE Author RENAME INDEX idx_author_mission TO idx_author_old_mission;
    RENAME TABLE Author TO AuthorOld;

    CREATE TABLE IF NOT EXISTS `Author` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `name` VARCHAR(128),
        `userid` INTEGER REFERENCES User(id) ON DELETE SET NULL
    );

    CREATE TABLE IF NOT EXISTS `MissionAuthor` (
        `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `mission` INTEGER NOT NULL REFERENCES Mission(id) ON DELETE CASCADE,
        `author` INTEGER NOT NULL REFERENCES Author(id) ON DELETE CASCADE,
        `order` INTEGER NOT NULL
    );
    
    CREATE INDEX idx_author_mission ON MissionAuthor (mission);
    CREATE INDEX idx_author_author ON MissionAuthor (author);
    CREATE INDEX idx_author ON Author (id);
COMMIT
    );
    $migr = $db->query("SELECT * FROM AuthorOld")->all();
    foreach ($migr as &$result) {
        if (is_null($result["userid"]))
            $aid = $db->query("SELECT Author.id FROM Author WHERE Author.`name` = ?", $result["name"])->one();
        else
            $aid = $db->query("SELECT Author.id FROM Author WHERE Author.`userid` = ?", $result["userid"])->one();
        if (!is_null($aid)) {
            $aid = $aid["id"];
        } else {
            $db->execute("INSERT INTO `Author` (`name`, `userid`) VALUES (?, ?)", is_null($result["userid"]) ? $result["name"] : null, $result["userid"]);
            $aid = $db->newId();
        }
        $db->execute("INSERT INTO `MissionAuthor` (`mission`, `author`, `order`) VALUES (?, ?, ?)", $result["mission"], $aid, $result["order"]);
    }
    $db->migrateQuery("DROP TABLE AuthorOld");
};
