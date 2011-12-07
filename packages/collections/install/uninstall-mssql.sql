-- Dropping collections tables...
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookCreatorIndex') DROP TABLE tblCollections_BookCreatorIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookLanguageIndex') DROP TABLE tblCollections_BookLanguageIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Books') DROP TABLE tblCollections_Books ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookSubjectIndex') DROP TABLE tblCollections_BookSubjectIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Classifications') DROP TABLE tblCollections_Classifications ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionBookIndex') DROP TABLE tblCollections_CollectionBookIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionCreatorIndex') DROP TABLE tblCollections_CollectionCreatorIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionLanguageIndex') DROP TABLE tblCollections_CollectionLanguageIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionLocationIndex') DROP TABLE tblCollections_CollectionLocationIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Collections') DROP TABLE tblCollections_Collections ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionSubjectIndex') DROP TABLE tblCollections_CollectionSubjectIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Content') DROP TABLE tblCollections_Content ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentCreatorIndex') DROP TABLE tblCollections_CollectionContentCreatorIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentSubjectIndex') DROP TABLE tblCollections_CollectionContentSubjectIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ExtentUnits') DROP TABLE tblCollections_ExtentUnits ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_LevelContainers') DROP TABLE tblCollections_LevelContainers ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Locations') DROP TABLE tblCollections_Locations ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_LocationRepositoryIndex') DROP TABLE tblCollections_LocationRepositoryIndex ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_MaterialTypes') DROP TABLE tblCollections_MaterialTypes ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointmentMaterialsIndex') DROP TABLE tblCollections_ResearchAppointmentMaterialsIndex;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointmentPurposes') DROP TABLE tblCollections_ResearchAppointmentPurposes;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointments') DROP TABLE tblCollections_ResearchAppointments ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchCarts') DROP TABLE tblCollections_ResearchCarts ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearcherTypes') DROP TABLE tblCollections_ResearcherTypes ;
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_UserFields') DROP TABLE tblCollections_UserFields ;

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