-- Add TemplateSet to Repository table
ALTER TABLE tblCore_Repositories ADD TemplateSet VARCHAR( 50 ) NULL DEFAULT NULL;

--Add ResearchFunctionality to Repository table
ALTER TABLE tblCore_Repositories ADD ResearchFunctionality INT NOT NULL DEFAULT '0';

CREATE TABLE tblCore_VersionCache (
   VersionName varchar(50) NOT NULL DEFAULT '' ,
   VersionNumber varchar(10) NULL DEFAULT NULL,
   LastUpdated DATETIME NOT NULL
);
