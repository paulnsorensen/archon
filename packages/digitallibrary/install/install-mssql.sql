-- Create table  tblDigitalLibrary_DigitalContent 

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContent') DROP TABLE tblDigitalLibrary_DigitalContent; 
CREATE TABLE tblDigitalLibrary_DigitalContent (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Browsable BIT NOT NULL DEFAULT '1',
  Title varchar(255) NOT NULL,
  CollectionID INT NOT NULL DEFAULT '0',
  CollectionContentID INT NOT NULL DEFAULT '0',
  Identifier varchar(255) NULL DEFAULT NULL,
  Scope TEXT NULL,
  PhysicalDescription TEXT NULL,
  Date varchar(50) NULL DEFAULT NULL,
  Publisher varchar(255) NULL DEFAULT NULL,
  Contributor varchar(100) NULL DEFAULT NULL,
  RightsStatement TEXT NULL,
  ContentURL varchar(255) NULL DEFAULT NULL,
  HyperlinkURL BIT NOT NULL DEFAULT '0'
);


-- Create table 'tblDigitalLibrary_DigitalContentCreatorIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentCreatorIndex') DROP TABLE tblDigitalLibrary_DigitalContentCreatorIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  DigitalContentID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0',
  PrimaryCreator BIT NOT NULL DEFAULT '1'
);


-- Create table 'tblDigitalLibrary_DigitalContentLanguageIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentLanguageIndex') DROP TABLE tblDigitalLibrary_DigitalContentLanguageIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentLanguageIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  DigitalContentID INT NOT NULL DEFAULT '0',
  LanguageID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblDigitalLibrary_DigitalContentSubjectIndex'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_DigitalContentSubjectIndex') DROP TABLE tblDigitalLibrary_DigitalContentSubjectIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentSubjectIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  DigitalContentID INT NOT NULL DEFAULT '0',
  SubjectID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblDigitalLibrary_Files'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_Files') DROP TABLE tblDigitalLibrary_Files;
CREATE TABLE tblDigitalLibrary_Files (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  DefaultAccessLevel INT NOT NULL DEFAULT '2',
  DigitalContentID INT NOT NULL DEFAULT '0',
  Title varchar(255) NULL DEFAULT NULL,
  Filename varchar(255) NOT NULL DEFAULT '',
  FileTypeID INT NOT NULL DEFAULT '0',
  FileContents VARBINARY(max) NOT NULL DEFAULT 0,
  FilePreviewLong VARBINARY(max) NOT NULL DEFAULT 0,
  FilePreviewShort VARBINARY(max) NOT NULL DEFAULT 0,
  Size INT NOT NULL DEFAULT '0',
  DisplayOrder INT NOT NULL DEFAULT '0'
);



-- Create table 'tblDigitalLibrary_FileTypes'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_FileTypes') DROP TABLE tblDigitalLibrary_FileTypes;
CREATE TABLE tblDigitalLibrary_FileTypes (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  FileType varchar(50) NOT NULL DEFAULT '',
  FileExtensions varchar(50) NOT NULL DEFAULT '',
  ContentType varchar(50) NOT NULL DEFAULT '',
  MediaTypeID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblDigitalLibrary_MediaTypes'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblDigitalLibrary_MediaTypes') DROP TABLE tblDigitalLibrary_MediaTypes;
CREATE TABLE tblDigitalLibrary_MediaTypes (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  MediaType varchar(50) NOT NULL DEFAULT ''
);


-- Inserting default data for table tblDigitalLibrary_FileTypes...
SET IDENTITY_INSERT tblDigitalLibrary_FileTypes ON;
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('1','GIF Image','.gif','Image/gif','1');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('2','JPEG Image','.jpeg,.jpg','Image/jpeg','1');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('3','PNG Image','.png','Image/png','1');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('4','TIFF Image','.tif,.tiff','Image/tiff','1');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('5','MP3 Audio','.mp3','Audio/mpeg','2');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('6','WAV Audio','.wav','Audio/x-wav','2');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('7','WMA Audio','.wma','Audio/x-ms-wma','2');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('8','MIDI Audio','.mid,.midi,.rmi','Audio/midi','2');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('9','WMV Video','.wmv','video/x-ms-wmv','3');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('10','AVI Video','.avi','video/x-msvideo','3');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('11','Word Document','.doc','application/msword','4');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('12','Excel Spreadsheet','.xls','application/vnd.ms-excel','4');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('13','Powerpoint Presentation','.ppt','application/vnd.ms-powerpoint','4');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('14','PDF Document','.pdf','application/pdf','4');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('15','MPEG Video','.mpa,.mpe,.mpeg,.mpg','video/mpeg','3');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('16','ZIP Archive','.zip','application/zip','5');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('17','RAR Archive','.rar','application/rar','5');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('18','Quicktime Video','.mov','video/quicktime','3');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('19','Bitmap Image','.bmp','image/bmp','1');
INSERT INTO tblDigitalLibrary_FileTypes (ID,FileType,FileExtensions,ContentType,MediaTypeID) VALUES ('21','WordPerfect Document','.wpd','application/wordpefect','4');
SET IDENTITY_INSERT tblDigitalLibrary_FileTypes OFF;
-- Done!


-- Inserting default data for table tblDigitalLibrary_MediaTypes...
SET IDENTITY_INSERT tblDigitalLibrary_MediaTypes ON;
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('1','Image');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('2','Audio');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('3','Video');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('4','Document');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('5','Archive');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('6','Other');
SET IDENTITY_INSERT tblDigitalLibrary_MediaTypes OFF;
-- Done!


-- Inserting default data for table tblCore_Configuration...
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Thumbnail Width Small', '100','textfield', '2', '0', '0', NULL);
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Thumbnail Width Medium', '400','textfield', '2', '0', '0', NULL);
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Preview Short Max Size', '512','textfield', '2', '0', '0', NULL);
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Preview Long Max Size', '2048', 'textfield', '2', '0', '0', NULL);
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Max Thumbnails', '20', 'textfield', '2', '0', '0', NULL);
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES (@package_digitallibrary, '0', 'Digital Content Identifier Minimum Length','7','textfield', '2', '0', '0', NULL);

-- Done!

-- Inserting default data for table tblCore_Modules...
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_digitallibrary,'filetypes');
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_digitallibrary,'digitallibrary');
DECLARE @package_digitallibrary INT; SET @package_digitallibrary = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_digitallibrary,'filemanager');

-- Done!

-- Creating Index for DigitalContentID on Files table
CREATE INDEX DigitalContentID ON tblDigitalLibrary_Files(DigitalContentID);


-- Database Structure Created!

