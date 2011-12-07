-- Create table 'tblCreators_CreatorCreatorIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCreators_CreatorCreatorIndex') DROP TABLE tblCreators_CreatorCreatorIndex ;
CREATE TABLE tblCreators_CreatorCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CreatorID INT NOT NULL DEFAULT '0',
  RelatedCreatorID INT NOT NULL DEFAULT '0',
  CreatorRelationshipTypeID INT NOT NULL DEFAULT '0',
  Description TEXT NULL
);


-- Create table 'tblCreators_Creators'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCreators_Creators') DROP TABLE tblCreators_Creators ;
CREATE TABLE tblCreators_Creators (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Name varchar(100) NOT NULL DEFAULT '',
  NameFullerForm varchar(100) NULL DEFAULT NULL,
  NameVariants varchar(200) NULL DEFAULT NULL,
  CreatorTypeID INT NOT NULL DEFAULT '0',
  Dates varchar(50) NULL DEFAULT NULL,
  LCNAFDates varchar(50) NULL DEFAULT NULL,
  LCNAFCompliant BIT NOT NULL DEFAULT '0',
  BiogHistAuthor varchar(100) NULL DEFAULT NULL,
  BiogHist TEXT NULL,
  Sources TEXT NULL,
  LanguageID INT NULL DEFAULT NULL,
  RepositoryID INT DEFAULT '0',
  ScriptID INT DEFAULT '0',
  Identifier VARCHAR(50) NULL DEFAULT NULL,
  CreatorSourceID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCreators_CreatorSources'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCreators_CreatorSources') DROP TABLE tblCreators_CreatorSources;
CREATE TABLE tblCreators_CreatorSources (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CreatorSource varchar(50) NOT NULL DEFAULT '',
  SourceAbbreviation varchar(10) NOT NULL DEFAULT '',
  Citation TEXT NULL,
  Description TEXT NULL
);


-- Inserting default data for table tblCreators_CreatorSources...

SET IDENTITY_INSERT tblCreators_CreatorSources ON;
INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('1','Library of Congress Name Authority File','lcnaf');
INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('2','Local Authority File','local');
SET IDENTITY_INSERT tblCreators_CreatorSources OFF;



-- Inserting default data for table tblCore_Modules...
DECLARE @package_creators INT; SET @package_creators = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators'); INSERT INTO tblCore_Modules  (PackageID,Script) VALUES (@package_creators, 'creators');
DECLARE @package_creators INT; SET @package_creators = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators'); INSERT INTO tblCore_Modules  (PackageID,Script) VALUES (@package_creators, 'creatorsources');

-- Done!<br><br>




-- Create indexes on relevant fields for index table
CREATE INDEX CreatorID ON tblCreators_CreatorCreatorIndex(CreatorID);
CREATE INDEX RelatedCreatorID ON tblCreators_CreatorCreatorIndex(RelatedCreatorID);


-- Database Structure Created!


