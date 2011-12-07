
-- Insert File Contents fields to Files table
ALTER TABLE tblDigitalLibrary_Files ADD FileContents VARBINARY(max) NOT NULL DEFAULT 0;
ALTER TABLE tblDigitalLibrary_Files ADD FilePreviewLong VARBINARY(max) NOT NULL DEFAULT 0;
ALTER TABLE tblDigitalLibrary_Files ADD FilePreviewShort VARBINARY(max) NOT NULL DEFAULT 0;


-- Change Enabled field to Default Access Level
DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblDigitalLibrary_Content' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblDigitalLibrary_Content') AND name = 'Enabled')); SET @cmd = 'ALTER TABLE tblDigitalLibrary_Content DROP CONSTRAINT ' + @defname; EXEC(@cmd);
ALTER TABLE tblDigitalLibrary_Content DROP COLUMN Enabled;
ALTER TABLE tblDigitalLibrary_Content ADD DefaultAccessLevel INT NOT NULL DEFAULT '2';

-- Rename the DigitalContent table to adhere to naming standards
EXEC sp_rename tblDigitalLibrary_Content, tblDigitalLibrary_DigitalContent;


DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblDigitalLibrary_Files' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblDigitalLibrary_Files') AND name = 'Enabled')); SET @cmd = 'ALTER TABLE tblDigitalLibrary_Files DROP CONSTRAINT ' + @defname; EXEC(@cmd);
ALTER TABLE tblDigitalLibrary_Files DROP COLUMN Enabled;
ALTER TABLE tblDigitalLibrary_Files ADD DefaultAccessLevel INT NOT NULL DEFAULT '2';


-- Add Browsable field
ALTER TABLE tblDigitalLibrary_DigitalContent ADD Browsable BIT NOT NULL DEFAULT '1';


-- Rename fields in the files table to be consistent with the digital content table
EXEC sp_rename 'tblDigitalLibrary_Files.ContentID', 'DigitalContentID', 'COLUMN';



-- Rename Index tables to adhere to naming standards
EXEC sp_rename tblDigitalLibrary_CreatorIndex, tblDigitalLibrary_DigitalContentCreatorIndex;
EXEC sp_rename 'tblDigitalLibrary_DigitalContentCreatorIndex.ContentID', 'DigitalContentID', 'COLUMN';

EXEC sp_rename tblDigitalLibrary_SubjectIndex, tblDigitalLibrary_DigitalContentSubjectIndex;
EXEC sp_rename 'tblDigitalLibrary_DigitalContentSubjectIndex.ContentID', 'DigitalContentID', 'COLUMN';

EXEC sp_rename tblDigitalLibrary_LanguageIndex, tblDigitalLibrary_DigitalContentLanguageIndex;
EXEC sp_rename 'tblDigitalLibrary_DigitalContentLanguageIndex.ContentID', 'DigitalContentID', 'COLUMN';
