
-- Create table 'tblAccessions_AccessionCollectionIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionCollectionIndex') DROP TABLE tblAccessions_AccessionCollectionIndex;
CREATE TABLE tblAccessions_AccessionCollectionIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AccessionID INT NOT NULL DEFAULT '0', ClassificationID INT NOT NULL DEFAULT '0', CollectionID INT NOT NULL DEFAULT '0', PrimaryCollection BIT NOT NULL DEFAULT '1');



-- Create table 'tblAccessions_AccessionCreatorIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionCreatorIndex') DROP TABLE tblAccessions_AccessionCreatorIndex;
CREATE TABLE tblAccessions_AccessionCreatorIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AccessionID INT NOT NULL DEFAULT '0', CreatorID INT NOT NULL DEFAULT '0', PrimaryCreator BIT NOT NULL DEFAULT '0');



-- Create table 'tblAccessions_AccessionLocationIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionLocationIndex') DROP TABLE tblAccessions_AccessionLocationIndex;
CREATE TABLE tblAccessions_AccessionLocationIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AccessionID INT NOT NULL DEFAULT '0', LocationID INT NOT NULL DEFAULT '0', Content varchar(255) NULL DEFAULT NULL, RangeValue varchar(25) NULL DEFAULT NULL, Section varchar(25) NULL DEFAULT NULL, Shelf varchar(25) NULL DEFAULT NULL, Extent decimal(9,2) NULL DEFAULT NULL, ExtentUnitID INT NOT NULL DEFAULT '0');



-- Create table 'tblAccessions_Accessions'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_Accessions') DROP TABLE tblAccessions_Accessions;
CREATE TABLE tblAccessions_Accessions (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, Enabled BIT NOT NULL DEFAULT '0', AccessionDate varchar(8) NULL DEFAULT NULL, Title varchar(100) NULL, Identifier varchar(50) NULL DEFAULT NULL, InclusiveDates varchar(75) NULL DEFAULT NULL, ReceivedExtent decimal(9,2) NULL DEFAULT NULL, ReceivedExtentUnitID INT NOT NULL DEFAULT '0', UnprocessedExtent decimal(9,2) NULL DEFAULT NULL, UnprocessedExtentUnitID INT NOT NULL DEFAULT '0', MaterialTypeID INT NOT NULL DEFAULT '0', ProcessingPriorityID INT NOT NULL DEFAULT '0', ExpectedCompletionDate varchar(8) NULL DEFAULT NULL, Donor varchar(200) NULL DEFAULT NULL, DonorContactInformation TEXT NULL, DonorNotes TEXT NULL, PhysicalDescription TEXT NULL, ScopeContent TEXT NULL, Comments TEXT NULL);



-- Create table 'tblAccessions_AccessionSubjectIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionSubjectIndex') DROP TABLE tblAccessions_AccessionSubjectIndex;
CREATE TABLE tblAccessions_AccessionSubjectIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AccessionID INT NOT NULL DEFAULT '0', SubjectID INT NOT NULL DEFAULT '0');



-- Create table 'tblAccessions_ProcessingPriorities'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_ProcessingPriorities') DROP TABLE tblAccessions_ProcessingPriorities;
CREATE TABLE tblAccessions_ProcessingPriorities (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, ProcessingPriority varchar(50) NOT NULL, Description TEXT NULL, DisplayOrder INT NOT NULL DEFAULT '0');



-- Insert default data for table tblAccessions_ProcessingPriorities
SET IDENTITY_INSERT tblAccessions_ProcessingPriorities ON;
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('3','Low Priority','Materials assigned low priority should be processed with 4 years of accession.','30');
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('4','Medium Priority','Materials assigned medium priority should be processed within 2 years of accession.','20');
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('5','High Priority','Materials assigned high priority should be processed within 6 months of accession.','10');
SET IDENTITY_INSERT tblAccessions_ProcessingPriorities OFF;


-- Insert default data for table tblCore_Configuration

DECLARE @package_accessions INT; SET @package_accessions = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_accessions, '0', 'Accession Identifier Minimum Length', '0', 'textfield', '2', '0', '0', NULL);


-- Insert default data for table tblCore_Modules

DECLARE @package_accessions INT; SET @package_accessions = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_accessions,'accessions');
DECLARE @package_accessions INT; SET @package_accessions = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_accessions,'processingpriorities');
