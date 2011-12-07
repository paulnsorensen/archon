-- Drop Languages Table
DROP TABLE IF EXISTS tblSubjects_SubjectTypes;

-- Remove Language Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_Modules WHERE Script = 'subjecttypes';
