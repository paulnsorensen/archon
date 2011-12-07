-- Update DBVersion for Digital Library
UPDATE tblCore_Packages SET DBVersion = '3.20' WHERE APRCode = 'digitallibrary';

-- Update existing digital content to hyperlink URL
UPDATE tblDigitalLibrary_DigitalContent SET HyperlinkURL = 1;