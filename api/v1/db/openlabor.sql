
-- -----------------------------------------------------
-- Table `mydb`.`jobOffers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jobOffers` ;

CREATE  TABLE IF NOT EXISTS .`jobOffers` (
  `idjobOffers` INT NOT NULL ,
  `idJobOriginal` INT NOT NULL ,
  `jobexpiration` TIME NULL ,
  `workersRequired` INT NULL ,
  `professionalProfile` TEXT NULL ,
  `positionCode` VARCHAR(18) NULL ,
  `position` VARCHAR(45) NULL ,
  `qualificationRequired` TEXT NULL ,
  `descriptionQualificationRequired` VARCHAR(45) NULL ,
  `cityCompany` VARCHAR(45) NULL ,
  `idCPI` INT NULL ,
  `experienceRequired` TEXT NULL ,
  `durationExperience` INT NULL ,
  `minAge` INT NULL ,
  `maxAge` INT NULL ,
  `remuneration` DECIMAL(2) NULL ,
  `rewards` VARCHAR(10) NULL ,
  `reservedForDisabled` VARCHAR(10) NULL ,
  `favoredCategoryRequests` VARCHAR(45) NULL ,
  `ownVehicle` VARCHAR(10) NULL ,
  `notes` TEXT NULL ,
  `linkMoreInfo` VARCHAR(60) NULL ,
  PRIMARY KEY (`idjobOffers`, `idJobOriginal`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mydb`.`CPI`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CPI` ;

CREATE  TABLE IF NOT EXISTS `CPI` (
  `idCPI` INT NOT NULL ,
  `nameCPI` VARCHAR(60) NULL ,
  `address` VARCHAR(60) NULL ,
  `CAP` VARCHAR(5) NULL ,
  `city` VARCHAR(45) NULL ,
  `CPIcol` TEXT NULL ,
  `phone` VARCHAR(20) NULL ,
  `fax` VARCHAR(20) NULL ,
  `email` VARCHAR(45) NULL ,
  `latitude` VARCHAR(12) NULL ,
  `longitude` VARCHAR(12) NULL ,
  PRIMARY KEY (`idCPI`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `mydb`.`training`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `training` ;

CREATE  TABLE IF NOT EXISTS `training` (
  `idTraining` INT NOT NULL ,
  `nameTraining` VARCHAR(45) NULL ,
  `company` VARCHAR(45) NULL ,
  `trainingAddress` VARCHAR(45) NULL ,
  `CAP` VARCHAR(5) NULL ,
  `city` VARCHAR(45) NULL ,
  `phone` VARCHAR(12) NULL ,
  `durationHours` INT NULL ,
  `trainingType` VARCHAR(16) NULL ,
  `userType` VARCHAR(16) NULL ,
  `qualificationRequired` VARCHAR(45) NULL ,
  `longitude` VARCHAR(12) NULL ,
  `latitude` VARCHAR(12) NULL ,
  PRIMARY KEY (`idTraining`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;




