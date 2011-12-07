-- Added fields to table tblCreators_Creators
ALTER TABLE tblCreators_Creators ADD RepositoryID INT NULL;
ALTER TABLE tblCreators_Creators ADD ScriptID INT NULL;


-- Add index for which to relate creators to other creators
CREATE TABLE tblCreators_CreatorCreatorIndex (ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY, CreatorID INT NOT NULL DEFAULT '0', RelatedCreatorID INT NOT NULL DEFAULT '0', CreatorRelationshipTypeID INT NOT NULL DEFAULT '0', Description TEXT NULL);

-- Create indexes on relevant fields for index table
CREATE INDEX CreatorID ON tblCreators_CreatorCreatorIndex(CreatorID);
CREATE INDEX RelatedCreatorID ON tblCreators_CreatorCreatorIndex(RelatedCreatorID);
