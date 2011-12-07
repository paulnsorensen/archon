-- Drop CreatorTypes Table
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblCreators_CreatorTypes') DROP TABLE tblCreators_CreatorTypes;
