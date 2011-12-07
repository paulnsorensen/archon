-- Drop DescriptiveRules Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_DescriptiveRules') DROP TABLE tblCollections_DescriptiveRules;

-- Drop EADElements Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCollections_EADElements') DROP TABLE tblCollections_EADElements;
