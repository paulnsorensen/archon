-- Change Title in tblAccessions_Accessions to allow NULL
ALTER TABLE  tblAccessions_Accessions CHANGE  Title  Title VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
