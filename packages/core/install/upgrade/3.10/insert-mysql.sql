-- Insert default unit tests
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'), 1, 'abc');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Text'), 1, '123');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 1, '0');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 1, '1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 0, '-1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Non-Negative Number'), 0, 'a');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 1, '1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 0, 'a');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 1, '0');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Boolean'), 0, '-1');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'Email Address'), 1, 'noreply@archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217-555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217 555 1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '2175551234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 0, '555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '(217) 555-1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'US Phone Number'), 1, '217.555.1234');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'www.archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'http://archon.org');
INSERT INTO tblCore_PatternUnitTestIndex (PatternID, ExpectedResult, `Value`) VALUES ((SELECT ID FROM tblCore_Patterns WHERE Name = 'URL'), 1, 'forums.archon.org/index.php');


--Insert new configuration directives
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, `Value`, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Pagination Limit', '40', 'textfield', '2', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, `Value`, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Read Permissions', '1', 'radio', '3', '0', '0', NULL);
INSERT INTO tblCore_Configuration (PackageID, ModuleID, Directive, `Value`, InputType, PatternID, ReadOnly, Encrypted, ListDataSource) VALUES ('1', '0', 'Limit Repository Search Results', '0',  'radio', '3', '0', '0', NULL);
