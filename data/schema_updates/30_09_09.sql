-- 
-- Database: `crm2`
-- 

-- changes for free guarantee period
ALTER TABLE `project` 
	ADD COLUMN `startfgp` DATETIME AFTER `ismilestone`,
 	ADD COLUMN `durationfgp` FLOAT AFTER `startfgp`;

