-- Create table 'tblCore_Configuration'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Configuration') DROP TABLE tblCore_Configuration;
CREATE TABLE tblCore_Configuration (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  PackageID INT NOT NULL DEFAULT '0',
  ModuleID INT NOT NULL DEFAULT '0',
  Directive varchar(100) NOT NULL DEFAULT '',
  Value text NULL,
  InputType varchar(25) NOT NULL,
  PatternID INT NOT NULL DEFAULT '0',
  ReadOnly BIT NOT NULL DEFAULT '0',
  Encrypted BIT NOT NULL DEFAULT '0',
  ListDataSource varchar(50) NULL DEFAULT NULL
);


-- Create table 'tblCore_ModificationLog'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_ModificationLog') DROP TABLE tblCore_ModificationLog;
CREATE TABLE tblCore_ModificationLog (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  TableName varchar(50) NOT NULL DEFAULT '',
  RowID INT NOT NULL DEFAULT '0',
  Timestamp INT NOT NULL DEFAULT '0',
  UserID INT NOT NULL DEFAULT '0',
  Login varchar(50) NOT NULL,
  RemoteHost varchar(200) NULL DEFAULT NULL,
  ModuleID INT NOT NULL DEFAULT '0',
  ArchonFunction varchar(30) NULL DEFAULT NULL,
  RequestData text NULL
);


-- Create table 'tblCore_Modules'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Modules') DROP TABLE tblCore_Modules;
CREATE TABLE tblCore_Modules (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  PackageID INT NOT NULL DEFAULT '0',
  Script varchar(100) NOT NULL DEFAULT ''
);


-- Create table 'tblCore_Packages'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Packages') DROP TABLE tblCore_Packages;
CREATE TABLE tblCore_Packages (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Enabled BIT NOT NULL DEFAULT '0',
  APRCode varchar(25) NOT NULL,
  DBVersion varchar(25) NOT NULL
);



-- Create table 'tblCore_Patterns'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Patterns') DROP TABLE tblCore_Patterns;
CREATE TABLE tblCore_Patterns (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  PackageID INT NOT NULL DEFAULT '0',
  Name varchar(50) NOT NULL,
  Pattern text NOT NULL
);

-- Create table 'tblCore_PatternUnitTestIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_PatternUnitTestIndex') DROP TABLE tblCore_PatternUnitTestIndex;
CREATE TABLE tblCore_PatternUnitTestIndex (
   ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
   PatternID INT NOT NULL DEFAULT  '0',
   ExpectedResult BIT NOT NULL DEFAULT  '1',
   Value text NULL
);


-- Create table 'tblCore_Phrases'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Phrases') DROP TABLE tblCore_Phrases;
CREATE TABLE tblCore_Phrases (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  PackageID INT NOT NULL DEFAULT '0',
  ModuleID INT NOT NULL DEFAULT '0',
  LanguageID INT NOT NULL DEFAULT '0',
  PhraseName varchar(100) NOT NULL,
  PhraseValue text NOT NULL,
  RegularExpression text NULL,
  PhraseTypeID INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCore_Repositories'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Repositories') DROP TABLE tblCore_Repositories;
CREATE TABLE tblCore_Repositories (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Name varchar(100) NOT NULL DEFAULT '',
  Administrator varchar(50) NULL DEFAULT NULL,
  Code varchar(10) NULL DEFAULT NULL,
  Address varchar(100) NULL DEFAULT NULL,
  Address2 varchar(100) NULL DEFAULT NULL,
  City varchar(75) NULL DEFAULT NULL,
  State char(2) NULL DEFAULT NULL,
  StateProvinceID INT NOT NULL DEFAULT '0',
  CountryID INT NOT NULL DEFAULT '0',
  ZIPCode varchar(5) NULL DEFAULT NULL,
  ZIPPlusFour varchar(4) NULL DEFAULT NULL,
  Phone varchar(25) NULL DEFAULT NULL,
  PhoneExtension varchar(10) NULL DEFAULT NULL,
  Fax varchar(25) NULL DEFAULT NULL,
  Email varchar(50) NULL DEFAULT NULL,
  URL varchar(255) NULL DEFAULT NULL,
  EmailSignature text NULL,
  TemplateSet VARCHAR(50) NULL DEFAULT NULL,
  ResearchFunctionality INT NOT NULL DEFAULT '0'
);



