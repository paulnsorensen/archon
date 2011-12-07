-- Drop Languages Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Languages') DROP TABLE tblCore_Languages;

-- Remove Languages Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_Modules WHERE Script = 'languages';


-- Drop Countries Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Countries') DROP TABLE tblCore_Countries;

-- Drop PhraseTypes Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_PhraseTypes') DROP TABLE tblCore_PhraseTypes;

-- Drop Scripts Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCore_Scripts') DROP TABLE tblCore_Scripts;

-- Remove Scripts Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_Modules WHERE Script = 'scripts';
