-- Update DBVersion for Core
UPDATE tblCore_Packages SET DBVersion = '3.10' WHERE APRCode = 'core';

-- Fix Patterns
UPDATE tblCore_Patterns SET Pattern = '/^[0-9]*\\.?[0-9]+$/' WHERE Name = 'Non-Negative Number';
UPDATE tblCore_Patterns SET Pattern = '/^.*?$/' WHERE Name = 'Text';
UPDATE tblCore_Patterns SET Pattern = '/^[01]$/' WHERE Name = 'Boolean';
UPDATE tblCore_Patterns SET Pattern = '/^[\\w\\-\\+\\&amp;\\*]+(?:\\.[\\w\\-\\_\\+\\&amp;\\*]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7}$/' WHERE Name = 'Email Address';
UPDATE tblCore_Patterns SET Pattern = '/^\\D?\\d{3}\\D?\\D?\\d{3}\\D?\\d{4}$/' WHERE Name = 'US Phone Number';
UPDATE tblCore_Patterns SET Pattern = '#^(((http|https|ftp)://)?(\\S*?\\.\\S*?))(\\s|\\;|\\)|\\]|\\[|\\{|\\}|,|\\"|''|:|\\&lt;|$|\\.\\s)$#ie' WHERE Name = 'URL';