-- Create table 'tblCore_Sessions'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Sessions') DROP TABLE tblCore_Sessions;
CREATE TABLE tblCore_Sessions (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Hash varchar(32) NOT NULL DEFAULT '',
  UserID INT NOT NULL DEFAULT '0',
  RemoteHost varchar(100) NOT NULL DEFAULT '',
  Expires INT NOT NULL DEFAULT '0',
  Persistent BIT NOT NULL DEFAULT '0',
  SecureConnection BIT NOT NULL DEFAULT '0'
);



-- Create table 'tblCore_StateProvinces'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_StateProvinces') DROP TABLE tblCore_StateProvinces;
CREATE TABLE tblCore_StateProvinces (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CountryID INT NOT NULL,
  StateProvinceName varchar(100) NOT NULL,
  ISOAlpha2 char(2) NOT NULL
);


-- Create table 'tblCore_UsergroupPermissions'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UsergroupPermissions') DROP TABLE tblCore_UsergroupPermissions;
CREATE TABLE tblCore_UsergroupPermissions (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UsergroupID INT NOT NULL DEFAULT '0',
  ModuleID INT NOT NULL DEFAULT '0',
  Permissions INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_Usergroups'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Usergroups') DROP TABLE tblCore_Usergroups;
CREATE TABLE tblCore_Usergroups (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Usergroup varchar(25) NOT NULL DEFAULT '',
  DefaultPermissions INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_UserPermissions'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserPermissions') DROP TABLE tblCore_UserPermissions;
CREATE TABLE tblCore_UserPermissions (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserID INT NOT NULL DEFAULT '0',
  ModuleID INT NOT NULL DEFAULT '0',
  Permissions INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_UserProfileFieldCategories'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserProfileFieldCategories') DROP TABLE tblCore_UserProfileFieldCategories;
CREATE TABLE tblCore_UserProfileFieldCategories (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserProfileFieldCategory varchar(25) NOT NULL,
  DisplayOrder INT NOT NULL DEFAULT '1'
);


-- Create table 'tblCore_UserProfileFieldCountryIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserProfileFieldCountryIndex') DROP TABLE tblCore_UserProfileFieldCountryIndex;
CREATE TABLE tblCore_UserProfileFieldCountryIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserProfileFieldID INT NOT NULL DEFAULT '0',
  CountryID INT NOT NULL DEFAULT '0',
  Required BIT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_UserProfileFields'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserProfileFields') DROP TABLE tblCore_UserProfileFields;
CREATE TABLE tblCore_UserProfileFields (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  PackageID INT NOT NULL DEFAULT '0',
  UserProfileFieldCategoryID INT NOT NULL DEFAULT '0',
  DisplayOrder INT NOT NULL DEFAULT '1',
  UserProfileField varchar(50) NOT NULL,
  DefaultValue text NULL,
  Required BIT NOT NULL DEFAULT '0',
  UserEditable BIT NOT NULL DEFAULT '1',
  InputType varchar(25) NOT NULL,
  PatternID INT NOT NULL DEFAULT '0',
  Size INT NOT NULL DEFAULT '30',
  MaxLength INT NOT NULL DEFAULT '50',
  ListDataSource varchar(50) NULL DEFAULT NULL
);


-- Create table 'tblCore_UserRepositoryIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserRepositoryIndex') DROP TABLE tblCore_UserRepositoryIndex;
CREATE TABLE tblCore_UserRepositoryIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserID INT NOT NULL DEFAULT '0',
  RepositoryID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_Users'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Users') DROP TABLE tblCore_Users;
