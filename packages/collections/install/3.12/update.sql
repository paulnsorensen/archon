-- Update DBVersion for Collections
UPDATE tblCore_Packages SET DBVersion = '3.12' WHERE APRCode = 'collections';

-- Update existing collection content to be enabled
UPDATE tblCollections_Content SET Enabled = 1;