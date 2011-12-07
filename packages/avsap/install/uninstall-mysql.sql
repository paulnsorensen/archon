-- Dropping AvSAP tables
DROP TABLE IF EXISTS tblAVSAP_AVSAPAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPAudioCassetteAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPFilmAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPGroovedCylinderAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPGroovedDiscAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPInstitutions;
DROP TABLE IF EXISTS tblAVSAP_AVSAPOpenReelAudioAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPOpenReelVideoAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPOpticalMediaAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPStorageFacilities;
DROP TABLE IF EXISTS tblAVSAP_AVSAPVideoCassetteAssessments;
DROP TABLE IF EXISTS tblAVSAP_AVSAPWireAudioAssessments;




-- Removing Configurations
DELETE FROM tblCore_Configuration WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap');


-- Removing Phrases
DELETE FROM tblCore_Phrases WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap');


-- Removing Modules
DELETE FROM tblCore_Modules WHERE PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap');


-- AvSAP Package Uninstalled!
