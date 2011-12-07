-- Create table 'tblCollections_BookCreatorIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookCreatorIndex') DROP TABLE tblCollections_BookCreatorIndex ;
CREATE TABLE tblCollections_BookCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  BookID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0',
  PrimaryCreator BIT DEFAULT '1'
);


-- Create table 'tblCollections_BookLanguageIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookLanguageIndex') DROP TABLE tblCollections_BookLanguageIndex ;
CREATE TABLE tblCollections_BookLanguageIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  BookID INT NOT NULL DEFAULT '0',
  LanguageID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_Books'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Books') DROP TABLE tblCollections_Books ;
CREATE TABLE tblCollections_Books (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Title varchar(100) NOT NULL,
  Edition varchar(15) NULL DEFAULT NULL,
  CopyNumber INT NULL DEFAULT NULL,
  PublicationDate varchar(50) NULL DEFAULT NULL,
  PlaceOfPublication varchar(50) NULL DEFAULT NULL,
  Publisher varchar(50) NULL DEFAULT NULL,
  Description TEXT NULL,
  Notes TEXT NULL,
  NumberOfPages INT NULL DEFAULT NULL,
  Series varchar(50) NULL DEFAULT NULL
);


-- Create table 'tblCollections_BookSubjectIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_BookSubjectIndex') DROP TABLE tblCollections_BookSubjectIndex ;
CREATE TABLE tblCollections_BookSubjectIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  BookID INT NOT NULL DEFAULT '0',
  SubjectID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_Classifications'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Classifications') DROP TABLE tblCollections_Classifications ;
CREATE TABLE tblCollections_Classifications (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ClassificationIdentifier varchar(50) NULL DEFAULT NULL,
  Title varchar(255) NULL DEFAULT NULL,
  Description TEXT NULL,
  ParentID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_CollectionBookIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionBookIndex') DROP TABLE tblCollections_CollectionBookIndex ;
CREATE TABLE tblCollections_CollectionBookIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  BookID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_CollectionCreatorIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionCreatorIndex') DROP TABLE tblCollections_CollectionCreatorIndex ;
CREATE TABLE tblCollections_CollectionCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0',
  PrimaryCreator BIT NOT NULL DEFAULT '1'
);



-- Create table 'tblCollections_CollectionLanguageIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionLanguageIndex') DROP TABLE tblCollections_CollectionLanguageIndex ;
CREATE TABLE tblCollections_CollectionLanguageIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  LanguageID INT NOT NULL DEFAULT '0'
);

-- Create table 'tblCollections_CollectionLocationIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionLocationIndex') DROP TABLE tblCollections_CollectionLocationIndex ;
CREATE TABLE tblCollections_CollectionLocationIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  LocationID INT NOT NULL DEFAULT '0',
  Content varchar(255) NULL DEFAULT NULL,
  RangeValue varchar(25) NULL DEFAULT NULL,
  Section varchar(25) NULL DEFAULT NULL,
  Shelf varchar(25) NULL DEFAULT NULL,
  Extent decimal(9,2) NULL DEFAULT NULL,
  ExtentUnitID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCollections_Collections'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Collections') DROP TABLE tblCollections_Collections ;
CREATE TABLE tblCollections_Collections (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Enabled BIT NOT NULL DEFAULT '0',
  RepositoryID INT NOT NULL DEFAULT '0',
  ClassificationID INT NOT NULL DEFAULT '0',
  CollectionIdentifier varchar(50) NULL DEFAULT NULL,
  Title varchar(150) NOT NULL,
  SortTitle varchar(150) NOT NULL,
  InclusiveDates varchar(75) NULL DEFAULT NULL,
  PredominantDates varchar(50) NULL DEFAULT NULL,
  NormalDateBegin INT NULL DEFAULT NULL,
  NormalDateEnd INT NULL DEFAULT NULL,
  FindingAidAuthor varchar(200) NULL DEFAULT NULL,
  Extent decimal(9,2) NULL DEFAULT NULL,
  ExtentUnitID INT NOT NULL DEFAULT '0',
  Scope TEXT NULL,
  Abstract TEXT NULL,
  Arrangement TEXT NULL,
  MaterialTypeID INT NOT NULL DEFAULT '0',
  AltExtentStatement TEXT NULL,
  AccessRestrictions TEXT NULL,
  UseRestrictions TEXT NULL,
  PhysicalAccess TEXT NULL,
  TechnicalAccess TEXT NULL,
  AcquisitionSource TEXT NULL,
  AcquisitionMethod TEXT NULL,
  AcquisitionDate varchar(8) NULL DEFAULT NULL,
  AppraisalInfo TEXT NULL,
  AccrualInfo TEXT NULL,
  CustodialHistory TEXT NULL,
  OrigCopiesNote TEXT NULL,
  OrigCopiesURL varchar(200) NULL DEFAULT NULL,
  RelatedMaterials TEXT NULL,
  RelatedMaterialsURL varchar(200) NULL DEFAULT NULL,
  RelatedPublications TEXT NULL,
  SeparatedMaterials TEXT NULL,
  PreferredCitation TEXT NULL,
  OtherNote TEXT NULL,
  OtherURL TEXT NULL,
  DescriptiveRulesID INT NOT NULL DEFAULT '0',
  ProcessingInfo TEXT NULL,
  RevisionHistory TEXT NULL,
  PublicationDate varchar(8) NULL DEFAULT NULL,
  PublicationNote TEXT NULL,
  FindingLanguageID INT NOT NULL DEFAULT '0',
  BiogHistAuthor varchar(100) NULL DEFAULT NULL,
  BiogHist TEXT NULL
);



