-- Add new EAD Elements
SET IDENTITY_INSERT tblCollections_EADElements ON;
INSERT INTO tblCollections_EADElements (ID,EADElement,EADTag) VALUES ('20', 'Extent', 'extent');
INSERT INTO tblCollections_EADElements (ID,EADElement,EADTag) VALUES ('21', 'Dimensions', 'dimensions');
INSERT INTO tblCollections_EADElements (ID,EADElement,EADTag) VALUES ('22', 'Biography or History', 'bioghist');
INSERT INTO tblCollections_EADElements (ID,EADElement,EADTag) VALUES ('23', 'Physical Facet', 'physfacet');
SET IDENTITY_INSERT tblCollections_EADElements OFF;
