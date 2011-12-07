-- Dropping subjects tables...
DROP TABLE IF EXISTS tblSubjects_Subjects;
DROP TABLE IF EXISTS tblSubjects_SubjectSources;
-- Done!<br>

-- Removing Configurations...
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects');
-- Done!<br>

-- Removing Phrases...
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects');
-- Done!<br>

-- Removing Modules...
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'subjects');

-- Done!<br>

-- Subjects Package Uninstalled!
