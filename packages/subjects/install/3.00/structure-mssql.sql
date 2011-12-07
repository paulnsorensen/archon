-- Add Index to ParentID on Subjects table
CREATE INDEX ParentID ON tblSubjects_Subjects(ParentID);

-- Add Description Field to Subjects table
ALTER TABLE tblSubjects_Subjects ADD Description TEXT NULL;
