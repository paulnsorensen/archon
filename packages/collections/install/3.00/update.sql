-- Update DBVersion for Collections
UPDATE tblCore_Packages SET DBVersion = '3.00' WHERE APRCode = 'collections';

UPDATE tblCore_UserProfileFields SET PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'collections') WHERE UserProfileField = 'ResearcherTypeID';

-- Update LevelContainerIdentifiers to remove trailing zeroes from conversion to VARCHARs
UPDATE tblCollections_Content SET LevelContainerIdentifier = REPLACE(LevelContainerIdentifier, '.00', '') WHERE LevelContainerIdentifier LIKE '%.00';