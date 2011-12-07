-- Create table 'tblSubjects_Subjects'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblSubjects_Subjects') DROP TABLE tblSubjects_Subjects; 
CREATE TABLE tblSubjects_Subjects (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  Subject varchar(100) NOT NULL DEFAULT '',
  SubjectTypeID INT NOT NULL DEFAULT '0',
  SubjectSourceID INT NOT NULL DEFAULT '0',
  ParentID INT NOT NULL DEFAULT '0',
  LastModified INT NOT NULL DEFAULT '0',
  ModifiedByID INT NOT NULL DEFAULT '0',
  Description TEXT NULL,
  Identifier varchar(50) NULL DEFAULT NULL
);


-- Create table 'tblSubjects_SubjectSources'

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblSubjects_SubjectSources') DROP TABLE tblSubjects_SubjectSources; 
CREATE TABLE tblSubjects_SubjectSources (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  SubjectSource varchar(50) NOT NULL DEFAULT '',
  EADSource varchar(10) NOT NULL DEFAULT ''
);



-- Inserting default data for table tblSubjects_SubjectSources...

SET IDENTITY_INSERT tblSubjects_SubjectSources ON;
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('1','Library of Congress Subject Heading','lcsh');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('2','Library of Congress Name Authority File','lcnaf');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('3','Art & Architecture Thesaurus','aat');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('4','VRA Core', 'vra');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('5','Local Authority File','local');
SET IDENTITY_INSERT tblSubjects_SubjectSources OFF;
-- Done!



-- Inserting default data for table tblCore_Modules...
DECLARE @package_subjects INT; SET @package_subjects = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_subjects,'subjects');
DECLARE @package_subjects INT; SET @package_subjects = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_subjects,'subjectsources');


-- Done!

-- Add Index to ParentID on Subjects table
CREATE INDEX ParentID ON tblSubjects_Subjects(ParentID);

-- Database Structure Created!
