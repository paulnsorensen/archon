-- Dropping digital library tables...
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContent') DROP TABLE tblDigitalLibrary_DigitalContent; 
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentCreatorIndex') DROP TABLE tblDigitalLibrary_DigitalContentCreatorIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_FileContents') DROP TABLE tblDigitalLibrary_FileContents;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_Files') DROP TABLE tblDigitalLibrary_Files;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_FileTypes') DROP TABLE tblDigitalLibrary_FileTypes;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentLanguageIndex') DROP TABLE tblDigitalLibrary_DigitalContentLanguageIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_MediaTypes') DROP TABLE tblDigitalLibrary_MediaTypes;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentSubjectIndex') DROP TABLE tblDigitalLibrary_DigitalContentSubjectIndex;
-- Done!<br>

-- Removing Configurations...
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary');
-- Done!<br>

-- Removing Phrases...
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary');
-- Done!<br>

-- Removing Modules...
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary');
-- Done!<br>

-- Digital Library Package Uninstalled!