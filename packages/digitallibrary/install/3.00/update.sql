-- Update DBVersion for Digital Library
UPDATE tblCore_Packages SET DBVersion = '3.00' WHERE APRCode = 'digitallibrary';

-- Set Enabled value to the corresponding value for Default Access Level
UPDATE tblDigitalLibrary_DigitalContent SET DefaultAccessLevel = '2' WHERE DefaultAccessLevel = '1';
UPDATE tblDigitalLibrary_Files SET DefaultAccessLevel = '2' WHERE DefaultAccessLevel = '1';