CREATE TABLE tblCore_Users (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Login varchar(50) NULL DEFAULT NULL,
  Email varchar(50) NULL DEFAULT NULL,
  PasswordHash varchar(34) NOT NULL DEFAULT '',
  FirstName varchar(50) NULL DEFAULT NULL,
  LastName varchar(50) NULL DEFAULT NULL,
  DisplayName varchar(100) NULL DEFAULT NULL,
  IsAdminUser BIT NOT NULL DEFAULT '0',
  RegisterTime INT NOT NULL DEFAULT '0',
  Pending BIT NOT NULL DEFAULT '0',
  PendingHash varchar(32) NULL DEFAULT NULL,
  LanguageID INT NOT NULL DEFAULT '0',
  CountryID INT NOT NULL DEFAULT '0',
  RepositoryLimit BIT NOT NULL DEFAULT '0',
  Locked BIT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_UserUsergroupIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserUsergroupIndex') DROP TABLE tblCore_UserUsergroupIndex;
CREATE TABLE tblCore_UserUsergroupIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserID INT NOT NULL DEFAULT '0',
  UsergroupID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCore_UserUserProfileFieldIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_UserUserProfileFieldIndex') DROP TABLE tblCore_UserUserProfileFieldIndex;
CREATE TABLE tblCore_UserUserProfileFieldIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  UserID INT NOT NULL DEFAULT '0',
  UserProfileFieldID INT NOT NULL DEFAULT '0',
  Value text NOT NULL
);

-- Create table 'tblCore_VersionCache'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_VersionCache') DROP TABLE tblCore_VersionCache;
CREATE TABLE tblCore_VersionCache (
   VersionName varchar(50) NOT NULL DEFAULT '' ,
   VersionNumber varchar(10) NULL DEFAULT NULL,
   LastUpdated DATETIME NOT NULL
);


-- Inserting default data for table tblCore_Configuration...
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Public Enabled', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Disabled Message', 'Archon is currently undergoing maintenance.  The system will be available as soon as possible.  Thank you for your patience!', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','SA Password', '*', 'password', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Search Results Limit', '100', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Default Template Set', 'default', 'select', '1', '0', '0', 'getAllTemplates');
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Default Theme', 'default', 'select', '1', '0', '0','getAllThemes');
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Related Option Max Length', '30', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Login Banner','You are entering a secure area.  Please log in below.', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Default Administrative Theme', 'default', 'select', '1', '0','0', 'getAllAdminThemes');
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Administrative Welcome Message', 'Welcome to the Archon Administrative Interface!', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Installation ID', '0', 'textfield', '2', '1', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Default Language', '2081', 'select', '2', '0', '0','getAllLanguages');
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Date Format', 'm/d/Y h:i:s A', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0','Default Public Script', 'core/index', 'textfield', '1', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Public Registration Enabled', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Modification Log Enabled', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Escape XML', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Default Repository', '0', 'select', '2', '0', '0', 'getAllRepositories');
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Pagination Limit', '40', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Read Permissions', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Search Results', '0',  'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Check For Updates', '1', 'radio', '3', '0', '0', NULL);
-- Done!<br><br>





-- Inserting default data for table tblCore_Modules...
SET IDENTITY_INSERT tblCore_Modules ON;
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('1','1','adminusers');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('11','1','database');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('12','1','usergroups');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('19','1','repositories');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('23','1','mypreferences');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('24','1','configuration');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('25','1','sessions');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('26','1','about');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('27','1','phrases');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('35','1','packages');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('36','1','modificationlog');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('48','1','userprofilefields');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('50','1','patterns');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('55','1','regions');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('57','1','userprofilefieldcategories');
INSERT INTO tblCore_Modules (ID,PackageID,Script) VALUES ('60','1','publicusers');
SET IDENTITY_INSERT tblCore_Modules OFF;
-- Done!<br><br>


