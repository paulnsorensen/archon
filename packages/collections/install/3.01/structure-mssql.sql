-- Add RepositoryLimit field to tblCollections_Locations
ALTER TABLE tblCollections_Locations ADD RepositoryLimit BIT NOT NULL DEFAULT '0';

-- Create tblCollections_LocationRepositoryIndex
CREATE TABLE tblCollections_LocationRepositoryIndex (
ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
LocationID INT NOT NULL DEFAULT '0',
RepositoryID INT NOT NULL DEFAULT '0'
);
