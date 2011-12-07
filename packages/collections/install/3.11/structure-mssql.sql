-- Add Abstract field to tblCollections_Collections
ALTER TABLE tblCollections_Collections ADD Abstract TEXT NULL;

-- Add PrivateTitle field to tblCollections_Content
ALTER TABLE tblCollections_Content ADD PrivateTitle TEXT NULL;

-- Add Enabled field to tblCollections_Content
ALTER TABLE tblCollections_Content ADD Enabled BIT NOT NULL DEFAULT '0';
