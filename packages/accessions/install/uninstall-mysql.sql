-- Dropping Accession tables
DROP TABLE IF EXISTS tblAccessions_AccessionCollectionIndex;
DROP TABLE IF EXISTS tblAccessions_AccessionCreatorIndex;
DROP TABLE IF EXISTS tblAccessions_AccessionLocationIndex;
DROP TABLE IF EXISTS tblAccessions_Accessions;
DROP TABLE IF EXISTS tblAccessions_ProcessingPriorities;
DROP TABLE IF EXISTS tblAccessions_AccessionSubjectIndex;


-- Removing Configurations
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');


-- Removing Phrases
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');


-- Removing Modules
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'accessions');


-- Accessions Package Uninstalled!
