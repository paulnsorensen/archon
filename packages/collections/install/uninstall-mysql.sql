-- Dropping collections tables...
DROP TABLE IF EXISTS tblCollections_BookCreatorIndex ;
DROP TABLE IF EXISTS tblCollections_BookLanguageIndex ;
DROP TABLE IF EXISTS tblCollections_Books ;
DROP TABLE IF EXISTS tblCollections_BookSubjectIndex ;
DROP TABLE IF EXISTS tblCollections_Classifications ;
DROP TABLE IF EXISTS tblCollections_CollectionBookIndex ;
DROP TABLE IF EXISTS tblCollections_CollectionCreatorIndex ;
DROP TABLE IF EXISTS tblCollections_CollectionLanguageIndex ;
DROP TABLE IF EXISTS tblCollections_CollectionLocationIndex ;
DROP TABLE IF EXISTS tblCollections_Collections ;
DROP TABLE IF EXISTS tblCollections_CollectionSubjectIndex ;
DROP TABLE IF EXISTS tblCollections_Content ;
DROP TABLE IF EXISTS tblCollections_CollectionContentCreatorIndex ;
DROP TABLE IF EXISTS tblCollections_CollectionContentSubjectIndex ;
DROP TABLE IF EXISTS tblCollections_ExtentUnits ;
DROP TABLE IF EXISTS tblCollections_LevelContainers ;
DROP TABLE IF EXISTS tblCollections_Locations ;
DROP TABLE IF EXISTS tblCollections_LocationRepositoryIndex ;
DROP TABLE IF EXISTS tblCollections_MaterialTypes ;
DROP TABLE IF EXISTS tblCollections_ResearchAppointmentMaterialsIndex;
DROP TABLE IF EXISTS tblCollections_ResearchAppointmentPurposes;
DROP TABLE IF EXISTS tblCollections_ResearchAppointments ;
DROP TABLE IF EXISTS tblCollections_ResearchCarts ;
DROP TABLE IF EXISTS tblCollections_ResearcherTypes ;
DROP TABLE IF EXISTS tblCollections_UserFields ;

-- Done!<br>

-- Removing Configurations...
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections');

-- Done!<br>

-- Removing Phrases...
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections');

-- Done!<br>

-- Removing Modules...
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections');

-- Done!<br>


-- Removing User Profile Fields ...
DELETE FROM tblCore_UserProfileFields WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections');
-- Done!<br>

-- Collections Package Uninstalled!