-- Add Indexes to Collection Content table
CREATE INDEX CollectionID ON tblCollections_Content(CollectionID);
CREATE INDEX ParentID ON tblCollections_Content(ParentID);
CREATE INDEX LevelContainerID ON tblCollections_Content(LevelContainerID);

-- Create the Books table
CREATE TABLE tblCollections_Books (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, Title VARCHAR(100) NOT NULL, Edition VARCHAR(15), CopyNumber INT, PublicationDate VARCHAR(50), PlaceOfPublication VARCHAR(50), Publisher VARCHAR(50), Description TEXT NULL, Notes TEXT NULL, NumberOfPages INT, Series VARCHAR(50));

-- Create the CollectionBookIndex table
CREATE TABLE tblCollections_CollectionBookIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, CollectionID INT, BookID INT);

-- Create the BooksCreatorIndex table
CREATE TABLE tblCollections_BookCreatorIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, BookID INT, CreatorID INT, PrimaryCreator BIT DEFAULT '1');


-- Create the BookLanguageIndex table
CREATE TABLE tblCollections_BookLanguageIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, BookID INT, LanguageID INT);

-- Create the BookSubjectIndex table (
CREATE TABLE tblCollections_BookSubjectIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, BookID INT, SubjectID INT);




-- Create/Rename the ResearcherTypes table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_ResearcherTypes') CREATE TABLE tblResearch_ResearcherTypes (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, ResearcherType VARCHAR(50) NOT NULL);
EXEC sp_rename tblResearch_ResearcherTypes, tblCollections_ResearcherTypes;





-- Create/Rename the AppointmentPurposes table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_AppointmentPurposes') CREATE TABLE tblResearch_AppointmentPurposes (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AppointmentPurpose VARCHAR(50) NOT NULL);
EXEC sp_rename tblResearch_AppointmentPurposes, tblCollections_ResearchAppointmentPurposes;
EXEC sp_rename 'tblCollections_ResearchAppointmentPurposes.AppointmentPurpose', 'ResearchAppointmentPurpose', 'COLUMN';



-- Create/Rename the Appointments table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_Appointments') CREATE TABLE tblResearch_Appointments (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, SubmitTime INT NOT NULL DEFAULT '0', ResearcherID INT NOT NULL DEFAULT '0', AppointmentPurposeID INT DEFAULT '0', ArrivalTime INT NOT NULL DEFAULT '0', DepartureTime INT DEFAULT '0', Topic varchar(100) NULL DEFAULT NULL, ResearcherComments TEXT NULL, ArchivistComments TEXT NULL);
EXEC sp_rename tblResearch_Appointments, tblCollections_ResearchAppointments;

-- Create/Rename the AppointmentMaterialsIndex table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_AppointmentMaterialsIndex') CREATE TABLE tblResearch_AppointmentMaterialsIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, AppointmentID INT NOT NULL DEFAULT '0', CollectionID INT NOT NULL DEFAULT '0', CollectionContentID INT NOT NULL DEFAULT '0', RetrievalTime INT NOT NULL DEFAULT '0', RetrievalUserID INT NOT NULL DEFAULT '0', ReturnTime INT NOT NULL DEFAULT '0', ReturnUserID INT NOT NULL DEFAULT '0');
EXEC sp_rename tblResearch_AppointmentMaterialsIndex, tblCollections_ResearchAppointmentMaterialsIndex;

-- Create/Rename the Research Carts table
IF NOT EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_Carts') CREATE TABLE tblResearch_Carts (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, ResearcherID INT NOT NULL DEFAULT '0', CollectionID INT NOT NULL DEFAULT '0', CollectionContentID INT NOT NULL DEFAULT '0');
EXEC sp_rename tblResearch_Carts, tblCollections_ResearchCarts;


-- Rename Index tables to adhere to naming conventions
EXEC sp_rename tblCollections_CreatorIndex, tblCollections_CollectionCreatorIndex;
EXEC sp_rename tblCollections_SubjectIndex, tblCollections_CollectionSubjectIndex;
EXEC sp_rename tblCollections_LanguageIndex, tblCollections_CollectionLanguageIndex;
EXEC sp_rename tblCollections_LocationIndex, tblCollections_CollectionLocationIndex;

-- Add Indexes to CollectionSubjectIndex Table
CREATE INDEX CollectionID ON tblCollections_CollectionSubjectIndex(CollectionID);
CREATE INDEX SubjectID ON tblCollections_CollectionSubjectIndex(SubjectID);

-- Add BiogHist for collections
ALTER TABLE tblCollections_Collections ADD BiogHistAuthor VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE tblCollections_Collections ADD BiogHist TEXT NULL;




-- Create temporary table to count the amount of records in each grouping for content
SELECT CollectionID, ParentID, LevelContainerID, COUNT( * )+1 AS Count INTO cmax FROM tblCollections_Content WHERE 1=1 GROUP BY CollectionID, ParentID, LevelContainerID;

-- Create temporary content table for which to compare against
SELECT ID,CollectionID,ParentID,LevelContainerID,LevelContainerNumber INTO ctmp FROM tblCollections_Content;

-- Create SortOrder column for content
ALTER TABLE tblCollections_Content ADD SortOrder INT NOT NULL DEFAULT '0';

-- Update SortOrder NOTICE -- THIS WILL TAKE A LONG TIME TO COMPLETE!
UPDATE tblCollections_Content SET SortOrder = (SELECT Count FROM cmax WHERE cmax.CollectionID = tblCollections_Content.CollectionID AND cmax.ParentID = tblCollections_Content.ParentID AND cmax.LevelContainerID = tblCollections_Content.LevelContainerID ) - (SELECT COUNT( ctmp.LevelContainerNumber ) AS SO FROM ctmp WHERE ctmp.LevelContainerNumber >= tblCollections_Content.LevelContainerNumber AND ctmp.CollectionID = tblCollections_Content.CollectionID AND ctmp.ParentID = tblCollections_Content.ParentID AND ctmp.LevelContainerID = tblCollections_Content.LevelContainerID GROUP BY ctmp.CollectionID, ctmp.ParentID, ctmp.LevelContainerID);


-- Make LevelContainerNumbers into VARCHARS
DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblCollections_Content' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblCollections_Content') AND name = 'LevelContainerNumber')); SET @cmd = 'ALTER TABLE tblCollections_Content DROP CONSTRAINT ' + @defname; EXEC(@cmd);
EXEC sp_rename 'tblCollections_Content.LevelContainerNumber', 'LevelContainerIdentifier', 'COLUMN';
ALTER TABLE tblCollections_Content ALTER COLUMN LevelContainerIdentifier VARCHAR(10) NOT NULL;

-- Drop Temp Tables
DROP TABLE cmax;
DROP TABLE ctmp;

