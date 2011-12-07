-- Insert File Contents fields to Files table
ALTER TABLE tblDigitalLibrary_Files ADD FileContents LONGBLOB NOT NULL AFTER FileTypeID;
ALTER TABLE tblDigitalLibrary_Files ADD FilePreviewLong LONGBLOB NOT NULL AFTER FileContents;
ALTER TABLE tblDigitalLibrary_Files ADD FilePreviewShort LONGBLOB NOT NULL AFTER FilePreviewLong;

-- Rename the DigitalContent table to adhere to naming standards
RENAME TABLE tblDigitalLibrary_Content TO tblDigitalLibrary_DigitalContent;

-- Change Enabled field to Default Access Level
ALTER TABLE tblDigitalLibrary_DigitalContent CHANGE Enabled DefaultAccessLevel INT NOT NULL DEFAULT '2';
ALTER TABLE tblDigitalLibrary_Files CHANGE Enabled DefaultAccessLevel INT NOT NULL DEFAULT '2';


-- Add Browsable field
ALTER TABLE tblDigitalLibrary_DigitalContent ADD Browsable TINYINT(1) NOT NULL DEFAULT '1' AFTER DefaultAccessLevel;



-- Rename fields in the files table to be consistent with the digital content table
ALTER TABLE tblDigitalLibrary_Files CHANGE ContentID DigitalContentID INT NOT NULL DEFAULT '0';



-- Rename index tables to adhere to naming standards
RENAME TABLE tblDigitalLibrary_CreatorIndex TO tblDigitalLibrary_DigitalContentCreatorIndex;
ALTER TABLE tblDigitalLibrary_DigitalContentCreatorIndex CHANGE ContentID DigitalContentID INT NOT NULL DEFAULT '0';

RENAME TABLE tblDigitalLibrary_SubjectIndex TO tblDigitalLibrary_DigitalContentSubjectIndex;
ALTER TABLE tblDigitalLibrary_DigitalContentSubjectIndex CHANGE ContentID DigitalContentID INT NOT NULL DEFAULT '0';

RENAME TABLE tblDigitalLibrary_LanguageIndex TO tblDigitalLibrary_DigitalContentLanguageIndex;
ALTER TABLE tblDigitalLibrary_DigitalContentLanguageIndex CHANGE ContentID DigitalContentID INT NOT NULL DEFAULT '0';
