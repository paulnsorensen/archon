-- Update DBVersion for Core
UPDATE tblCore_Packages SET DBVersion = '3.00' WHERE APRCode = 'core';

-- Change users module to adminusers
UPDATE tblCore_Modules SET Script = 'adminusers' WHERE tblCore_Modules.Script = 'users';

-- Move Repositories module to core package from collections
UPDATE tblCore_Modules SET PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core') WHERE Script = 'repositories';

-- Convert configuration Types to proper InputType and PatternID
UPDATE tblCore_Configuration SET InputType = 'textfield', PatternID = 1 WHERE InputType = 'string';
UPDATE tblCore_Configuration SET InputType = 'textfield', PatternID = 2 WHERE InputType = 'number';
UPDATE tblCore_Configuration SET InputType = 'select', PatternID = 2 WHERE InputType = 'list';
UPDATE tblCore_Configuration SET InputType = 'radio', PatternID = 3 WHERE InputType = 'boolean';
UPDATE tblCore_Configuration SET PatternID = 1 WHERE InputType = 'password';


-- Update administrative users to ensure they have administrative access
UPDATE tblCore_Users SET IsAdminUser = 1 WHERE UsergroupID != 0 ;

-- Change Default Repository PackageID to Core
UPDATE tblCore_Configuration SET PackageID = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'core') WHERE Directive = 'Default Repository';

-- Remove configuration for confirm researcher accounts
DELETE FROM tblCore_Configuration WHERE Directive = 'Confirm Researcher Accounts';
