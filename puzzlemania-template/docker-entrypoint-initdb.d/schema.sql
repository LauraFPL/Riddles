SET NAMES utf8;
SET
time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `puzzlemania`;
CREATE DATABASE `puzzlemania`;
USE `puzzlemania`;


DROP TABLE IF EXISTS `images`;
CREATE TABLE `images`
(
    `id`               VARCHAR(41)                              NOT NULL,
    `originalName`     VARCHAR(255)                             NOT NULL,
    `width`            INTEGER                                  NOT NULL,
    `height`           INTEGER                                  NOT NULL,
    `userUpload`       INTEGER                                  NOT NULL, 
    `actualInUse`      BOOLEAN                                  NOT NULL DEFAULT false,
    `uploadedAt`       DATETIME                                 NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`userUpload`) REFERENCES `users` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`
(
    `id`            INT                                                     NOT NULL AUTO_INCREMENT,
    `email`         VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `password`      VARCHAR(255)                                            NOT NULL,
    `createdAt`     DATETIME                                                NOT NULL,
    `updatedAt`     DATETIME                                                NOT NULL,
    PRIMARY KEY (`id`)

) AUTO_INCREMENT = 1 ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `riddles`;
CREATE TABLE `riddles`
(
    `riddle_id`   INT          NOT NULL AUTO_INCREMENT,
    `user_id`    INT DEFAULT NULL,    /*NOT NULL*/ /*LO he comentado*/
    `riddle`      VARCHAR(255) NOT NULL,
    `answer`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`riddle_id`),
    FOREIGN KEY (user_id) REFERENCES users (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `id`                int            NOT NULL AUTO_INCREMENT,
  `name`              varchar(255)   NOT NULL,
  `user1`             int            NOT NULL,
  `user2`             int            DEFAULT NULL,
  `lastGamePoints`    int            NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user1` (`user1`),
  KEY `user2` (`user2`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`user1`) REFERENCES `users` (`id`),
  CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`user2`) REFERENCES `users` (`id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games`
(
    `game_id`   INT          NOT NULL AUTO_INCREMENT,
    `user_id`   INT          NOT NULL,
    `points`    INT          NOT NULL,
    `riddle1`    INT         NOT NULL,
    `riddle2`    INT         NOT NULL,
    `riddle3`    INT         NOT NULL,
    `playedAt` DATETIME      NOT NULL,
    PRIMARY KEY (`game_id`),
    FOREIGN KEY (user_id) REFERENCES users (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO riddles (riddle, answer) VALUES 
('It brings back the lost as though never gone, shines laughter and tears with light long since shone; a moment to make, a lifetime to shed; valued then but lost when your dead. What Is It?', 'Memory'),
('What do you get when you cross a fish with an elephant?', 'Swimming trunks'),
('I can be long, or I can be short.\nI can be grown, and I can be bought.\nI can be painted, or left bare.\nI can be round, or I can be square.\nWhat am I?', 'Fingernails'),
('I am lighter than a feather yet no man can hold me for long.', 'Breath'),
('What occurs once in every minute, twice in every moment and yet never in a thousand years?', 'The letter M'),
('What nationality is Santa Claus?', 'North Polish'),
('What animal is best at hitting a baseball?', 'A bat'),
('What do you call a cow that twitches?', 'Beef jerky');