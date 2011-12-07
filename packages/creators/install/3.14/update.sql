-- Update DBVersion for Creators
UPDATE tblCore_Packages SET DBVersion = '3.14' WHERE APRCode = 'creators';

-- Set CreatorSourceIDs
UPDATE tblCreators_Creators SET CreatorSourceID = 1 WHERE LCNAFCompliant = 1;
UPDATE tblCreators_Creators SET CreatorSourceID = 2 WHERE LCNAFCompliant = 0;

-- Copy LCNAFDates to Dates if LCNAFDates are set and record is LCNAF Compliant
UPDATE tblCreators_Creators SET Dates = LCNAFDates WHERE LCNAFCompliant = 1 AND LCNAFDates IS NOT NULL AND LTRIM(RTRIM(LCNAFDates)) != '';