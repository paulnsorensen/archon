-- Added fields to table tblCreators_Creators
ALTER TABLE tblCreators_Creators ADD RepositoryID INT  NULL DEFAULT '0', ADD ScriptID INT  NULL DEFAULT '0';

-- Add index for which to relate creators to other creators
CREATE TABLE tblCreators_CreatorCreatorIndex (ID INT NOT NULL auto_increment PRIMARY KEY, CreatorID INT NOT NULL DEFAULT '0', RelatedCreatorID INT NOT NULL DEFAULT '0', CreatorRelationshipTypeID INT NOT NULL DEFAULT '0', Description TEXT NULL)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create indexes on relevant fields for index table
ALTER TABLE tblCreators_CreatorCreatorIndex ADD INDEX (CreatorID);
ALTER TABLE tblCreators_CreatorCreatorIndex ADD INDEX (RelatedCreatorID);

