-- Insert default unit tests
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'abc');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '123');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '0');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '-1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, 'a');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, 'a');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '0');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '-1');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'Email Address'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'noreply@archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217-555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217 555 1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '2175551234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 0, '555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '(217) 555-1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, '217.555.1234');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'www.archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'http://archon.org');
DECLARE @patternid INT; SET @patternid = (SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'); INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, Value) VALUES (@patternid, 1, 'forums.archon.org/index.php');

--Insert new configuration for directives
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Pagination Limit', '40', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Read Permissions', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, Value, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Search Results', '0',  'radio', '3', '0', '0', NULL);
