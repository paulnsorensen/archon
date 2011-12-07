-- Drop FileContentsID fields
DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblDigitalLibrary_Files' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblDigitalLibrary_Files') AND name = 'FileContentsID')); SET @cmd = 'ALTER TABLE tblDigitalLibrary_Files DROP CONSTRAINT ' + @defname; EXEC(@cmd);
ALTER TABLE tblDigitalLibrary_Files DROP COLUMN FileContentsID;

DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblDigitalLibrary_Files' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblDigitalLibrary_Files') AND name = 'FilePreviewLongID')); SET @cmd = 'ALTER TABLE tblDigitalLibrary_Files DROP CONSTRAINT ' + @defname; EXEC(@cmd);
ALTER TABLE tblDigitalLibrary_Files DROP COLUMN FilePreviewLongID;

DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblDigitalLibrary_Files' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblDigitalLibrary_Files') AND name = 'FilePreviewShortID')); SET @cmd = 'ALTER TABLE tblDigitalLibrary_Files DROP CONSTRAINT ' + @defname; EXEC(@cmd);
ALTER TABLE tblDigitalLibrary_Files DROP COLUMN FilePreviewShortID;

-- Drop FileContents table
DROP TABLE tblDigitalLibrary_FileContents;