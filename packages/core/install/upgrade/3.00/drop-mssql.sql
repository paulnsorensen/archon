-- Drop Researchers Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblResearch_Researchers') DROP TABLE tblResearch_Researchers;

-- Drop UsergroupID and ScratchPad Fields;
DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblCore_Users' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblCore_Users') AND name = 'UsergroupID')); SET @cmd = 'ALTER TABLE tblCore_Users DROP CONSTRAINT ' + @defname; IF EXISTS((SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblCore_Users' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblCore_Users') AND name = 'UsergroupID')))EXEC(@cmd);
ALTER TABLE tblCore_Users DROP COLUMN UsergroupID;
ALTER TABLE tblCore_Users DROP COLUMN ScratchPad;

-- Delete Research Package Modules
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'research');

-- Delete Research Package Phrases
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'research');

-- Delete Research Package from Packages
DELETE FROM tblCore_Packages WHERE APRCode = 'research';

-- Drop Widgets Table
DROP TABLE tblCore_UserHomeWidgetsIndex;