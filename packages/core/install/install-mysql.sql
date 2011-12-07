-- Create table 'tblCore_Configuration'
--
DROP TABLE IF EXISTS tblCore_Configuration ;
CREATE TABLE tblCore_Configuration (
  ID int(11) NOT NULL AUTO_INCREMENT,
  PackageID int(11) NOT NULL DEFAULT '0',
  ModuleID int(11) NOT NULL DEFAULT '0',
  Directive varchar(100) NOT NULL DEFAULT '',
  `Value` text,
  InputType varchar(25) NOT NULL,
  PatternID int(11) NOT NULL DEFAULT '0',
  ReadOnly tinyint(1) NOT NULL DEFAULT '0',
  Encrypted tinyint(1) NOT NULL DEFAULT '0',
  ListDataSource varchar(50) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCore_ModificationLog'
--
DROP TABLE IF EXISTS tblCore_ModificationLog ;
CREATE TABLE tblCore_ModificationLog (
  ID int(11) NOT NULL AUTO_INCREMENT,
  TableName varchar(50) NOT NULL DEFAULT '',
  RowID int(11) NOT NULL DEFAULT '0',
  `Timestamp` int(11) NOT NULL DEFAULT '0',
  UserID int(11) NOT NULL DEFAULT '0',
  Login varchar(50) NOT NULL,
  RemoteHost varchar(200) DEFAULT NULL,
  ModuleID int(11) NOT NULL DEFAULT '0',
  ArchonFunction varchar(30) DEFAULT NULL,
  RequestData text,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_Modules'
--
DROP TABLE IF EXISTS tblCore_Modules ;
CREATE TABLE tblCore_Modules (
  ID int(11) NOT NULL AUTO_INCREMENT,
  PackageID int(11) NOT NULL DEFAULT '0',
  Script varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_Packages'
--
DROP TABLE IF EXISTS tblCore_Packages ;
CREATE TABLE tblCore_Packages (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Enabled tinyint(1) NOT NULL DEFAULT '0',
  APRCode varchar(25) NOT NULL,
  DBVersion varchar(25) NOT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCore_Patterns'
--
DROP TABLE IF EXISTS tblCore_Patterns ;
CREATE TABLE tblCore_Patterns (
  ID int(11) NOT NULL AUTO_INCREMENT,
  PackageID int(11) NOT NULL DEFAULT '0',
  `Name` varchar(50) NOT NULL,
  Pattern text NOT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_PatternUnitTestIndex'
--
DROP TABLE IF EXISTS tblCore_PatternUnitTestIndex;
CREATE TABLE tblCore_PatternUnitTestIndex (
ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
PatternID INT NOT NULL DEFAULT  '0',
ExpectedResult TINYINT NOT NULL DEFAULT  '1',
`Value` TEXT NULL DEFAULT NULL,
KEY PatternID (PatternID)
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_Phrases'
--
DROP TABLE IF EXISTS tblCore_Phrases ;
CREATE TABLE tblCore_Phrases (
  ID int(11) NOT NULL AUTO_INCREMENT,
  PackageID int(11) NOT NULL DEFAULT '0',
  ModuleID int(11) NOT NULL DEFAULT '0',
  LanguageID int(11) NOT NULL DEFAULT '0',
  PhraseName varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PhraseValue text COLLATE utf8_unicode_ci NOT NULL,
  RegularExpression text COLLATE utf8_unicode_ci,
  PhraseTypeID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY PhraseName (PhraseName(2)),
  KEY ModuleID (ModuleID),
  KEY PackageID (PackageID),
  KEY PhraseTypeID (PhraseTypeID)
)   DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCore_Repositories'
--
DROP TABLE IF EXISTS tblCore_Repositories ;
CREATE TABLE tblCore_Repositories (
  ID int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  Administrator varchar(50) DEFAULT NULL,
  `Code` varchar(10) DEFAULT NULL,
  Address varchar(100) DEFAULT NULL,
  Address2 varchar(100) DEFAULT NULL,
  City varchar(75) DEFAULT NULL,
  State char(2) DEFAULT NULL,
  StateProvinceID int(11) NOT NULL DEFAULT '0',
  CountryID int(11) NOT NULL DEFAULT '0',
  ZIPCode varchar(5) DEFAULT NULL,
  ZIPPlusFour varchar(4) DEFAULT NULL,
  Phone varchar(25) DEFAULT NULL,
  PhoneExtension varchar(10) DEFAULT NULL,
  Fax varchar(25) DEFAULT NULL,
  Email varchar(50) DEFAULT NULL,
  URL varchar(255) DEFAULT NULL,
  EmailSignature text,
  TemplateSet VARCHAR(50) DEFAULT NULL,
  ResearchFunctionality INT NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Create table 'tblCore_Sessions'
--
DROP TABLE IF EXISTS tblCore_Sessions ;
CREATE TABLE tblCore_Sessions (
  ID int(11) NOT NULL AUTO_INCREMENT,
  `Hash` varchar(32) NOT NULL DEFAULT '',
  UserID int(11) NOT NULL DEFAULT '0',
  RemoteHost varchar(100) NOT NULL DEFAULT '',
  Expires int(11) NOT NULL DEFAULT '0',
  Persistent tinyint(1) NOT NULL DEFAULT '0',
  SecureConnection tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Create table 'tblCore_StateProvinces'
--
DROP TABLE IF EXISTS tblCore_StateProvinces ;
CREATE TABLE tblCore_StateProvinces (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CountryID int(11) NOT NULL,
  StateProvinceName varchar(100) NOT NULL,
  ISOAlpha2 char(2) NOT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UsergroupPermissions'
--
DROP TABLE IF EXISTS tblCore_UsergroupPermissions ;
CREATE TABLE tblCore_UsergroupPermissions (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UsergroupID int(11) NOT NULL DEFAULT '0',
  ModuleID int(11) NOT NULL DEFAULT '0',
  Permissions int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_Usergroups'
--
DROP TABLE IF EXISTS tblCore_Usergroups ;
CREATE TABLE tblCore_Usergroups (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Usergroup varchar(25) NOT NULL DEFAULT '',
  DefaultPermissions int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserPermissions'
--
DROP TABLE IF EXISTS tblCore_UserPermissions ;
CREATE TABLE tblCore_UserPermissions (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL DEFAULT '0',
  ModuleID int(11) NOT NULL DEFAULT '0',
  Permissions int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserProfileFieldCategories'
--
DROP TABLE IF EXISTS tblCore_UserProfileFieldCategories ;
CREATE TABLE tblCore_UserProfileFieldCategories (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserProfileFieldCategory varchar(25) NOT NULL,
  DisplayOrder int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserProfileFieldCountryIndex'
--
DROP TABLE IF EXISTS tblCore_UserProfileFieldCountryIndex ;
CREATE TABLE tblCore_UserProfileFieldCountryIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserProfileFieldID int(11) NOT NULL DEFAULT '0',
  CountryID int(11) NOT NULL DEFAULT '0',
  Required tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserProfileFields'
--
DROP TABLE IF EXISTS tblCore_UserProfileFields ;
CREATE TABLE tblCore_UserProfileFields (
  ID int(11) NOT NULL AUTO_INCREMENT,
  PackageID int(11) NOT NULL DEFAULT '0',
  UserProfileFieldCategoryID int(11) NOT NULL DEFAULT '0',
  DisplayOrder int(11) NOT NULL DEFAULT '1',
  UserProfileField varchar(50) NOT NULL,
  DefaultValue text,
  Required tinyint(1) NOT NULL DEFAULT '0',
  UserEditable tinyint(1) NOT NULL DEFAULT '1',
  InputType varchar(25) NOT NULL,
  PatternID int(11) NOT NULL DEFAULT '0',
  Size int(11) NOT NULL DEFAULT '30',
  MaxLength int(11) NOT NULL DEFAULT '50',
  ListDataSource varchar(50) DEFAULT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserRepositoryIndex'
--
DROP TABLE IF EXISTS tblCore_UserRepositoryIndex ;
CREATE TABLE tblCore_UserRepositoryIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL DEFAULT '0',
  RepositoryID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_Users'
--
DROP TABLE IF EXISTS tblCore_Users ;
CREATE TABLE tblCore_Users (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Login varchar(50) DEFAULT NULL,
  Email varchar(50) DEFAULT NULL,
  PasswordHash varchar(34) NOT NULL DEFAULT '',
  FirstName varchar(50) DEFAULT NULL,
  LastName varchar(50) DEFAULT NULL,
  DisplayName varchar(100) DEFAULT NULL,
  IsAdminUser tinyint(1) NOT NULL DEFAULT '0',
  RegisterTime int(11) NOT NULL DEFAULT '0',
  Pending tinyint(1) NOT NULL DEFAULT '0',
  PendingHash varchar(32) DEFAULT NULL,
  LanguageID int(11) NOT NULL DEFAULT '0',
  CountryID int(11) NOT NULL DEFAULT '0',
  RepositoryLimit tinyint(1) NOT NULL DEFAULT '0',
  Locked tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserUsergroupIndex'
--
DROP TABLE IF EXISTS tblCore_UserUsergroupIndex ;
CREATE TABLE tblCore_UserUsergroupIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL DEFAULT '0',
  UsergroupID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_UserUserProfileFieldIndex'
--
DROP TABLE IF EXISTS tblCore_UserUserProfileFieldIndex ;
CREATE TABLE tblCore_UserUserProfileFieldIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  UserID int(11) NOT NULL DEFAULT '0',
  UserProfileFieldID int(11) NOT NULL DEFAULT '0',
  `Value` text NOT NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCore_VersionCache'
--
DROP TABLE IF EXISTS tblCore_VersionCache;
CREATE TABLE tblCore_VersionCache (
   VersionName varchar(50) NOT NULL DEFAULT '' ,
   VersionNumber varchar(10) NULL DEFAULT NULL,
   LastUpdated DATE NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



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

-- Done!<br><br>


-- Inserting default data for table tblCore_Packages...

INSERT INTO tblCore_Packages (ID,Enabled,APRCode,DBVersion) VALUES ('1','1','core','3.21');
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
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('1','Administrators','31');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('2','Power Users','15');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('3','Users','7');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('4','Read-Only Users','1');
INSERT INTO tblCore_Usergroups (ID,Usergroup,DefaultPermissions) VALUES ('5','Denied Users','0');
-- Done!<br><br>


-- Insert default patterns
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (1, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'Text', '/^.*?$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (2, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'Non-Negative Number', '/^[0-9]*\\.?[0-9]+$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (3, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'Boolean', '/^[01]$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (4, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'Email Address', '/^[\\w\\-\\+\\&amp;\\*]+(?:\\.[\\w\\-\\_\\+\\&amp;\\*]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7}$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (5, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'US Phone Number', '/^\\D?\\d{3}\\D?\\D?\\d{3}\\D?\\d{4}$/');
INSERT INTO tblCore_Patterns (ID, PackageID, Name, Pattern) VALUES (6, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), 'URL', '#^(((http|https|ftp)://)?(\\S*?\\.\\S*?))(\\s|\\;|\\)|\\]|\\[|\\{|\\}|,|\\"|''|:|\\&lt;|$|\\.\\s)$#ie');

-- Insert default unit tests
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'), 1, 'abc');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'), 1, '123');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 1, '0');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 1, '1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 0, '-1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 0, 'a');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 1, '1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 0, 'a');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 1, '0');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 0, '-1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Email Address'), 1, 'noreply@archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217-555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217 555 1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '2175551234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 0, '555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '(217) 555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217.555.1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'www.archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'http://archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'forums.archon.org/index.php');



-- Insert some default userprofilefieldcategories
INSERT INTO tblCore_UserProfileFieldCategories (ID, UserProfileFieldCategory, DisplayOrder) VALUES (1, 'Contact Information', '1');
INSERT INTO tblCore_UserProfileFieldCategories (ID, UserProfileFieldCategory, DisplayOrder) VALUES (2, 'Miscellaneous Information', '2');



-- Insert some default userprofilefields
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (1, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '1', 'Address', NULL, '1', '1', 'textfield', '1', '30', '100', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (2, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '2', 'Address2', NULL, '0', '1', 'textfield', '1', '30', '100', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (3, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '3', 'City', NULL, '1', '1', 'textfield', '1', '30', '75', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (4, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '4', 'StateProvinceID', NULL, '1', '1', 'select', '1', '0', '0', 'getAllStateProvinces');
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (5, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '5', 'ZIPCode', NULL, '0', '1', 'textfield', '2', '5', '5', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (6, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '6', 'ZIPPlusFour', NULL, '0', '1', 'textfield', '2', '4', '4', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (7, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '7', 'Phone', NULL, '1', '1', 'textfield', '5', '15', '25', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (8, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '1', '8', 'PhoneExtension', NULL, '0', '1', 'textfield', '2', '5', '10', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (9, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '2', '1', 'ReceiveUpdates', '1', '1', '1', 'radio', '0', '0', '0', NULL);
INSERT INTO tblCore_UserProfileFields (ID, PackageID, UserProfileFieldCategoryID, DisplayOrder, UserProfileField, DefaultValue, Required, UserEditable, InputType, PatternID, Size, MaxLength, ListDataSource) VALUES (10, (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core'), '2', '2', 'ResearcherTypeID', NULL, '0', '1', 'select', '1', '0', '0', 'getAllResearcherTypes');


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
