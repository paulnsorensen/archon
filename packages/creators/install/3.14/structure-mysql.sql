-- Add Identifier to Creators table
ALTER TABLE tblCreators_Creators ADD Identifier VARCHAR(50) NULL DEFAULT NULL;

-- Add CreatorSourceID to Creators table
ALTER TABLE tblCreators_Creators ADD CreatorSourceID INT NOT NULL DEFAULT '0';


-- Create table 'tblCreators_CreatorSources'
DROP TABLE IF EXISTS tblCreators_CreatorSources ;
CREATE TABLE tblCreators_CreatorSources (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CreatorSource varchar(50) NOT NULL DEFAULT '',
  SourceAbbreviation varchar(10) NOT NULL DEFAULT '',
  Citation text,
  Description text,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
