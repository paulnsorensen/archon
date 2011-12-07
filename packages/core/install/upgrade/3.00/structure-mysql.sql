

-- Create/Rename the tblCollections_Repositories table
CREATE TABLE IF NOT EXISTS tblCollections_Repositories (ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, Name varchar(100) NOT NULL DEFAULT '', Administrator varchar(50) DEFAULT NULL, Code varchar(10) DEFAULT NULL, Address varchar(100) DEFAULT NULL, Address2 varchar(100) DEFAULT NULL, City varchar(75) DEFAULT NULL, State char(2) DEFAULT NULL, ZIPCode varchar(5) DEFAULT NULL, ZIPPlusFour varchar(4) DEFAULT NULL, Phone varchar(25) DEFAULT NULL, PhoneExtension varchar(10) DEFAULT NULL, Fax varchar(25) DEFAULT NULL, Email varchar(50) DEFAULT NULL, URL varchar(255) DEFAULT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblCollections_Repositories  to tblCore_Repositories;

-- Rename Type to InputType
ALTER TABLE tblCore_Configuration CHANGE Type InputType VARCHAR(25) NOT NULL, CHANGE Value Value TEXT;

-- Add New fields to Configuration table
ALTER TABLE tblCore_Configuration ADD ModuleID INT NOT NULL DEFAULT '0' AFTER PackageID, ADD PatternID INT NOT NULL DEFAULT '0' AFTER InputType, ADD Encrypted TINYINT(1) NOT NULL DEFAULT '0' AFTER ReadOnly;

-- Create a countries table
CREATE TABLE tblCore_Countries (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, CountryName VARCHAR(100) NOT NULL, ISOAlpha2 CHAR(2) NOT NULL, ISOAlpha3 CHAR(3) NULL, ISONumeric3 CHAR(3) NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create patterns table
CREATE TABLE tblCore_Patterns (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, PackageID INT NOT NULL DEFAULT '0', Name VARCHAR(50) NOT NULL, Pattern TEXT NOT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add StateProvinceID and CountryID to Repositories Table
ALTER TABLE tblCore_Repositories ADD StateProvinceID INT NOT NULL DEFAULT '0' AFTER State, ADD CountryID INT NOT NULL DEFAULT '0' AFTER StateProvinceID;

-- Add an email signature field
ALTER TABLE tblCore_Repositories ADD EmailSignature TEXT NULL AFTER URL;

-- Add a field to the Sessions table to specify whether the connection should be secure
ALTER TABLE tblCore_Sessions ADD SecureConnection TINYINT(1) NOT NULL DEFAULT '0' AFTER Persistent;


-- Create a state/province table
CREATE TABLE tblCore_StateProvinces (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, CountryID INT NOT NULL, StateProvinceName VARCHAR(100) NOT NULL, ISOAlpha2 CHAR(2) NOT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create the userprofilefieldscategories table
CREATE TABLE tblCore_UserProfileFieldCategories (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, UserProfileFieldCategory VARCHAR(25) NOT NULL, DisplayOrder INT NOT NULL DEFAULT '1')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create the userprofilefields table
CREATE TABLE tblCore_UserProfileFields (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, PackageID INT NOT NULL DEFAULT '0', UserProfileFieldCategoryID INT NOT NULL DEFAULT '0', DisplayOrder INT NOT NULL DEFAULT '1', UserProfileField VARCHAR(50) NOT NULL, DefaultValue TEXT NULL, Required TINYINT(1) NOT NULL DEFAULT '0', UserEditable TINYINT(1) NOT NULL DEFAULT '1', InputType VARCHAR(25) NOT NULL, PatternID INT NOT NULL DEFAULT '0', Size INT NOT NULL DEFAULT '30', MaxLength INT NOT NULL DEFAULT '50', ListDataSource VARCHAR(50) NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create an index table to store the userprofilefield country-specific information
CREATE TABLE tblCore_UserProfileFieldCountryIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, UserProfileFieldID INT NOT NULL DEFAULT '0', CountryID INT NOT NULL DEFAULT '0', Required TINYINT(1) NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add a few additional fields to the users table
ALTER TABLE tblCore_Users ADD Email VARCHAR(50) NULL DEFAULT NULL AFTER Login, ADD FirstName VARCHAR(50) NULL DEFAULT NULL AFTER PasswordHash, ADD LastName VARCHAR(50) NULL DEFAULT NULL AFTER FirstName, ADD RegisterTime INT NOT NULL DEFAULT 0 AFTER DisplayName, ADD Pending TINYINT(1) NOT NULL DEFAULT 0 AFTER RegisterTime, ADD PendingHash VARCHAR(32) NULL AFTER Pending, ADD CountryID INT NOT NULL DEFAULT '0' AFTER LanguageID, ADD IsAdminUser TINYINT(1) NOT NULL DEFAULT '0' AFTER DisplayName;

-- Make displayname able to store a full first and last name
ALTER TABLE tblCore_Users CHANGE DisplayName DisplayName VARCHAR(100);

-- Make login large enough to hold an email address
ALTER TABLE tblCore_Users CHANGE Login Login VARCHAR(50);

-- Add a field to the user table to allow users to insist on a secure connection when logged in
ALTER TABLE tblCore_Users ADD RequireSecureConnection TINYINT(1) NOT NULL DEFAULT '0' AFTER LanguageID;

-- Create an index table to store the repositories for users
CREATE TABLE tblCore_UserRepositoryIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', RepositoryID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create an index table to store the usergroups for users
CREATE TABLE tblCore_UserUsergroupIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', UsergroupID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create an index table to store the userprofilefield values for users
CREATE TABLE tblCore_UserUserProfileFieldIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', UserProfileFieldID INT NOT NULL DEFAULT '0', Value TEXT NOT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create the scripts table
CREATE TABLE tblCore_Scripts (ID INT NOT NULL auto_increment PRIMARY KEY, ScriptShort char(4) NOT NULL, ScriptEnglishLong varchar(50) NOT NULL, ScriptFrenchLong varchar(50) NOT NULL, ScriptCode INT NOT NULL, DisplayOrder INT NOT NULL default '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add indexes for Phrase tables
ALTER TABLE tblCore_Phrases ADD INDEX (PhraseName(2));
ALTER TABLE tblCore_Phrases ADD INDEX (ModuleID);
ALTER TABLE tblCore_Phrases ADD INDEX (PackageID);
ALTER TABLE tblCore_Phrases ADD INDEX (PhraseTypeID);
