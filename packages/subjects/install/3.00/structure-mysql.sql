-- Add Index to ParentID on Subjects table
ALTER TABLE tblSubjects_Subjects ADD INDEX ( ParentID );

-- Add Description Field to Subjects table
ALTER TABLE tblSubjects_Subjects ADD Description TEXT NULL;
