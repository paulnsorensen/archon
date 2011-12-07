-- Dropping creator tables...
DROP TABLE IF EXISTS tblCreators_Creators;
DROP TABLE IF EXISTS tblCreators_CreatorCreatorIndex ;
DROP TABLE IF EXISTS tblCreators_CreatorSources;

-- Done!

-- Removing Configurations...
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators');
-- Done!

-- Removing Phrases...
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators');

-- Done!

-- Removing Modules...
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators');

-- Done!

-- Creators Package Uninstalled!