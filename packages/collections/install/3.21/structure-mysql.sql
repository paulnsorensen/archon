
-- Create table 'tblCollections_FindingAidCache'
DROP TABLE IF EXISTS tblCollections_FindingAidCache ;
CREATE TABLE tblCollections_FindingAidCache (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CollectionID int(11) NOT NULL,
  TemplateSet varchar(100) NOT NULL,
  ReadPermissions tinyint(1) NOT NULL DEFAULT '0',
  Dirty tinyint(1) NOT NULL DEFAULT '0',
  RootContentID int(11) NOT NULL DEFAULT '0',
  FindingAidText longtext,
  PRIMARY KEY (ID),
  KEY CollectionID (CollectionID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Add Identifier to Books table
ALTER TABLE tblCollections_Books ADD Identifier VARCHAR(50) NULL DEFAULT NULL;
