-- Add Identifier to Creators table
ALTER TABLE tblCreators_Creators ADD Identifier VARCHAR(50) NULL;

-- Add CreatorSourceID to Creators table
ALTER TABLE tblCreators_Creators ADD CreatorSourceID INT NOT NULL DEFAULT '0';

-- Create table 'tblCreators_CreatorSources'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCreators_CreatorSources') DROP TABLE tblCreators_CreatorSources;
CREATE TABLE tblCreators_CreatorSources (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  CreatorSource varchar(50) NOT NULL DEFAULT '',
  SourceAbbreviation varchar(10) NOT NULL DEFAULT '',
  Citation TEXT NULL,
  Description TEXT NULL
);