-- Create table 'tblCollections_CollectionSubjectIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionSubjectIndex') DROP TABLE tblCollections_CollectionSubjectIndex ;
CREATE TABLE tblCollections_CollectionSubjectIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  SubjectID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCollections_Content'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Content') DROP TABLE tblCollections_Content ;
CREATE TABLE tblCollections_Content (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL DEFAULT '0',
  LevelContainerID INT NOT NULL DEFAULT '0',
  LevelContainerIdentifier varchar(10) NOT NULL,
  Title TEXT NULL,
  PrivateTitle TEXT NULL,
  Date varchar(75) NULL DEFAULT NULL,
  Description TEXT NULL,
  RootContentID INT NOT NULL DEFAULT '0',
  ParentID INT NOT NULL DEFAULT '0',
  ContainsContent BIT NOT NULL DEFAULT '0',
  SortOrder INT NOT NULL DEFAULT '0',
  Enabled BIT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_CollectionContentCreatorIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentCreatorIndex') DROP TABLE tblCollections_CollectionContentCreatorIndex ;
CREATE TABLE tblCollections_CollectionContentCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionContentID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_CollectionContentSubjectIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentSubjectIndex') DROP TABLE tblCollections_CollectionContentSubjectIndex ;
CREATE TABLE tblCollections_CollectionContentSubjectIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionContentID INT NOT NULL DEFAULT '0',
  SubjectID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_ExtentUnits'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ExtentUnits') DROP TABLE tblCollections_ExtentUnits ;
CREATE TABLE tblCollections_ExtentUnits (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ExtentUnit varchar(100) NOT NULL
);



-- Create table 'tblCollections_LevelContainers'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_LevelContainers') DROP TABLE tblCollections_LevelContainers ;
CREATE TABLE tblCollections_LevelContainers (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  LevelContainer varchar(100) NOT NULL DEFAULT '',
  IntellectualLevel BIT NOT NULL DEFAULT '1',
  PhysicalContainer BIT NOT NULL DEFAULT '0',
  EADLevel varchar(25) NULL DEFAULT NULL,
  PrimaryEADLevel BIT NOT NULL DEFAULT '0',
  GlobalNumbering BIT NOT NULL DEFAULT '0'
);



-- Create table 'tblCollections_Locations'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Locations') DROP TABLE tblCollections_Locations ;
CREATE TABLE tblCollections_Locations (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Location varchar(200) NOT NULL DEFAULT '',
  Description TEXT NULL,
  RepositoryLimit BIT NOT NULL DEFAULT '0'
);


-- Create 'tblCollections_LocationRepositoryIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_LocationRepositoryIndex') DROP TABLE tblCollections_LocationRepositoryIndex ;
CREATE TABLE tblCollections_LocationRepositoryIndex (
ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
LocationID INT NOT NULL DEFAULT '0',
RepositoryID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_MaterialTypes'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_MaterialTypes') DROP TABLE tblCollections_MaterialTypes ;
CREATE TABLE tblCollections_MaterialTypes (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  MaterialType varchar(50) NOT NULL DEFAULT ''
);



-- Create table 'tblCollections_ResearchAppointmentMaterialsIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointmentMaterialsIndex') DROP TABLE tblCollections_ResearchAppointmentMaterialsIndex;
CREATE TABLE tblCollections_ResearchAppointmentMaterialsIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AppointmentID INT NOT NULL DEFAULT '0',
  CollectionID INT NOT NULL DEFAULT '0',
  CollectionContentID INT NOT NULL DEFAULT '0',
  RetrievalTime INT NOT NULL DEFAULT '0',
  RetrievalUserID INT NOT NULL DEFAULT '0',
  ReturnTime INT NOT NULL DEFAULT '0',
  ReturnUserID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCollections_ResearchAppointmentPurposes'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointmentPurposes') DROP TABLE tblCollections_ResearchAppointmentPurposes;
CREATE TABLE tblCollections_ResearchAppointmentPurposes (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ResearchAppointmentPurpose varchar(50) NULL DEFAULT NULL
);



-- Create table 'tblCollections_ResearchAppointments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchAppointments') DROP TABLE tblCollections_ResearchAppointments ;
CREATE TABLE tblCollections_ResearchAppointments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  SubmitTime INT NOT NULL DEFAULT '0',
  ResearcherID INT NOT NULL DEFAULT '0',
  AppointmentPurposeID INT DEFAULT '0',
  ArrivalTime INT NOT NULL DEFAULT '0',
  DepartureTime INT DEFAULT '0',
  Topic varchar(100) NULL DEFAULT NULL,
  ResearcherComments TEXT NULL,
  ArchivistComments TEXT NULL
);



