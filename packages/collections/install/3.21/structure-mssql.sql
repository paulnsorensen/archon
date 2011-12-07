
-- Create table 'tblCollections_FindingAidCache'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_FindingAidCache') DROP TABLE tblCollections_FindingAidCache;
CREATE TABLE tblCollections_FindingAidCache (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CollectionID INT NOT NULL,
  TemplateSet varchar(100) NOT NULL,
  ReadPermissions BIT NOT NULL DEFAULT '0',
  Dirty BIT NOT NULL DEFAULT '0',
  RootContentID INT NOT NULL DEFAULT '0',
  FindingAidText TEXT 
);

CREATE INDEX CollectionID ON tblCollections_FindingAidCache(CollectionID);


-- Add Identifier to Books table
ALTER TABLE tblCollections_Books ADD Identifier VARCHAR(50) NULL;
