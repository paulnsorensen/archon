-- Add Abstract field to tblCollections_Collections
ALTER TABLE tblCollections_Collections ADD Abstract TEXT NULL AFTER Scope;

-- Add PrivateTitle field to tblCollections_Content
ALTER TABLE tblCollections_Content ADD PrivateTitle TEXT NULL AFTER Title;

-- Add Enabled field to tblCollections_Content
ALTER TABLE tblCollections_Content ADD Enabled tinyint(1) NOT NULL DEFAULT '0';
