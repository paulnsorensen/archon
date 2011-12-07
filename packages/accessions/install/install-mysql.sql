
-- Create table 'tblAccessions_AccessionCollectionIndex'

DROP TABLE IF EXISTS tblAccessions_AccessionCollectionIndex;
CREATE TABLE tblAccessions_AccessionCollectionIndex (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, AccessionID int(11) NOT NULL DEFAULT '0', ClassificationID int(11) NOT NULL DEFAULT '0', CollectionID int(11) NOT NULL DEFAULT '0', PrimaryCollection tinyint(1) NOT NULL DEFAULT '1') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblAccessions_AccessionCreatorIndex'

DROP TABLE IF EXISTS tblAccessions_AccessionCreatorIndex;
CREATE TABLE tblAccessions_AccessionCreatorIndex (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, AccessionID int(11) NOT NULL DEFAULT '0', CreatorID int(11) NOT NULL DEFAULT '0', PrimaryCreator tinyint(1) NOT NULL DEFAULT '0') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblAccessions_AccessionLocationIndex'

DROP TABLE IF EXISTS tblAccessions_AccessionLocationIndex;
CREATE TABLE tblAccessions_AccessionLocationIndex (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, AccessionID int(11) NOT NULL DEFAULT '0', LocationID int(11) NOT NULL DEFAULT '0', Content varchar(255) DEFAULT NULL, RangeValue varchar(25) DEFAULT NULL, Section varchar(25) DEFAULT NULL, Shelf varchar(25) DEFAULT NULL, Extent decimal(9,2) DEFAULT NULL, ExtentUnitID int(11) NOT NULL DEFAULT '0') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblAccessions_Accessions'

DROP TABLE IF EXISTS tblAccessions_Accessions;
CREATE TABLE tblAccessions_Accessions (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, Enabled tinyint(1) NOT NULL DEFAULT '0', AccessionDate varchar(8) DEFAULT NULL, Title varchar(100) NULL, Identifier varchar(50) DEFAULT NULL, InclusiveDates varchar(75) DEFAULT NULL, ReceivedExtent decimal(9,2) DEFAULT NULL, ReceivedExtentUnitID int(11) NOT NULL DEFAULT '0', UnprocessedExtent decimal(9,2) DEFAULT NULL, UnprocessedExtentUnitID int(11) NOT NULL DEFAULT '0', MaterialTypeID int(11) NOT NULL DEFAULT '0', ProcessingPriorityID int(11) NOT NULL DEFAULT '0', ExpectedCompletionDate varchar(8) DEFAULT NULL, Donor varchar(200) DEFAULT NULL, DonorContactInformation text, DonorNotes text, PhysicalDescription text, ScopeContent text, Comments text) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblAccessions_AccessionSubjectIndex'

DROP TABLE IF EXISTS tblAccessions_AccessionSubjectIndex;
CREATE TABLE tblAccessions_AccessionSubjectIndex (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, AccessionID int(11) NOT NULL DEFAULT '0', SubjectID int(11) NOT NULL DEFAULT '0') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblAccessions_ProcessingPriorities'

DROP TABLE IF EXISTS tblAccessions_ProcessingPriorities;
CREATE TABLE tblAccessions_ProcessingPriorities (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ProcessingPriority varchar(50) NOT NULL, Description text, DisplayOrder int(11) NOT NULL DEFAULT '0') DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Insert default data for table tblAccessions_ProcessingPriorities
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('3','Low Priority','Materials assigned low priority should be processed with 4 years of accession.','30');
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('4','Medium Priority','Materials assigned medium priority should be processed within 2 years of accession.','20');
INSERT INTO tblAccessions_ProcessingPriorities (ID,ProcessingPriority,Description,DisplayOrder) VALUES ('5','High Priority','Materials assigned high priority should be processed within 6 months of accession.','10');


-- Insert default data for table tblCore_Configuration

INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'), '0', 'Accession Identifier Minimum Length', '0', 'textfield', '2', '0', '0', NULL);


-- Insert default data for table tblCore_Modules

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'),'accessions');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions'),'processingpriorities');