-- Create table 'tblCollections_ResearchCarts'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearchCarts') DROP TABLE tblCollections_ResearchCarts ;
CREATE TABLE tblCollections_ResearchCarts (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ResearcherID INT NOT NULL DEFAULT '0',
  CollectionID INT NOT NULL DEFAULT '0',
  CollectionContentID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCollections_ResearcherTypes'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_ResearcherTypes') DROP TABLE tblCollections_ResearcherTypes ;
CREATE TABLE tblCollections_ResearcherTypes (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ResearcherType varchar(50) NOT NULL DEFAULT ''
);



-- Create table 'tblCollections_UserFields'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_UserFields') DROP TABLE tblCollections_UserFields ;
CREATE TABLE tblCollections_UserFields (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  ContentID INT NOT NULL DEFAULT '0',
  Title varchar(50) NOT NULL DEFAULT '',
  Value TEXT NULL,
  EADElementID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_FindingAidCache'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_FindingAidCache') DROP TABLE tblCollections_FindingAidCache;
CREATE TABLE tblCollections_FindingAidCache (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL,
  TemplateSet varchar(100) NOT NULL,
  ReadPermissions BIT NOT NULL DEFAULT '0',
  Dirty BIT NOT NULL DEFAULT '0',
  RootContentID INT NOT NULL DEFAULT '0',
  FindingAidText TEXT 
);

-- Inserting default data for table tblCollections_ExtentUnits...
SET IDENTITY_INSERT tblCollections_ExtentUnits ON;
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('1','Cubic Feet');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('4','Linear Feet');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('5','Items');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('6','Folders');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('7','Boxes');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('8','Photographs');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('9','Letters');
SET IDENTITY_INSERT tblCollections_ExtentUnits OFF
-- Done! 


-- Inserting default data for table tblCollections_LevelContainers...
SET IDENTITY_INSERT tblCollections_LevelContainers ON;
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('1','Series','1','0','series','1','1');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('2','Box','0','1','','1','1');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('3','Folder','1','1','file','1','0');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('4','Sub-Series','1','0','subseries','1','0');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('5','Item','1','0','item','1','0');
SET IDENTITY_INSERT tblCollections_LevelContainers OFF;
-- Done!

-- Inserting default data for table tblCollections_MaterialTypes...
SET IDENTITY_INSERT tblCollections_MaterialTypes ON; 
INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('1','Official Records');
INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('2','Personal Papers');
INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('3','Publications');
SET IDENTITY_INSERT tblCollections_MaterialTypes OFF;
-- Done!

-- Inserting default data for table tblCore_Configuration...
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Enable User Defined Fields', '1', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Classification Identifier Minimum Length', '2', 'textfield', '2', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Collection Identifier Minimum Length', '3', 'textfield', '2', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Search By Classification', '1', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Search Box Lists', '1', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Enable Content Level Creators', '1', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Enable Content Level Subjects', '1', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Invoke External System', '0', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'External URL For EAD Export', '', 'textfield', '1', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'External Target For EAD Export', '', 'textfield', '1', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'External URL For Collection Deletion', '', 'textfield', '1', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'External Target For Collection Deletion', '', 'textfield', '1', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Enable Public EAD List', '0', 'radio', '3', '0', '0', NULL);
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_collections, '0', 'Enable Finding Aid Caching', '0', 'radio', '3', '0', '0', NULL);
-- Done!
 	
-- Inserting default data for table tblCore_Modules...
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'collections');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'collectioncontent');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'locations');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'classification');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'materialtypes');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'levelcontainers');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections,'extentunits');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections, 'researchertypes');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections, 'researchappointmentpurposes');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_collections, 'researchappointments');
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_Modules (PackageID, Script) VALUES (@package_collections, 'books');

-- Done!

-- Inserting default User Profile Fields ...
DECLARE @package_collections INT; SET @package_collections = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'); INSERT INTO tblCore_UserProfileFields (PackageID, UserProfileField, Required, InputType, Size, ListDataSource) VALUES (@package_collections, 'ResearcherTypeID', '0', 'list', '0', 'getAllResearcherTypes');

-- Done!

-- Add Indexes to Collection Content table
CREATE INDEX CollectionID ON tblCollections_Content(CollectionID);
CREATE INDEX ParentID ON tblCollections_Content(ParentID);
CREATE INDEX LevelContainerID ON tblCollections_Content(LevelContainerID);

-- Add Indexes to CollectionSubjectIndex Table
CREATE INDEX CollectionID ON tblCollections_CollectionSubjectIndex(CollectionID);
CREATE INDEX SubjectID ON tblCollections_CollectionSubjectIndex(SubjectID);

-- Add Indexes to CollectionContentSubjectIndex Table
CREATE INDEX CollectionContentID ON tblCollections_CollectionContentSubjectIndex(CollectionContentID);
CREATE INDEX SubjectID ON tblCollections_CollectionContentSubjectIndex(SubjectID);


CREATE INDEX CollectionID ON tblCollections_FindingAidCache(CollectionID);


-- Database Structure Created!

