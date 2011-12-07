-- Inserting default data for table tblCreators_CreatorSources...
SET IDENTITY_INSERT tblCreators_CreatorSources ON;
INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('1','Library of Congress Name Authority File','lcnaf');
INSERT INTO tblCreators_CreatorSources (ID,CreatorSource,SourceAbbreviation) VALUES ('2','Local Authority File','local');
SET IDENTITY_INSERT tblCreators_CreatorSources OFF;

-- Inserting default data for table tblCore_Modules...
DECLARE @package_creators INT; SET @package_creators = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'creators'); INSERT INTO tblCore_Modules  (PackageID,Script) VALUES (@package_creators, 'creatorsources');
