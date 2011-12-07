-- Update DBVersion for Core
UPDATE tblCore_Packages SET DBVersion = '3.13' WHERE APRCode = 'core';

-- Change State/Province Manager to Region Manager
UPDATE tblCore_Modules SET Script = 'regions' WHERE Script = 'stateprovinces';