-- Increase SortTitle and Title fields to 150 Characters in tblCollections_Collections
ALTER TABLE tblCollections_Collections ALTER COLUMN SortTitle VARCHAR(150);
ALTER TABLE tblCollections_Collections ALTER COLUMN Title VARCHAR(150);


-- Create table 'tblCollections_CollectionContentCreatorIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentCreatorIndex') DROP TABLE tblCollections_CollectionContentCreatorIndex ;
CREATE TABLE tblCollections_CollectionContentCreatorIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionContentID INT NOT NULL DEFAULT '0',
  CreatorID INT NOT NULL DEFAULT '0'
);


-- Create table 'tblCollections_CollectionContentSubjectIndex'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_CollectionContentSubjectIndex') DROP TABLE tblCollections_CollectionContentSubjectIndex ;
CREATE TABLE tblCollections_CollectionContentSubjectIndex (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionContentID INT NOT NULL DEFAULT '0',
  SubjectID INT NOT NULL DEFAULT '0'
);


-- Add Indexes to CollectionContentSubjectIndex Table
CREATE INDEX CollectionContentID ON tblCollections_CollectionContentSubjectIndex(CollectionContentID);
CREATE INDEX SubjectID ON tblCollections_CollectionContentSubjectIndex(SubjectID);
