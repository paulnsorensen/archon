-- Create table 'tblCreators_CreatorCreatorIndex'

DROP TABLE IF EXISTS tblCreators_CreatorCreatorIndex ;
CREATE TABLE tblCreators_CreatorCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CreatorID int(11) NOT NULL DEFAULT '0',
  RelatedCreatorID int(11) NOT NULL DEFAULT '0',
  CreatorRelationshipTypeID int(11) NOT NULL DEFAULT '0',
  Description text,
  PRIMARY KEY (ID),
  KEY CreatorID (CreatorID),
  KEY RelatedCreatorID (RelatedCreatorID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCreators_Creators'

DROP TABLE IF EXISTS tblCreators_Creators ;
CREATE TABLE tblCreators_Creators (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Name varchar(100) NOT NULL DEFAULT '',
  NameFullerForm varchar(100) DEFAULT NULL,
  NameVariants varchar(200) DEFAULT NULL,
  CreatorTypeID int(11) NOT NULL DEFAULT '0',
  Dates varchar(50) DEFAULT NULL,
  LCNAFDates varchar(50) DEFAULT NULL,
  LCNAFCompliant tinyint(1) NOT NULL DEFAULT '0',
  BiogHistAuthor varchar(100) DEFAULT NULL,
  BiogHist text,
  Sources text,
  LanguageID int(11) DEFAULT NULL,
  RepositoryID int(11) DEFAULT '0',
  ScriptID int(11) DEFAULT '0',
  Identifier VARCHAR(50) DEFAULT NULL,
  CreatorSourceID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCreators_CreatorSources'

DROP TABLE IF EXISTS tblCreators_CreatorSources ;
CREATE TABLE tblCreators_CreatorSources (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CreatorSource varchar(50) NOT NULL DEFAULT '',
  SourceAbbreviation varchar(10) NOT NULL DEFAULT '',
  Citation text,
  Description text,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Inserting default data for table tblCreators_CreatorSources...

INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('1','Library of Congress Name Authority File','lcnaf');
INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('2','Local Authority File','local');


-- Inserting default data for table tblCore_Modules...

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators'), 'creators');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators'), 'creatorsources');

-- Done!<br><br>

-- Database Structure Created!


