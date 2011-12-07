-- Drop Languages Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblSubjects_SubjectTypes') DROP TABLE tblSubjects_SubjectTypes;

-- Remove Languages Module
DELETE FROM tblCore_Phrases WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_UserPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_UsergroupPermissions WHERE ModuleID = (SELECT ID FROM tblCore_Modules WHERE Script = 'subjecttypes');
DELETE FROM tblCore_Modules WHERE Script = 'subjecttypes';
