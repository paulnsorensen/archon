-- Drop Languages Table
DROP TABLE IF EXISTS tblCore_Languages;

-- Remove Language Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'languages');
DELETE FROM tblCore_Modules WHERE Script = 'languages';


-- Drop Countries Table
DROP TABLE IF EXISTS tblCore_Countries;

-- Drop PhraseTypes Table
DROP TABLE IF EXISTS tblCore_PhraseTypes;

-- Drop Scripts Table
DROP TABLE IF EXISTS tblCore_Scripts;

-- Remove Scripts Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'scripts');
DELETE FROM tblCore_Modules WHERE Script = 'scripts';
