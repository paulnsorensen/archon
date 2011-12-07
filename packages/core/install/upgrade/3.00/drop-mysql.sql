-- Drop Researchers Table
DROP TABLE IF EXISTS tblResearch_Researchers;

-- Drop UsergroupID and ScratchPad Fields;
ALTER TABLE tblCore_Users DROP UsergroupID;
ALTER TABLE tblCore_Users DROP ScratchPad;

-- Delete Research Package Modules
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'research');

-- Delete Research Package Phrases
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'research');

-- Delete Research Package from Packages
DELETE FROM tblCore_Packages WHERE APRCode = 'research';

-- Drop Widgets Table
DROP TABLE tblCore_UserHomeWidgetsIndex;
