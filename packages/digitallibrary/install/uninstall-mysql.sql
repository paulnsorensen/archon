-- Dropping digital library tables...
DROP TABLE IF EXISTS tblDigitalLibrary_DigitalContent; 
DROP TABLE IF EXISTS tblDigitalLibrary_DigitalContentCreatorIndex;
DROP TABLE IF EXISTS tblDigitalLibrary_Files;
DROP TABLE IF EXISTS tblDigitalLibrary_FileTypes;
DROP TABLE IF EXISTS tblDigitalLibrary_DigitalContentLanguageIndex;
DROP TABLE IF EXISTS tblDigitalLibrary_MediaTypes;
DROP TABLE IF EXISTS tblDigitalLibrary_DigitalContentSubjectIndex;
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
