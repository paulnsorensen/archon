-- Add Books Module to tblCore_Modules
INSERT INTO tblCore_Modules (PackageID, Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'books');

-- Add ResearcherType to userprofilefields
INSERT INTO tblCore_UserProfileFields (PackageID, UserProfileField, Required, InputType, Size, ListDataSource) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'ResearcherTypeID', '0', 'list', '0', 'getAllResearcherTypes');

-- Add collection content module
INSERT INTO tblCore_Modules (PackageID, Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'collectioncontent');


-- Add converted research package modules
INSERT INTO tblCore_Modules (PackageID, Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchertypes');
INSERT INTO tblCore_Modules (PackageID, Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchappointmentpurposes');
INSERT INTO tblCore_Modules (PackageID, Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections'), 'researchappointments');