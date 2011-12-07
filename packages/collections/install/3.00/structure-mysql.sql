-- Add Indexes to Collection Content table
ALTER TABLE tblCollections_Content ADD INDEX (CollectionID);
ALTER TABLE tblCollections_Content ADD INDEX (ParentID);
ALTER TABLE tblCollections_Content ADD INDEX (LevelContainerID);
ALTER TABLE tblCollections_Content ADD INDEX (LevelContainerNumber);







-- Create the Books table
CREATE TABLE tblCollections_Books (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, Title VARCHAR(100) NOT NULL, Edition VARCHAR(15), CopyNumber INT, PublicationDate VARCHAR(50), PlaceOfPublication VARCHAR(50), Publisher VARCHAR(50), Description TEXT, Notes TEXT, NumberOfPages INT, Series VARCHAR(50))  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create the CollectionBookIndex table
CREATE TABLE tblCollections_CollectionBookIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, CollectionID INT NOT NULL DEFAULT '0', BookID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create the BooksCreatorIndex table
CREATE TABLE tblCollections_BookCreatorIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, BookID INT NOT NULL DEFAULT '0', CreatorID INT NOT NULL DEFAULT '0', PrimaryCreator TINYINT  DEFAULT '1')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create the BookLanguageIndex table
CREATE TABLE tblCollections_BookLanguageIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, BookID INT NOT NULL DEFAULT '0', LanguageID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create the BookSubjectIndex table (
CREATE TABLE tblCollections_BookSubjectIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, BookID INT NOT NULL DEFAULT '0', SubjectID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- Create/Rename the ResearcherTypes table
CREATE TABLE IF NOT EXISTS tblResearch_ResearcherTypes (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ResearcherType VARCHAR(50) NOT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblResearch_ResearcherTypes TO tblCollections_ResearcherTypes;




-- Create/Rename the AppointmentPurposes table
CREATE TABLE IF NOT EXISTS tblResearch_AppointmentPurposes (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, AppointmentPurpose VARCHAR(50) NOT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblResearch_AppointmentPurposes TO tblCollections_ResearchAppointmentPurposes;
ALTER TABLE tblCollections_ResearchAppointmentPurposes CHANGE AppointmentPurpose ResearchAppointmentPurpose VARCHAR(50);


-- Create/Rename the Appointments table
CREATE TABLE IF NOT EXISTS tblResearch_Appointments (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, SubmitTime INT NOT NULL DEFAULT '0', ResearcherID INT NOT NULL DEFAULT '0', AppointmentPurposeID INT DEFAULT '0', ArrivalTime INT NOT NULL DEFAULT '0', DepartureTime INT DEFAULT '0', Topic varchar(100) DEFAULT NULL, ResearcherComments TEXT, ArchivistComments TEXT)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblResearch_Appointments TO tblCollections_ResearchAppointments;

-- Create/Rename the AppointmentMaterialsIndex table
CREATE TABLE IF NOT EXISTS tblResearch_AppointmentMaterialsIndex (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, AppointmentID INT NOT NULL DEFAULT '0', CollectionID INT NOT NULL DEFAULT '0', CollectionContentID INT NOT NULL DEFAULT '0', RetrievalTime INT NOT NULL DEFAULT '0', RetrievalUserID INT NOT NULL DEFAULT '0', ReturnTime INT NOT NULL DEFAULT '0', ReturnUserID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblResearch_AppointmentMaterialsIndex TO tblCollections_ResearchAppointmentMaterialsIndex;

-- Create/Rename the Research Carts table
CREATE TABLE IF NOT EXISTS tblResearch_Carts (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ResearcherID INT NOT NULL DEFAULT '0', CollectionID INT NOT NULL DEFAULT '0', CollectionContentID INT NOT NULL DEFAULT '0')  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
RENAME TABLE tblResearch_Carts TO tblCollections_ResearchCarts;


-- Rename Index tables to adhere to naming conventions
RENAME TABLE tblCollections_CreatorIndex TO tblCollections_CollectionCreatorIndex;
RENAME TABLE tblCollections_SubjectIndex TO tblCollections_CollectionSubjectIndex;
RENAME TABLE tblCollections_LanguageIndex TO tblCollections_CollectionLanguageIndex;
RENAME TABLE tblCollections_LocationIndex TO tblCollections_CollectionLocationIndex;

-- Add Indexes to CollectionSubjectIndex Table
ALTER TABLE tblCollections_CollectionSubjectIndex ADD INDEX (CollectionID);
ALTER TABLE tblCollections_CollectionSubjectIndex ADD INDEX (SubjectID);

-- Add BiogHist for collections
ALTER TABLE tblCollections_Collections ADD BiogHistAuthor VARCHAR(100) NULL DEFAULT NULL, ADD BiogHist TEXT;



-- Create temporary table to count the amount of records in each grouping for content
CREATE TABLE cmax SELECT CollectionID, ParentID, LevelContainerID, COUNT( * )+1 AS Count FROM tblCollections_Content WHERE 1 GROUP BY CollectionID, ParentID, LevelContainerID;

-- Create temporary content table for which to compare against
CREATE TABLE ctmp SELECT ID,CollectionID,ParentID,LevelContainerID,LevelContainerNumber FROM tblCollections_Content;

-- Create temporary indexes on temporary tables
ALTER TABLE ctmp ADD INDEX (CollectionID);
ALTER TABLE ctmp ADD INDEX (ParentID);
ALTER TABLE ctmp ADD INDEX (LevelContainerID);
ALTER TABLE ctmp ADD INDEX (LevelContainerNumber);

ALTER TABLE cmax ADD INDEX (CollectionID);
ALTER TABLE cmax ADD INDEX (ParentID);
ALTER TABLE cmax ADD INDEX (LevelContainerID);



-- Create SortOrder column for content
ALTER TABLE tblCollections_Content ADD SortOrder INT NOT NULL DEFAULT '0';

-- Update SortOrder NOTICE -- THIS WILL TAKE A LONG TIME TO COMPLETE!
UPDATE tblCollections_Content SET SortOrder = (SELECT Count FROM cmax WHERE cmax.CollectionID = tblCollections_Content.CollectionID AND cmax.ParentID = tblCollections_Content.ParentID AND cmax.LevelContainerID = tblCollections_Content.LevelContainerID ) - (SELECT COUNT( ctmp.LevelContainerNumber ) AS SO FROM ctmp WHERE ctmp.LevelContainerNumber >= tblCollections_Content.LevelContainerNumber AND ctmp.CollectionID = tblCollections_Content.CollectionID AND ctmp.ParentID = tblCollections_Content.ParentID AND ctmp.LevelContainerID = tblCollections_Content.LevelContainerID GROUP BY ctmp.CollectionID, ctmp.ParentID, ctmp.LevelContainerID);

-- Drop Temporary Index
ALTER TABLE tblCollections_Content DROP INDEX LevelContainerNumber;

-- Make LevelContainerNumbers into VARCHARS
ALTER TABLE tblCollections_Content CHANGE LevelContainerNumber LevelContainerIdentifier VARCHAR( 10 ) NOT NULL;

-- Drop Temp Tables
DROP TABLE cmax;
DROP TABLE ctmp;
