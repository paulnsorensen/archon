-- Increase SortTitle and Title fields to 150 Characters in tblCollections_Collections
ALTER TABLE  tblCollections_Collections CHANGE SortTitle SortTitle VARCHAR(150) NOT NULL;
ALTER TABLE  tblCollections_Collections CHANGE Title Title VARCHAR(150) NOT NULL;


-- Create table 'tblCollections_CollectionContentCreatorIndex'
DROP TABLE IF EXISTS tblCollections_CollectionContentCreatorIndex ;
CREATE TABLE tblCollections_CollectionContentCreatorIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  CreatorID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionContentID (CollectionContentID),
  KEY CreatorID (CreatorID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Create table 'tblCollections_CollectionContentSubjectIndex'
DROP TABLE IF EXISTS tblCollections_CollectionContentSubjectIndex ;
CREATE TABLE tblCollections_CollectionContentSubjectIndex (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionContentID int(11) NOT NULL DEFAULT '0',
  SubjectID int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ID),
  KEY CollectionContentID (CollectionContentID),
  KEY SubjectID (SubjectID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
