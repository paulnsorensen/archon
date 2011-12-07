-- Create table 'tblCollections_BookCreatorIndex'

DROP TABLE IF EXISTS tblCollections_BookCreatorIndex ;
CREATE TABLE tblCollections_BookCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  BookID int(11) DEFAULT NULL,
  CreatorID int(11) DEFAULT NULL,
  PrimaryCreator tinyint(1) DEFAULT '1',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_BookLanguageIndex'

DROP TABLE IF EXISTS tblCollections_BookLanguageIndex ;
CREATE TABLE tblCollections_BookLanguageIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  BookID int(11) DEFAULT NULL,
  LanguageID int(11) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_Books'

DROP TABLE IF EXISTS tblCollections_Books ;
CREATE TABLE tblCollections_Books (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Title varchar(100) NOT NULL,
  Edition varchar(15) DEFAULT NULL,
  CopyNumber int(11) DEFAULT NULL,
  PublicationDate varchar(50) DEFAULT NULL,
  PlaceOfPublication varchar(50) DEFAULT NULL,
  Publisher varchar(50) DEFAULT NULL,
  Description text,
  Notes text,
  NumberOfPages int(11) DEFAULT NULL,
  Series varchar(50) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_BookSubjectIndex'
--
DROP TABLE IF EXISTS tblCollections_BookSubjectIndex ;
CREATE TABLE tblCollections_BookSubjectIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  BookID int(11) DEFAULT NULL,
  SubjectID int(11) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_Classifications'
--
DROP TABLE IF EXISTS tblCollections_Classifications ;
CREATE TABLE tblCollections_Classifications (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ClassificationIdentifier varchar(50) DEFAULT NULL,
  Title varchar(255) DEFAULT NULL,
  Description text,
  ParentID int(11) NOT NULL DEFAULT '0',
  CreatorID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_CollectionBookIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionBookIndex ;
CREATE TABLE tblCollections_CollectionBookIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) DEFAULT NULL,
  BookID int(11) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_CollectionCreatorIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionCreatorIndex ;
CREATE TABLE tblCollections_CollectionCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL DEFAULT '0',
  CreatorID int(11) NOT NULL DEFAULT '0',
  PrimaryCreator tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_CollectionLanguageIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionLanguageIndex ;
CREATE TABLE tblCollections_CollectionLanguageIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL DEFAULT '0',
  LanguageID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create table 'tblCollections_CollectionLocationIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionLocationIndex ;
CREATE TABLE tblCollections_CollectionLocationIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL DEFAULT '0',
  LocationID int(11) NOT NULL DEFAULT '0',
  Content varchar(255) DEFAULT NULL,
  RangeValue varchar(25) DEFAULT NULL,
  Section varchar(25) DEFAULT NULL,
  Shelf varchar(25) DEFAULT NULL,
  Extent decimal(9,2) DEFAULT NULL,
  ExtentUnitID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_Collections'
--
DROP TABLE IF EXISTS tblCollections_Collections ;
CREATE TABLE tblCollections_Collections (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Enabled tinyint(1) NOT NULL DEFAULT '0',
  RepositoryID int(11) NOT NULL DEFAULT '0',
  ClassificationID int(11) NOT NULL DEFAULT '0',
  CollectionIdentifier varchar(50) DEFAULT NULL,
  Title varchar(150) NOT NULL,
  SortTitle varchar(150) NOT NULL,
  InclusiveDates varchar(75) DEFAULT NULL,
  PredominantDates varchar(50) DEFAULT NULL,
  NormalDateBegin int(11) DEFAULT NULL,
  NormalDateEnd int(11) DEFAULT NULL,
  FindingAidAuthor varchar(200) DEFAULT NULL,
  Extent decimal(9,2) DEFAULT NULL,
  ExtentUnitID int(11) NOT NULL DEFAULT '0',
  Scope text,
  Abstract text,
  Arrangement text,
  MaterialTypeID int(11) NOT NULL DEFAULT '0',
  AltExtentStatement text,
  AccessRestrictions text,
  UseRestrictions text,
  PhysicalAccess text,
  TechnicalAccess text,
  AcquisitionSource text,
  AcquisitionMethod text,
  AcquisitionDate varchar(8) DEFAULT NULL,
  AppraisalInfo text,
  AccrualInfo text,
  CustodialHistory text,
  OrigCopiesNote text,
  OrigCopiesURL varchar(200) DEFAULT NULL,
  RelatedMaterials text,
  RelatedMaterialsURL varchar(200) DEFAULT NULL,
  RelatedPublications text,
  SeparatedMaterials text,
  PreferredCitation text,
  OtherNote text,
  OtherURL text,
  DescriptiveRulesID int(11) NOT NULL DEFAULT '0',
  ProcessingInfo text,
  RevisionHistory text,
  PublicationDate varchar(8) DEFAULT NULL,
  PublicationNote text,
  FindingLanguageID int(11) NOT NULL DEFAULT '0',
  BiogHistAuthor varchar(100) DEFAULT NULL,
  BiogHist text,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_CollectionSubjectIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionSubjectIndex ;
CREATE TABLE tblCollections_CollectionSubjectIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL DEFAULT '0',
  SubjectID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionID (CollectionID),
  KEY SubjectID (SubjectID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_Content'
--
DROP TABLE IF EXISTS tblCollections_Content ;
CREATE TABLE tblCollections_Content (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL DEFAULT '0',
  LevelContainerID int(11) NOT NULL DEFAULT '0',
  LevelContainerIdentifier varchar(10) NOT NULL,
  Title text,
  PrivateTitle text,
  `Date` varchar(75) DEFAULT NULL,
  Description text,
  RootContentID int(11) NOT NULL DEFAULT '0',
  ParentID int(11) NOT NULL DEFAULT '0',
  ContainsContent tinyint(1) NOT NULL DEFAULT '0',
  SortOrder int(11) NOT NULL DEFAULT '0',
  Enabled tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionID (CollectionID),
  KEY ParentID (ParentID),
  KEY LevelContainerID (LevelContainerID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_CollectionContentCreatorIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionContentCreatorIndex ;
CREATE TABLE tblCollections_CollectionContentCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  CreatorID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionContentID (CollectionContentID),
  KEY CreatorID (CreatorID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_CollectionContentSubjectIndex'
--
DROP TABLE IF EXISTS tblCollections_CollectionContentSubjectIndex ;
CREATE TABLE tblCollections_CollectionContentSubjectIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  SubjectID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionContentID (CollectionContentID),
  KEY SubjectID (SubjectID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;






-- Create table 'tblCollections_ExtentUnits'
--
DROP TABLE IF EXISTS tblCollections_ExtentUnits ;
CREATE TABLE tblCollections_ExtentUnits (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ExtentUnit varchar(100) NOT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_LevelContainers'
--
DROP TABLE IF EXISTS tblCollections_LevelContainers ;
CREATE TABLE tblCollections_LevelContainers (
  ID int(11) NOT NULL AUTO_INCREMENT,
  LevelContainer varchar(100) NOT NULL DEFAULT '',
  IntellectualLevel tinyint(1) NOT NULL DEFAULT '1',
  PhysicalContainer tinyint(1) NOT NULL DEFAULT '0',
  EADLevel varchar(25) DEFAULT NULL,
  PrimaryEADLevel tinyint(1) NOT NULL DEFAULT '0',
  GlobalNumbering tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_Locations'
--
DROP TABLE IF EXISTS tblCollections_Locations ;
CREATE TABLE tblCollections_Locations (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Location varchar(200) NOT NULL DEFAULT '',
  Description text,
  RepositoryLimit TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create tblCollections_LocationRepositoryIndex
DROP TABLE IF EXISTS tblCollections_LocationRepositoryIndex ;
CREATE TABLE tblCollections_LocationRepositoryIndex (
ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
LocationID int(11) NOT NULL DEFAULT '0',
RepositoryID int(11) NOT NULL DEFAULT '0'
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_MaterialTypes'
--
DROP TABLE IF EXISTS tblCollections_MaterialTypes ;
CREATE TABLE tblCollections_MaterialTypes (
  ID int(11) NOT NULL AUTO_INCREMENT,
  MaterialType varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_ResearchAppointmentMaterialsIndex'
--
DROP TABLE IF EXISTS tblCollections_ResearchAppointmentMaterialsIndex;
CREATE TABLE tblCollections_ResearchAppointmentMaterialsIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  AppointmentID int(11) NOT NULL DEFAULT '0',
  CollectionID int(11) NOT NULL DEFAULT '0',
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  RetrievalTime int(11) NOT NULL DEFAULT '0',
  RetrievalUserID int(11) NOT NULL DEFAULT '0',
  ReturnTime int(11) NOT NULL DEFAULT '0',
  ReturnUserID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_ResearchAppointmentPurposes'
--
DROP TABLE IF EXISTS tblCollections_ResearchAppointmentPurposes;
CREATE TABLE tblCollections_ResearchAppointmentPurposes (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ResearchAppointmentPurpose varchar(50) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_ResearchAppointments'
--
DROP TABLE IF EXISTS tblCollections_ResearchAppointments ;
CREATE TABLE tblCollections_ResearchAppointments (
  ID int(11) NOT NULL AUTO_INCREMENT,
  SubmitTime int(11) NOT NULL DEFAULT '0',
  ResearcherID int(11) NOT NULL DEFAULT '0',
  AppointmentPurposeID int(11) DEFAULT '0',
  ArrivalTime int(11) NOT NULL DEFAULT '0',
  DepartureTime int(11) DEFAULT '0',
  Topic varchar(100) DEFAULT NULL,
  ResearcherComments text,
  ArchivistComments text,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_ResearchCarts'
--
DROP TABLE IF EXISTS tblCollections_ResearchCarts ;
CREATE TABLE tblCollections_ResearchCarts (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ResearcherID int(11) NOT NULL DEFAULT '0',
  CollectionID int(11) NOT NULL DEFAULT '0',
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_ResearcherTypes'
--
DROP TABLE IF EXISTS tblCollections_ResearcherTypes ;
CREATE TABLE tblCollections_ResearcherTypes (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ResearcherType varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCollections_UserFields'
--
DROP TABLE IF EXISTS tblCollections_UserFields ;
CREATE TABLE tblCollections_UserFields (
  ID int(11) NOT NULL AUTO_INCREMENT,
  ContentID int(11) NOT NULL DEFAULT '0',
  Title varchar(50) NOT NULL DEFAULT '',
  `Value` text,
  EADElementID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_FindingAidCache' 

DROP TABLE IF EXISTS tblCollections_FindingAidCache ;
CREATE TABLE tblCollections_FindingAidCache (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL,
  TemplateSet varchar(100) NOT NULL,
  ReadPermissions tinyint(1) NOT NULL DEFAULT '0',
  Dirty tinyint(1) NOT NULL DEFAULT '0',
  RootContentID int(11) NOT NULL DEFAULT '0',
  FindingAidText longtext,
  PRIMARY KEY (ID),
  KEY CollectionID (CollectionID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Inserting default data for table tblCollections_ExtentUnits...

INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('1','Cubic Feet');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('4','Linear Feet');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('5','Items');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('6','Folders');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('7','Boxes');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('8','Photographs');
INSERT INTO tblCollections_ExtentUnits (ID,ExtentUnit) VALUES ('9','Letters');
-- Done! 


-- Inserting default data for table tblCollections_LevelContainers...

INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('1','Series','1','0','series','1','1');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('2','Box','0','1','','1','1');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('3','Folder','1','1','file','1','0');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('4','Sub-Series','1','0','subseries','1','0');
INSERT INTO tblCollections_LevelContainers (ID,LevelContainer,IntellectualLevel,PhysicalContainer,EADLevel,PrimaryEADLevel,GlobalNumbering) VALUES ('5','Item','1','0','item','1','0');
-- Done!

-- Inserting default data for table tblCollections_MaterialTypes...

INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('1','Official Records');
INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('2','Personal Papers');
INSERT INTO tblCollections_MaterialTypes (ID,MaterialType) VALUES ('3','Publications');
-- Done!

-- Inserting default data for table tblCore_Configuration...

INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Enable User Defined Fields', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Classification Identifier Minimum Length', '2', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Collection Identifier Minimum Length', '3', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Search By Classification', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Search Box Lists', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Enable Content Level Creators', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Enable Content Level Subjects', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Invoke External System', '0', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'External URL For EAD Export', '', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'External Target For EAD Export', '', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'External URL For Collection Deletion', '', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'External Target For Collection Deletion', '', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Enable Public EAD List', '0', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), '0', 'Enable Finding Aid Caching', '0', 'radio', '3', '0', '0', NULL);
-- Done!
 	
-- Inserting default data for table tblCore_Modules...

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'collections');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'collectioncontent');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'locations');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'classification');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'materialtypes');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'levelcontainers');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'),'extentunits');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchertypes');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchappointmentpurposes');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchappointments');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'books');

-- Done!

-- Inserting default User Profile Fields ...

INSERT INTO tblCore_UserProfileFields (PackageID, UserProfileField, Required, InputType, Size, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'ResearcherTypeID', '0', 'list', '0', 'getAllResearcherTypes');

-- Done!


-- Database Structure Created!

