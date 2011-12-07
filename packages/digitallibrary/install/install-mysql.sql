-- Create table  tblDigitalLibrary_DigitalContent 

DROP TABLE IF EXISTS tblDigitalLibrary_DigitalContent; 
CREATE TABLE tblDigitalLibrary_DigitalContent (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Browsable tinyint(1) NOT NULL DEFAULT '1',
  Title varchar(255) NOT NULL,
  CollectionID int(11) NOT NULL DEFAULT '0',
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  Identifier varchar(255) DEFAULT NULL,
  Scope text,
  PhysicalDescription text,
  `Date` varchar(50) DEFAULT NULL,
  Publisher varchar(255) DEFAULT NULL,
  Contributor varchar(100) DEFAULT NULL,
  RightsStatement text,
  ContentURL varchar(255) DEFAULT NULL,
  HyperlinkURL TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblDigitalLibrary_DigitalContentCreatorIndex'

DROP TABLE IF EXISTS  tblDigitalLibrary_DigitalContentCreatorIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  DigitalContentID int(11) NOT NULL DEFAULT '0',
  CreatorID int(11) NOT NULL DEFAULT '0',
  PrimaryCreator tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblDigitalLibrary_DigitalContentLanguageIndex'

DROP TABLE IF EXISTS  tblDigitalLibrary_DigitalContentLanguageIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentLanguageIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  DigitalContentID int(11) NOT NULL DEFAULT '0',
  LanguageID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblDigitalLibrary_DigitalContentSubjectIndex'

DROP TABLE IF EXISTS  tblDigitalLibrary_DigitalContentSubjectIndex;
CREATE TABLE tblDigitalLibrary_DigitalContentSubjectIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  DigitalContentID int(11) NOT NULL DEFAULT '0',
  SubjectID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblDigitalLibrary_Files'

DROP TABLE IF EXISTS tblDigitalLibrary_Files;
CREATE TABLE tblDigitalLibrary_Files (
  ID int(11) NOT NULL AUTO_INCREMENT,
  DefaultAccessLevel int(11) NOT NULL DEFAULT '2',
  DigitalContentID int(11) NOT NULL DEFAULT '0',
  Title varchar(255) DEFAULT NULL,
  Filename varchar(255) NOT NULL DEFAULT '',
  FileTypeID int(11) NOT NULL DEFAULT '0',
  FileContents longblob NOT NULL,
  FilePreviewLong longblob NOT NULL,
  FilePreviewShort longblob NOT NULL,
  Size int(11) NOT NULL DEFAULT '0',
  DisplayOrder int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY DigitalContentID (DigitalContentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblDigitalLibrary_FileTypes'

DROP TABLE IF EXISTS tblDigitalLibrary_FileTypes;
CREATE TABLE tblDigitalLibrary_FileTypes (
  ID int(11) NOT NULL AUTO_INCREMENT,
  FileType varchar(50) NOT NULL DEFAULT '',
  FileExtensions varchar(50) NOT NULL DEFAULT '',
  ContentType varchar(50) NOT NULL DEFAULT '',
  MediaTypeID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblDigitalLibrary_MediaTypes'

DROP TABLE IF EXISTS tblDigitalLibrary_MediaTypes;
CREATE TABLE tblDigitalLibrary_MediaTypes (
  ID int(11) NOT NULL AUTO_INCREMENT,
  MediaType varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Inserting default data for table tblDigitalLibrary_FileTypes...

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

-- Done!


-- Inserting default data for table tblDigitalLibrary_MediaTypes...

INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('1','Image');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('2','Audio');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('3','Video');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('4','Document');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('5','Archive');
INSERT INTO tblDigitalLibrary_MediaTypes (ID,MediaType) VALUES ('6','Other');

-- Done!


-- Inserting default data for table tblCore_Configuration...

INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Thumbnail Width Small', '100','textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Thumbnail Width Medium', '400','textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Preview Short Max Size', '512','textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Preview Long Max Size', '2048', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Max Thumbnails', '20', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'), '0', 'Digital Content Identifier Minimum Length','7','textfield', '2', '0', '0', NULL);

-- Done!

-- Inserting default data for table tblCore_Modules...

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'),'filetypes');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'),'digitallibrary');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'digitallibrary'),'filemanager');

-- Done!

-- Database Structure Created!
