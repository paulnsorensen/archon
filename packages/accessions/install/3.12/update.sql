-- Update DBVersion for Accessions
UPDATE tblCore_Packages SET DBVersion = '3.12' WHERE APRCode = 'accessions';
