
-- Dropping Accession tables
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionCollectionIndex') DROP TABLE tblAccessions_AccessionCollectionIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionCreatorIndex') DROP TABLE tblAccessions_AccessionCreatorIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionLocationIndex') DROP TABLE tblAccessions_AccessionLocationIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_Accessions') DROP TABLE tblAccessions_Accessions;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_ProcessingPriorities') DROP TABLE tblAccessions_ProcessingPriorities;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAccessions_AccessionSubjectIndex') DROP TABLE tblAccessions_AccessionSubjectIndex;


-- Removing Configurations
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');


-- Removing Phrases
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');


-- Removing Modules
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');

-- Accessions Package Uninstalled!