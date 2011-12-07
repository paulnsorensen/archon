
-- add unit test table for patterns
CREATE TABLE  tblCore_PatternUnitTestIndex (
ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
PatternID INT NOT NULL DEFAULT  '0',
ExpectedResult TINYINT NOT NULL DEFAULT  '1',
`Value` TEXT NULL DEFAULT NULL
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE tblCore_PatternUnitTestIndex ADD INDEX PatternID (PatternID);
