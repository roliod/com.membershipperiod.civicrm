-- install sql for mPRD extension, create a table to hold membership period

CREATE TABLE `civicrm_mprd_membership_period` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Membership period Id',
  `period` int NOT NULL COMMENT 'Period of Membership',
  `start_date` date DEFAULT NULL COMMENT 'Membership period start date',
  `end_date` date DEFAULT NULL COMMENT 'Membership period end date',
  `contribution_id` int DEFAULT 0 COMMENT 'Contribution id if a payment is received',
  `contact_id` int DEFAULT 0 COMMENT 'Contact id',
  PRIMARY KEY ( `id` ),
  KEY (`contribution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table to store membership period';


-- update membership table for mPRD extension, add membership period id

ALTER TABLE `civicrm_membership` ADD membership_period_id INT DEFAULT 0;
