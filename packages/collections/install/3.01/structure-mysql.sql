-- Add RepositoryLimit field to tblCollections_Locations
ALTER TABLE tblCollections_Locations ADD RepositoryLimit TINYINT(1) NOT NULL DEFAULT '0';

-- Create tblCollections_LocationRepositoryIndex
CREATE TABLE tblCollections_LocationRepositoryIndex (
ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
LocationID int(11) NOT NULL DEFAULT '0',
RepositoryID int(11) NOT NULL DEFAULT '0'
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
