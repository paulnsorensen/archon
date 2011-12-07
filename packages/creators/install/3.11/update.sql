-- Update DBVersion for Creators
UPDATE tblCore_Packages SET DBVersion = '3.11' WHERE APRCode = 'creators';
