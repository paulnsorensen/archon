-- Update DBVersion for Core
UPDATE tblCore_Packages SET DBVersion = '3.01' WHERE APRCode = 'core';

-- Fix configuration patterns for default theme and template set
UPDATE tblCore_Configuration SET PatternID = '1' WHERE Directive = 'Default Theme' OR Directive = 'Default Template Set';