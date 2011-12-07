 -- Tree holds the adjacency model
--CREATE TABLE Tree
--(emp CHAR(10) NOT NULL,
-- boss CHAR(10));
--
-- -- insert the sample data for testing
--INSERT INTO Tree VALUES ('Albert', NULL);
--INSERT INTO Tree VALUES ('Bert', 'Albert');
--INSERT INTO Tree VALUES ('Chuck', 'Albert');
--INSERT INTO Tree VALUES ('Donna', 'Chuck');
--INSERT INTO Tree VALUES ('Eddie', 'Chuck');
--INSERT INTO Tree VALUES ('Fred', 'Chuck');
--
---- Stack starts empty, will holds the nested set model
--CREATE TABLE Stack
--(stack_top INTEGER NOT NULL,
-- emp CHAR(10) NOT NULL,
-- lft INTEGER,
-- rgt INTEGER);

--Each row of the stack holds the nested set (lft, rgt) pair, the node value (emp) and an integer that represents the current top of the stack as an integer. When the stack_top is positive, something has been pushed onto the stack. When the stack_top is negative, it has been popped off the stack.
--
--The algorithm is pretty straight forward, though there are some tricks about representing a stack in T-SQL. Here is what we know:
--
--We will do (2 * (SELECT COUNT(*) FROM Tree)) operation to build the (lft, rgt) pairs for each node. We need a general counter for this.
--When a node is pushed on the stack, we give it a lft number and increment the counter.
--When a node is popped from the stack, we give it a rgt number and increment the counter.
--We start at the root. Each nodes goes on and off the stack once and only once.
--We look at the top of the stack and push the youngest subordinate of that node onto the stack
--When the node on the top of stack is a leaf node or a node without "un-popped" subordinates back in the tree, pop it off the stack.
--Here is the code in T-SQL:

--DROP TABLE CIDs;
--
--CREATE TABLE CIDs (cid INT NOT NULL);
--INSERT CIDs (SELECT DISTINCT CollectionID AS cid FROM tblCollections_Content);
--CREATE INDEX cid ON CIDs(cid);

-- CREATE TABLE currCID (cid INT NOT NULL);


CREATE PROCEDURE buildnested AS


-- you can create optional indexes on stack_top and child columns

FOR Coll IN (SELECT DISTINCT CollectionID AS cid FROM tblCollections_Content) LOOP

BEGIN

DECLARE @lft_rgt INT, @stack_pointer INT, @max_lft_rgt INT;

DROP TABLE Stack;

CREATE TABLE Stack
(stack_top INT NOT NULL,
 ID INT NOT NULL,
 lft INT NOT NULL,
 rgt INT);

CREATE INDEX stack_top ON Stack(stack_top);
CREATE INDEX ID ON Stack(ID);
CREATE INDEX lft ON Stack(lft);
CREATE INDEX rgt ON Stack(rgt);


SET @max_lft_rgt = 2 * (SELECT COUNT(ID) FROM tblCollections_Content WHERE CollectionID = Coll.cid) + 2;

INSERT INTO Stack (stack_top, ID, lft, rgt) VALUES (1, 0, 1, @max_lft_rgt);

SET @lft_rgt = 2;
SET @Stack_pointer = 1;

WHILE (@lft_rgt < @max_lft_rgt)
BEGIN
 IF EXISTS (SELECT *
              FROM Stack AS S1, tblCollections_Content AS T1
             WHERE S1.ID = T1.ParentID
               AND S1.stack_top = @stack_pointer
               AND T1.CollectionID = Coll.cid
               AND T1.Lft != -1)
    BEGIN -- push when stack_top has subordinates and set lft value
      INSERT INTO Stack
      SELECT (@stack_pointer + 1), T1.ID, @lft_rgt, NULL
        FROM Stack AS S1, tblCollections_Content AS T1
       WHERE S1.ID = T1.ParentID
         AND S1.stack_top = @stack_pointer
         AND T1.CollectionID = Coll.cid
         AND T1.Lft != -1
         ORDER BY T1.SortOrder
         LIMIT 1;

      UPDATE tblCollections_Content SET Lft = -1, Rgt = -1
         WHERE ID = (SELECT ID FROM Stack WHERE stack_top = @stack_pointer + 1);

      SET @stack_pointer = @stack_pointer + 1;
    END -- push
    ELSE
    BEGIN  -- pop the Stack and set rgt value
      UPDATE Stack
         SET rgt = @lft_rgt,
             stack_top = -stack_top
       WHERE stack_top = @stack_pointer
      SET @stack_pointer = @stack_pointer - 1;
    END; -- pop
  SET @lft_rgt = @lft_rgt + 1;
  END; -- if
END; -- while


UPDATE Stack SET lft = lft - 1, rgt = rgt -1;
  UPDATE tblCollections_Content AS T1, Stack AS S1 SET T1.Lft = S1.lft, T1.Rgt = S1.rgt WHERE T1.ID = S1.ID;

END LOOP;
DROP TABLE Stack;

GO



EXECUTE buildnestedset;

DROP PROCEDURE buildnestedset;

SELECT * FROM Stack ORDER BY lft;


-- Stack
-- stack_top   emp      lft  rgt
-- -----------------------------
--  -1         Albert   1     12
--  -2         Bert     2      3
--  -2         Chuck    4     11
--  -3         Donna    5      6
--  -3         Eddie    7      8
--  -3         Fred     9     10