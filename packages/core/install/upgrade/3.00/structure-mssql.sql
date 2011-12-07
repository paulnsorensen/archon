-- Create/Rename the tblCollections_Repositories table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_Repositories') CREATE TABLE tblCollections_Repositories (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, Name varchar(100) NOT NULL DEFAULT '', Administrator varchar(50) NULL DEFAULT NULL, Code varchar(10) NULL DEFAULT NULL, Address varchar(100) NULL DEFAULT NULL, Address2 varchar(100) NULL DEFAULT NULL, City varchar(75) NULL DEFAULT NULL, State char(2) NULL DEFAULT NULL, ZIPCode varchar(5) NULL DEFAULT NULL, ZIPPlusFour varchar(4) NULL DEFAULT NULL, Phone varchar(25) NULL DEFAULT NULL, PhoneExtension varchar(10) NULL DEFAULT NULL, Fax varchar(25) NULL DEFAULT NULL, Email varchar(50) NULL DEFAULT NULL, URL varchar(255) NULL DEFAULT NULL);
EXEC sp_rename tblCollections_Repositories, tblCore_Repositories;

-- Rename Type to InputType
EXEC sp_rename 'tblCore_Configuration.Type', 'InputType', 'COLUMN';
ALTER TABLE tblCore_Configuration ALTER COLUMN InputType VARCHAR(25) NOT NULL;
ALTER TABLE tblCore_Configuration ALTER COLUMN Value TEXT NULL;


-- Add New fields to Configuration table
ALTER TABLE tblCore_Configuration ADD ModuleID INT NOT NULL DEFAULT '0';
ALTER TABLE tblCore_Configuration ADD PatternID INT NOT NULL DEFAULT '0';
ALTER TABLE tblCore_Configuration ADD Encrypted BIT NOT NULL DEFAULT '0';

-- Create a countries table
CREATE TABLE tblCore_Countries (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, CountryName VARCHAR(100) NOT NULL, ISOAlpha2 CHAR(2) NOT NULL, ISOAlpha3 CHAR(3) NULL, ISONumeric3 CHAR(3) NULL);

-- Create patterns table
CREATE TABLE tblCore_Patterns (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, PackageID INT NOT NULL DEFAULT '0', Name VARCHAR(50) NOT NULL, Pattern TEXT NOT NULL);

-- Add StateProvinceID and CountryID to Repositories Table
ALTER TABLE tblCore_Repositories ADD StateProvinceID INT NOT NULL DEFAULT '0';
ALTER TABLE tblCore_Repositories ADD CountryID INT NOT NULL DEFAULT '0';

-- Add an email signature field
ALTER TABLE tblCore_Repositories ADD EmailSignature TEXT NULL;

-- Add a field to the Sessions table to specify whether the connection should be secure
ALTER TABLE tblCore_Sessions ADD SecureConnection BIT NOT NULL DEFAULT '0';



-- Create a state/province table
CREATE TABLE tblCore_StateProvinces (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, CountryID INT NOT NULL, StateProvinceName VARCHAR(100) NOT NULL, ISOAlpha2 CHAR(2) NOT NULL);


-- Create the userprofilefieldscategories table
CREATE TABLE tblCore_UserProfileFieldCategories (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, UserProfileFieldCategory VARCHAR(25) NOT NULL, DisplayOrder INT NOT NULL DEFAULT '1');

-- Create the userprofilefields table
CREATE TABLE tblCore_UserProfileFields (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, PackageID INT NOT NULL DEFAULT '0', UserProfileFieldCategoryID INT NOT NULL DEFAULT '0', DisplayOrder INT NOT NULL DEFAULT '1', UserProfileField VARCHAR(50) NOT NULL, DefaultValue TEXT NULL, Required BIT NOT NULL DEFAULT '0', UserEditable BIT NOT NULL DEFAULT '1', InputType VARCHAR(25) NOT NULL, PatternID INT NOT NULL DEFAULT '0', Size INT NOT NULL DEFAULT '30', MaxLength INT NOT NULL DEFAULT '50', ListDataSource VARCHAR(50) NULL);

-- Create an index table to store the userprofilefield country-specific information
CREATE TABLE tblCore_UserProfileFieldCountryIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, UserProfileFieldID INT NOT NULL DEFAULT '0', CountryID INT NOT NULL DEFAULT '0', Required BIT NOT NULL DEFAULT '0');

-- Add a few additional fields to the users table
ALTER TABLE tblCore_Users ADD Email VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE tblCore_Users ADD FirstName VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE tblCore_Users ADD LastName VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE tblCore_Users ADD RegisterTime INT NOT NULL DEFAULT 0;
ALTER TABLE tblCore_Users ADD Pending BIT NOT NULL DEFAULT 0;
ALTER TABLE tblCore_Users ADD PendingHash VARCHAR(32) NULL;
ALTER TABLE tblCore_Users ADD CountryID INT NOT NULL DEFAULT '0';
ALTER TABLE tblCore_Users ADD IsAdminUser BIT NOT NULL DEFAULT '0';

-- Make displayname able to store a full first and last name
ALTER TABLE tblCore_Users ALTER COLUMN DisplayName VARCHAR(100);

-- Make login field large enough to store email address
ALTER TABLE tblCore_Users ALTER COLUMN Login VARCHAR(50);

-- Add a field to the user table to allow users to insist on a secure connection when logged in
ALTER TABLE tblCore_Users ADD RequireSecureConnection BIT NOT NULL DEFAULT '0';

-- Create an index table to store the repositories for users
CREATE TABLE tblCore_UserRepositoryIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', RepositoryID INT NOT NULL DEFAULT '0');

-- Create an index table to store the usergroups for users
CREATE TABLE tblCore_UserUsergroupIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', UsergroupID INT NOT NULL DEFAULT '0');

-- Create an index table to store the userprofilefield values for users
CREATE TABLE tblCore_UserUserProfileFieldIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, UserID INT NOT NULL DEFAULT '0', UserProfileFieldID INT NOT NULL DEFAULT '0', Value TEXT NOT NULL);

-- Create the scripts table
CREATE TABLE tblCore_Scripts (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, ScriptShort char(4) NOT NULL, ScriptEnglishLong varchar(50) NOT NULL, ScriptFrenchLong varchar(50) NOT NULL, ScriptCode INT NOT NULL, DisplayOrder INT NOT NULL default '0');

-- Add indexes for Phrase tables
CREATE INDEX PhraseName ON tblCore_Phrases(PhraseName);
CREATE INDEX ModuleID ON tblCore_Phrases(ModuleID);
CREATE INDEX PackageID ON tblCore_Phrases(PackageID);
CREATE INDEX PhraseTypeID ON tblCore_Phrases(PhraseTypeID);