-- Inserting default data for table tblCore_Packages...
SET IDENTITY_INSERT tblCore_Packages ON;
INSERT INTO tblCore_Packages (ID,Enabled,APRCode,DBVersion) VALUES ('1','1','core','3.21');
SET IDENTITY_INSERT tblCore_Packages OFF;
-- Done!<br><br>



-- Inserting default data for table tblCore_UsergroupPermissions...


INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('4','23','15');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','26','1');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','24','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','11','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','36','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','35','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','27','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','25','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','12','1');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','19','7');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','48','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','50','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('2','57','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','26','1');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','24','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','11','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','36','1');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','35','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','27','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','25','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','12','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','1','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','7','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','19','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','48','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','50','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','57','0');
INSERT INTO tblCore_UsergroupPermissions (UsergroupID,ModuleID,Permissions) VALUES ('3','60','0');
-- Done!<br><br>


-- Inserting default data for table tblCore_Usergroups...
SET IDENTITY_INSERT tblCore_Usergroups ON;
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('1','Administrators','31');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('2','Power Users','15');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('3','Users','7');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('4','Read-Only Users','1');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('5','Denied Users','0');
SET IDENTITY_INSERT tblCore_Usergroups OFF;
-- Done!<br><br>


-- Insert some default patterns
SET IDENTITY_INSERT tblCore_Patterns ON;
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (1, '1', 'Text', '/^.*?$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (2, '1', 'Non-Negative Number', '/^[0-9]*\\.?[0-9]+$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (3, '1', 'Boolean', '/^[01]$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (4, '1', 'Email Address', '/^[\\w\\-\\+\\&amp;\\*]+(?:\\.[\\w\\-\\_\\+\\&amp;\\*]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7}$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (5, '1', 'US Phone Number', '/^\\D?\\d{3}\\D?\\D?\\d{3}\\D?\\d{4}$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (6, '1', 'URL', '#^(((http|https|ftp)://)?(\\S*?\\.\\S*?))(\\s|\\;|\\)|\\]|\\[|\\{|\\}|,|\\"|''|:|\\&lt;|$|\\.\\s)$#ie');
SET IDENTITY_INSERT tblCore_Patterns OFF;

-- Insert default unit tests
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'abc');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '123');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '0');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '-1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, 'a');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, 'a');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '0');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '-1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Email Address'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'noreply@archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217-555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217 555 1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '2175551234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '(217) 555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217.555.1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'www.archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'http://archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'forums.archon.org/index.php');



-- Insert some default userprofilefieldcategories
SET IDENTITY_INSERT tblCore_UserProfileFieldCategories ON;
INSERT INTO tblCore_UserProfileFieldCategories (ID, UserProfileFieldCategory, DisplayOrder) VALUES (1, 'Contact Information', '1');
INSERT INTO tblCore_UserProfileFieldCategories (ID, UserProfileFieldCategory, DisplayOrder) VALUES (2, 'Miscellaneous Information', '2');
SET IDENTITY_INSERT tblCore_UserProfileFieldCategories OFF;



