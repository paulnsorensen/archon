
-- Create table 'tblCore_PatternUnitTestIndex'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_PatternUnitTestIndex') DROP TABLE tblCore_PatternUnitTestIndex;
CREATE TABLE tblCore_PatternUnitTestIndex (
   ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
   PatternID INT NOT NULL DEFAULT  '0',
   ExpectedResult BIT NOT NULL DEFAULT  '1',
   Value text NULL
);

-- Add index for PatternUnitTestIndex table
CREATE INDEX PatternID ON tblCore_PatternUnitTestIndex(PatternID);