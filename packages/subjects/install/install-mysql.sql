-- Create table 'tblSubjects_Subjects'

DROP TABLE IF EXISTS tblSubjects_Subjects; 
CREATE TABLE tblSubjects_Subjects (
  ID int(11) NOT NULL AUTO_INCREMENT,
  `Subject` varchar(100) NOT NULL DEFAULT '',
  SubjectTypeID int(11) NOT NULL DEFAULT '0',
  SubjectSourceID int(11) NOT NULL DEFAULT '0',
  ParentID int(11) NOT NULL DEFAULT '0',
  LastModified int(11) NOT NULL DEFAULT '0',
  ModifiedByID int(11) NOT NULL DEFAULT '0',
  Description text,
  Identifier VARCHAR(50) NULL,
  PRIMARY KEY (ID),
  KEY ParentID (ParentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblSubjects_SubjectSources'

DROP TABLE IF EXISTS tblSubjects_SubjectSources; 
CREATE TABLE tblSubjects_SubjectSources (
  ID int(11) NOT NULL AUTO_INCREMENT,
  SubjectSource varchar(50) NOT NULL DEFAULT '',
  EADSource varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- Inserting default data for table tblSubjects_SubjectSources...


INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('1','Library of Congress Subject Heading','lcsh');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('2','Library of Congress Name Authority File','lcnaf');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('3','Art & Architecture Thesaurus','aat');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('4','VRA Core', 'vra');
INSERT INTO tblSubjects_SubjectSources (ID,SubjectSource,EADSource) VALUES ('5','Local Authority File','local');


-- Done!



-- Inserting default data for table tblCore_Modules...

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects'),'subjects');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects'),'subjectsources');

-- Done!

-- Database Structure Created!
