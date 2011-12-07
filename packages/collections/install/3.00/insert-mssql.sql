
-- Add Books Module to tblCore_Modules
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'books');

-- Add ResearcherType to userprofilefields
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_UserProfileFields (PackageID, UserProfileField, Required, InputType, Size, ListDataSource) VALUES (@package_collections, 'ResearcherTypeID', '0', 'list', '0', 'getAllResearcherTypes');

-- Add collection content module
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'collectioncontent');


-- Add converted research package modules
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'researchertypes');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'researchappointmentpurposes');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'researchappointments');