-- Insert some default userprofilefields
SET IDENTITY_INSERT tblCore_UserProfileFields ON;
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (1, '1', '1', '1', 'Address', NULL, '1', '1', 'textfield', '1', '30', '100', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (2, '1', '1', '2', 'Address2', NULL, '0', '1', 'textfield', '1', '30', '100', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (3, '1', '1', '3', 'City', NULL, '1', '1', 'textfield', '1', '30', '75', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (4, '1', '1', '4', 'StateProvinceID', NULL, '1', '1', 'select', '1', '0', '0', 'getAllStateProvinces');
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (5, '1', '1', '5', 'ZIPCode', NULL, '0', '1', 'textfield', '2', '5', '5', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (6, '1', '1', '6', 'ZIPPlusFour', NULL, '0', '1', 'textfield', '2', '4', '4', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (7, '1', '1', '7', 'Phone', NULL, '1', '1', 'textfield', '5', '15', '25', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (8, '1', '1', '8', 'PhoneExtension', NULL, '0', '1', 'textfield', '2', '5', '10', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (9, '1', '2', '1', 'ReceiveUpdates', '1', '1', '1', 'radio', '0', '0', '0', NULL);
SET IDENTITY_INSERT tblCore_UserProfileFields OFF;



-- Insert country data for userprofilefields
INSERT INTO tblCore_UserProfileFieldCountryIndex (UserProfileFieldID, CountryID, Required) VALUES ('4', '226', '1');
INSERT INTO tblCore_UserProfileFieldCountryIndex (UserProfileFieldID, CountryID, Required) VALUES ('4', '38', '1');
INSERT INTO tblCore_UserProfileFieldCountryIndex (UserProfileFieldID, CountryID, Required) VALUES ('5', '226', '1');
INSERT INTO tblCore_UserProfileFieldCountryIndex (UserProfileFieldID, CountryID, Required) VALUES ('6', '226', '0');



-- Insert State/Province data
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Alaska', 'AK');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Alabama', 'AL');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'American Samoa', 'AS');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Arizona', 'AZ');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Arkansas', 'AR');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'California', 'CA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Colorado', 'CO');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Connecticut', 'CT');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Delaware', 'DE');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'District of Columbia', 'DC');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Florida', 'FL');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Georgia', 'GA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Guam', 'GU');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Hawaii', 'HI');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Idaho', 'ID');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Illinois', 'IL');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Indiana', 'IN');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Iowa', 'IA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Kansas', 'KS');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Kentucky', 'KY');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Louisiana', 'LA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Maine', 'ME');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Maryland', 'MD');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Massachusetts', 'MA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Michigan', 'MI');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Minnesota', 'MN');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Mississippi', 'MS');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Missouri', 'MO');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Montana', 'MT');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Nebraska', 'NE');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Nevada', 'NV');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'New Hampshire', 'NH');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'New Jersey', 'NJ');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'New Mexico', 'NM');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'New York', 'NY');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'North Carolina', 'NC');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'North Dakota', 'ND');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Northern Mariana Islands', 'MP');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Ohio', 'OH');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Oklahoma', 'OK');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Oregon', 'OR');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Palau', 'PW');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Pennsylvania', 'PA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Puerto Rico', 'PR');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Rhode Island', 'RI');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'South Carolina', 'SC');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'South Dakota', 'SD');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Tennessee', 'TN');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Texas', 'TX');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Utah', 'UT');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Vermont', 'VT');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Virgin Islands', 'VI');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Virginia', 'VA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Washington', 'WA');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'West Virginia', 'WV');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Wisconsin', 'WI');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('226', 'Wyoming', 'WY');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Alberta', 'AB');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'British Columbia', 'BC');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Manitoba', 'MB');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'New Brunswick', 'NB');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Newfoundland and Labrador', 'NL');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Nova Scotia', 'NS');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Ontario', 'ON');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Prince Edward Island', 'PE');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Quebec', 'QC');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Saskatchewan', 'SK');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Northwest Territories', 'NT');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Nunavut', 'NU');
INSERT INTO tblCore_StateProvinces (CountryID, StateProvinceName, ISOAlpha2) VALUES ('38', 'Yukon Territory', 'YT');


-- Add indexes for Phrase tables
CREATE INDEX PhraseName ON tblCore_Phrases(PhraseName);
CREATE INDEX ModuleID ON tblCore_Phrases(ModuleID);
CREATE INDEX PackageID ON tblCore_Phrases(PackageID);
CREATE INDEX PhraseTypeID ON tblCore_Phrases(PhraseTypeID);

-- Add index for PatternUnitTestIndex table
CREATE INDEX PatternID ON tblCore_PatternUnitTestIndex(PatternID);
