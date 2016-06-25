-- MySQL Script generated by MySQL Workbench
-- Sat 25 Jun 2016 06:12:33 PM ICT
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema unicity
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema unicity
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `unicity` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `unicity` ;

-- -----------------------------------------------------
-- Table `unicity`.`country`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`country` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `unicity`.`photo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`photo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(45) NULL,
  `title` VARCHAR(45) NULL,
  `content` BLOB NULL,
  `checksum` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `unicity`.`document`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`document` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(45) NULL,
  `title` VARCHAR(45) NULL,
  `content` BLOB NULL,
  `checksum` VARCHAR(45) NULL,
  `country_id` INT NULL,
  `document_category_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `unicity`.`document_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`document_category` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `unicity`.`media_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`media_category` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `unicity`.`media`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `unicity`.`media` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(45) NULL,
  `title` VARCHAR(45) NULL,
  `content` BLOB NULL,
  `checksum` VARCHAR(45) NULL,
  `country_id` INT NULL,
  `media_category_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

