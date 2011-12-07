
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


DROP TABLE IF EXISTS CIDs;

CREATE TABLE CIDs (cid INT NOT NULL);
INSERT CIDs (SELECT DISTINCT CollectionID AS cid FROM tblCollections_Content);
ALTER TABLE CIDs ADD INDEX (cid);


CREATE TABLE currCID (cid INT NOT NULL);

DELIMITER //
CREATE PROCEDURE buildnestedset()

BEGIN
  SET @done = 0;

collections: REPEAT

  SET @x = (SELECT COUNT(*) FROM CIDs);
  IF @x = 0 THEN SET @done = 1;
  END IF;

IF @done > 0 THEN LEAVE collections;
END IF;

DROP TABLE IF EXISTS Stack;

CREATE TABLE Stack
(stack_top INT NOT NULL,
 ID INT NOT NULL,
 lft INT NOT NULL,
 rgt INT NULL);

ALTER TABLE Stack ADD INDEX (stack_top);
ALTER TABLE Stack ADD INDEX (ID);
ALTER TABLE Stack ADD INDEX (lft);
ALTER TABLE Stack ADD INDEX (rgt);


INSERT currCID (SELECT MIN(cid) FROM CIDs);

SET @max_lft_rgt = 2 * (SELECT COUNT(ID) FROM tblCollections_Content WHERE CollectionID = (SELECT MIN(cid) FROM currCID)) + 2;

INSERT INTO Stack (stack_top, ID, lft, rgt) VALUES (1, 0, 1, @max_lft_rgt);

SET @lft_rgt = 2;
SET @Stack_pointer = 1;


WHILE @lft_rgt < @max_lft_rgt DO
BEGIN
 SET @c = (SELECT COUNT(*) FROM Stack AS S1, tblCollections_Content AS T1 WHERE S1.ID = T1.ParentID AND S1.stack_top = @stack_pointer AND T1.CollectionID = (SELECT MIN(cid) FROM currCID) AND T1.Lft != -1);
 IF @c > 0 THEN

      INSERT INTO Stack
      SELECT (@stack_pointer + 1), T1.ID, @lft_rgt, NULL
        FROM Stack AS S1, tblCollections_Content AS T1
       WHERE S1.ID = T1.ParentID
         AND S1.stack_top = @stack_pointer
         AND T1.CollectionID = (SELECT MIN(cid) FROM currCID)
         AND T1.Lft != -1
         ORDER BY T1.SortOrder
         LIMIT 1;
     
      UPDATE tblCollections_Content SET Lft = -1, Rgt = -1
         WHERE ID = (SELECT ID FROM Stack WHERE stack_top = @stack_pointer + 1);

      SET @stack_pointer = @stack_pointer + 1;
    
    ELSE
    BEGIN  
      UPDATE Stack
         SET rgt = @lft_rgt,
             stack_top = -stack_top
       WHERE stack_top = @stack_pointer;
      SET @stack_pointer = @stack_pointer - 1;
    END; -- pop
    END IF;
  SET @lft_rgt = @lft_rgt + 1;
END;
  END WHILE;

  UPDATE Stack SET lft = lft - 1, rgt = rgt -1;
  UPDATE tblCollections_Content AS T1, Stack AS S1 SET T1.Lft = S1.lft, T1.Rgt = S1.rgt WHERE T1.ID = S1.ID;

  DELETE FROM CIDs WHERE cid = (SELECT MIN(cid) FROM currCID);
  DELETE FROM currCID WHERE 1 = 1;

  UNTIL @done END REPEAT collections;

END//
DELIMITER ;

CALL buildnestedset(14334);

DROP TABLE IF EXISTS Stack;
DROP TABLE IF EXISTS CIDs;
DROP TABLE IF EXISTS currCID;


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