-- Drop FileContentsID fields
ALTER TABLE tblDigitalLibrary_Files DROP FileContentsID;
ALTER TABLE tblDigitalLibrary_Files DROP FilePreviewLongID;
ALTER TABLE tblDigitalLibrary_Files DROP FilePreviewShortID;

-- Drop FileContents table
DROP TABLE tblDigitalLibrary_FileContents;