SET SQL_SAFE_UPDATES=0$
CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock` (
  `Stock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ispublished` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `asset_id` varchar(255) NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `staff_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`Stock_id`),
  UNIQUE KEY `asset_id_UNIQUE` (`asset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock_category` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ispublic` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(125) DEFAULT NULL,
  `description` text NOT NULL,
  `notes` tinytext NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `ispublic` (`ispublic`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock_status` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) DEFAULT NULL,
  `description` text NOT NULL,
  `image` text,
  `color` varchar(45) DEFAULT NULL,
  `baseline` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock_ticket` (
  `Stock_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`Stock_id`,`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock_ticket_recurring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Stock_id` int(11) NOT NULL,
  `last_opened` datetime DEFAULT NULL,
  `next_date` datetime DEFAULT NULL,
  `interval` double NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8$

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%Stock_config` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`key` varchar(255) NOT NULL DEFAULT 'undefined',
`value` varchar(255) NOT NULL DEFAULT 'undefined',
PRIMARY KEY (`id`),
UNIQUE KEY `key_UNIQUE` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8$

REPLACE INTO `%TABLE_PREFIX%Stock_config` (`key`, `value`)
VALUES ('recurrance_enabled','false')$ 

DELETE FROM `%TABLE_PREFIX%list` WHERE `name`='Stock_status'$ 
INSERT INTO `%TABLE_PREFIX%list` (`name`, `created`,`notes`,`updated`)
VALUES ('Stock_status',NOW(),'internal Stock plugin list, do not modify',NOW())$ 

DELETE FROM `%TABLE_PREFIX%list` WHERE `name`='Stock'$ 
INSERT INTO `%TABLE_PREFIX%list` (`name`, `created`,`notes`,`updated`)
VALUES ('Stock',NOW(),'internal Stock plugin list, do not modify',NOW())$ 

DELETE FROM `%TABLE_PREFIX%form` WHERE `title`='Stock'$
INSERT INTO `%TABLE_PREFIX%form` (`type`, `deletable`,`title`, `notes`, `created`, `updated`)
VALUES ('G',0,'Stock','Stock internal form',NOW(),NOW())$ 

DROP PROCEDURE IF EXISTS `%TABLE_PREFIX%CreateStockFormFields`$

CREATE PROCEDURE `%TABLE_PREFIX%CreateStockFormFields`()
BEGIN
	SET @form_id = (SELECT id FROM `%TABLE_PREFIX%form` WHERE title='Stock');
	SET @status_list_id = (SELECT id FROM `%TABLE_PREFIX%list` WHERE `name`='Stock_status');
	SET @Stock_list_id = (SELECT id FROM `%TABLE_PREFIX%list` WHERE `name`='Stock');

	IF (@form_id IS NOT NULL) AND (@status_list_id IS NOT NULL) AND (@Stock_list_id IS NOT NULL) then
		INSERT INTO `%TABLE_PREFIX%form_field`
			(`form_id`,
			`type`,
			`label`,
			`required`,
			`private`,
			`edit_mask`,
			`name`,
			`sort`,
			`created`,
			`updated`)
			VALUES
			(@form_id,
			CONCAT('list-',@Stock_list_id),
			'Stock',
			0,0,0,
			'Stock',			
			3,			
			NOW(),
			NOW());	

		INSERT INTO `%TABLE_PREFIX%form_field`
			(`form_id`,
			`type`,
			`label`,
			`required`,
			`private`,
			`edit_mask`,
			`name`,
			`sort`,
			`created`,
			`updated`)
			VALUES
			(@form_id,
			CONCAT('list-',@status_list_id),
			'Status',
			0,0,0,
			'status',			
			2,			
			NOW(),
			NOW());	

                INSERT INTO `%TABLE_PREFIX%form_field`
			(`form_id`,
			`type`,
			`label`,
			`required`,
			`private`,
			`edit_mask`,
			`name`,
			`sort`,
			`created`,
			`updated`)
			VALUES
			(@form_id,
			('text'),
			'Asset ID',
			0,0,0,
			'asset_id',			
			1,			
			NOW(),
			NOW());							
	END IF;
END$

CALL `%TABLE_PREFIX%CreateStockFormFields`()$


DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_ADEL`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_ADEL` AFTER DELETE ON `%TABLE_PREFIX%Stock` FOR EACH ROW
BEGIN
	SET @pk=OLD.Stock_id;
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock');
	DELETE FROM `%TABLE_PREFIX%list_items` WHERE list_id = @list_pk AND properties=CONCAT('',@pk); 
END$


DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_AINS`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_AINS` AFTER INSERT ON `%TABLE_PREFIX%Stock` FOR EACH ROW
BEGIN
	SET @pk=NEW.Stock_id;
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock');
	INSERT INTO `%TABLE_PREFIX%list_items` (list_id, `value`, `properties`)
	VALUES (@list_pk, CONCAT(' Asset_ID:', NEW.asset_id), CONCAT('',@pk));  
END$


DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_AUPD`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_AUPD` AFTER UPDATE ON `%TABLE_PREFIX%Stock` FOR EACH ROW
BEGIN
	SET @pk=NEW.Stock_id;
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock');

	IF NEW.is_active = 0 THEN
		DELETE FROM `%TABLE_PREFIX%list_items` WHERE list_id = @list_pk AND properties=CONCAT('',@pk);
	ELSE
		SET @list_item_pkid = (SELECT id 
							   FROM `%TABLE_PREFIX%list_items`
							   WHERE list_id = @list_pk AND properties=CONCAT('',@pk));

		IF (@list_item_pkid IS NOT NULL) AND (@list_item_pkid>0) THEN
			UPDATE `%TABLE_PREFIX%list_items` SET `value`= CONCAT(' Asset_ID:', NEW.asset_id) 
			WHERE `properties`= CONCAT('',@pk) AND list_id=@list_pk;
		ELSE
			INSERT INTO `%TABLE_PREFIX%list_items` (list_id, `value`, `properties`)
			VALUES (@list_pk, CONCAT(' Asset_ID:', NEW.asset_id), CONCAT('',@pk));			
		END IF;
	END IF; 
END$

DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_status_AINS`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_status_AINS` AFTER INSERT ON `%TABLE_PREFIX%Stock_status` FOR EACH ROW
BEGIN
	SET @pk=NEW.status_id;
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock_status');
	INSERT INTO `%TABLE_PREFIX%list_items` (list_id, `value`, `properties`) 
	VALUES (@list_pk, NEW.name, CONCAT('',@pk));
END$

DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_status_AUPD`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_status_AUPD` AFTER UPDATE ON `%TABLE_PREFIX%Stock_status` FOR EACH ROW
BEGIN
	SET @pk=NEW.status_id;
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock_status'); 
	UPDATE `%TABLE_PREFIX%list_items` SET `value`= NEW.name WHERE `properties`= CONCAT('',@pk) AND list_id=@list_pk;
END$

DROP TRIGGER IF EXISTS `%TABLE_PREFIX%Stock_status_ADEL`$

CREATE TRIGGER `%TABLE_PREFIX%Stock_status_ADEL` AFTER DELETE ON `%TABLE_PREFIX%Stock_status` FOR EACH ROW
BEGIN
	SET @pk=OLD.status_id; 
	SET @list_pk=(SELECT id FROM `%TABLE_PREFIX%list` WHERE name='Stock_status');
	DELETE FROM `%TABLE_PREFIX%list_items` WHERE list_id = @list_pk AND properties=CONCAT('',@pk);
END$

DROP VIEW IF EXISTS `%TABLE_PREFIX%StockFormView`$

CREATE VIEW `%TABLE_PREFIX%StockFormView` AS 
select `%TABLE_PREFIX%form`.`title` AS `title`,
`%TABLE_PREFIX%form_entry`.`id` AS `entry_id`,
`%TABLE_PREFIX%form_entry`.`object_id` AS `ticket_id`,
`%TABLE_PREFIX%form_field`.`id` AS `field_id`,
`%TABLE_PREFIX%form_field`.`label` AS `field_label`,
`%TABLE_PREFIX%form_entry_values`.`value` AS `value`,
`%TABLE_PREFIX%Stock_status`.`status_id` AS `status_id` 
from ((((`%TABLE_PREFIX%form_field` 
left join 
(`%TABLE_PREFIX%form_entry_values` join `%TABLE_PREFIX%form_entry`) 
on(((`%TABLE_PREFIX%form_field`.`id` = `%TABLE_PREFIX%form_entry_values`.`field_id`) and 
(`%TABLE_PREFIX%form_entry`.`id` = `%TABLE_PREFIX%form_entry_values`.`entry_id`)))) 
left join `%TABLE_PREFIX%form` 
on((`%TABLE_PREFIX%form`.`id` = `%TABLE_PREFIX%form_field`.`form_id`))) 
left join `%TABLE_PREFIX%Stock_status` 
on((`%TABLE_PREFIX%form_entry_values`.`value` like concat('%', `%TABLE_PREFIX%Stock_status`.`name`, '%'))))) 
where ((`%TABLE_PREFIX%form`.`title` = 'Stock') and 
(`%TABLE_PREFIX%form`.`id` = `%TABLE_PREFIX%form_entry`.`form_id`) and 
(`%TABLE_PREFIX%form_entry`.`object_type` = 'T'))$

DROP VIEW IF EXISTS `%TABLE_PREFIX%StockTicketView`$

CREATE VIEW `%TABLE_PREFIX%StockTicketView` AS 
select `%TABLE_PREFIX%Stock_ticket`.`Stock_id` AS `Stock_id`,
`%TABLE_PREFIX%Stock_ticket`.`ticket_id` AS `ticket_id`,
`%TABLE_PREFIX%Stock_ticket`.`created` AS `created`,
`%TABLE_PREFIX%Stock`.`category_id` AS `category_id`,
`%TABLE_PREFIX%Stock`.`is_active` AS `is_active`,
`%TABLE_PREFIX%ticket_status`.`state` AS `status` 
from `ost_Stock_ticket` 
left join `ost_Stock` 
on(`ost_Stock_ticket`.`Stock_id` = `ost_Stock`.`Stock_id`)
left join `ost_ticket` 
on(`ost_Stock_ticket`.`ticket_id` = `ost_ticket`.`ticket_id`)
left join `ost_ticket_status` 
on(`ost_ticket`.`status_id` = `ost_ticket_status`.`id`)$

DROP VIEW IF EXISTS `%TABLE_PREFIX%StockSearchView`$

CREATE VIEW `%TABLE_PREFIX%StockSearchView` AS 
select `eq`.`Stock_id` AS `Stock_id`,`eq`.`asset_id` AS `asset_id`,`fev`.`value` AS `value` 
from
`%TABLE_PREFIX%form_entry` `fe` 
left join `%TABLE_PREFIX%form_entry_values` `fev` ON (`fe`.`id` = `fev`.`entry_id` AND     
(`fe`.`object_type` = 'E'))
right join `%TABLE_PREFIX%Stock` `eq` ON (`eq`.`Stock_id` = `fe`.`object_id`)$

DROP TRIGGER IF EXISTS `%TABLE_PREFIX%ticket_event_AINS`$

CREATE TRIGGER `%TABLE_PREFIX%ticket_event_AINS` AFTER INSERT ON `%TABLE_PREFIX%ticket_event` FOR EACH ROW
BEGIN
	IF NEW.state='closed' THEN
               
		SET @Stock_id = (SELECT Stock_id FROM `%TABLE_PREFIX%Stock_ticket`
							WHERE ticket_id=NEW.ticket_id LIMIT 1);            


		IF ((@Stock_id IS NOT NULL) AND (@Stock_id>0)) THEN

                                            SET @open_ticks = (SELECT COUNT(ticket_id) FROM `%TABLE_PREFIX%StockTicketView`
                                                            WHERE Stock_id = @Stock_id  AND
								`status` != 'closed');

                        IF @open_ticks = 0 THEN
                            SET @status_id = (SELECT status_id FROM `%TABLE_PREFIX%Stock_status`
                                                            WHERE baseline=1 LIMIT 1);

                            IF ((@status_id IS NOT NULL) AND (@status_id>0)) THEN
                                    UPDATE `%TABLE_PREFIX%Stock` SET status_id = @status_id
                                    WHERE Stock_id = @Stock_id; 
                            END IF;
			END IF;
		END IF;

	ELSEIF NEW.state='created' THEN
		
		SET @status_id = (SELECT status_id FROM `%TABLE_PREFIX%StockFormView` WHERE 
						ticket_id= NEW.ticket_id AND field_label='Status' LIMIT 1);
                
                SET @asset_id = (SELECT value FROM `%TABLE_PREFIX%StockFormView` WHERE 
							ticket_id= NEW.ticket_id AND field_label='Asset ID' LIMIT 1);
		IF( @asset_id IS NULL) THEN
			SET @asset_id_str = (SELECT value FROM `%TABLE_PREFIX%StockFormView` WHERE 
							ticket_id= NEW.ticket_id AND field_label='Stock' LIMIT 1);
			SET @asset_id = (SELECT SUBSTRING_INDEX(@asset_id_str, 'Asset_ID:', -1));
                        SET @asset_id = SUBSTRING(@asset_id, 1, CHAR_LENGTH(@asset_id) - 2);
		END IF;

		
		SET @Stock_id = (SELECT Stock_id FROM `%TABLE_PREFIX%Stock` WHERE 
							asset_id= @asset_id);	

		IF ((@status_id IS NOT NULL) AND 
			(@status_id >0)) AND 
			((@Stock_id IS NOT NULL) AND 
			(@Stock_id >0)) THEN						
				
				UPDATE `%TABLE_PREFIX%Stock` SET status_id = @status_id WHERE Stock_id=@Stock_id;
				INSERT INTO `%TABLE_PREFIX%Stock_ticket` (Stock_id, ticket_id, created) 
				VALUES (@Stock_id, NEW.ticket_id, NOW());
		END IF;
	
	END IF;
END$	

DROP TRIGGER IF EXISTS `%TABLE_PREFIX%ticket_event_AUPD`$

CREATE TRIGGER `%TABLE_PREFIX%ticket_event_AUPD` AFTER UPDATE ON `%TABLE_PREFIX%ticket_event` FOR EACH ROW
BEGIN
		SET @status_id = (SELECT status_id FROM `%TABLE_PREFIX%StockFormView` WHERE 
						ticket_id= NEW.ticket_id AND field_label='Status' LIMIT 1);
                
                SET @asset_id = (SELECT value FROM `%TABLE_PREFIX%StockFormView` WHERE 
							ticket_id= NEW.ticket_id AND field_label='Asset ID' LIMIT 1);
		IF( @asset_id IS NULL) THEN
			SET @asset_id_str = (SELECT value FROM `%TABLE_PREFIX%StockFormView` WHERE 
							ticket_id= NEW.ticket_id AND field_label='Stock' LIMIT 1);
			SET @asset_id = (SELECT SUBSTRING_INDEX(@asset_id_str, 'Asset_ID:', -1));
                        SET @asset_id = SUBSTRING(@asset_id, 1, CHAR_LENGTH(@asset_id) - 2);
		END IF;

		
		SET @Stock_id = (SELECT Stock_id FROM `%TABLE_PREFIX%Stock` WHERE 
							asset_id= @asset_id);	

		IF ((@status_id IS NOT NULL) AND 
			(@status_id >0)) AND 
			((@Stock_id IS NOT NULL) AND 
			(@Stock_id >0)) THEN						
				
				UPDATE `%TABLE_PREFIX%Stock` SET status_id = @status_id WHERE Stock_id=@Stock_id;
		END IF;
	
	
END$	

DROP PROCEDURE IF EXISTS `%TABLE_PREFIX%Stock_Copy_Form_Entry`$
CREATE PROCEDURE `%TABLE_PREFIX%Stock_Copy_Form_Entry`(p_ticket_id INT, p_new_ticket_id INT)
BEGIN
	DECLARE n INT DEFAULT 0;
	DECLARE i INT DEFAULT 0;
	DECLARE l_id INT;
	DECLARE l_new_id INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_table2;
	CREATE TEMPORARY TABLE tmp_table2 
        SELECT * 
        FROM %TABLE_PREFIX%form_entry 
        WHERE object_id = p_ticket_id AND `object_type` = 'T';

	SET SQL_SAFE_UPDATES=0;

	ALTER TABLE tmp_table2 modify column id int;

	SELECT COUNT(*) FROM tmp_table2 INTO n;
	SET i = 0;

	WHILE i<n DO
		SELECT id INTO l_id FROM tmp_table2 LIMIT i,1;	
		UPDATE tmp_table2 set object_id=p_new_ticket_id, created=NOW(), updated=NOW() WHERE id=l_id;

		INSERT INTO %TABLE_PREFIX%form_entry 
		(SELECT NULL, form_id, object_id, object_type, sort, created, updated 
		 FROM tmp_table2 WHERE id=l_id); 

		SELECT LAST_INSERT_ID() INTO l_new_id;

		DROP TEMPORARY TABLE IF EXISTS tmp_table3;
		CREATE TEMPORARY TABLE tmp_table3 

		SELECT * FROM %TABLE_PREFIX%form_entry_values 
		WHERE entry_id = l_id;

		ALTER TABLE tmp_table3 modify column entry_id int;
		UPDATE tmp_table3 SET entry_id = l_new_id;
		INSERT INTO %TABLE_PREFIX%form_entry_values SELECT * FROM tmp_table3;		
		DROP TEMPORARY TABLE IF EXISTS tmp_table3;

		SET i = i + 1;
	END WHILE;
	
	SET SQL_SAFE_UPDATES=1;
	DROP TEMPORARY TABLE IF EXISTS tmp_table2;
END$


DROP PROCEDURE IF EXISTS `%TABLE_PREFIX%Stock_Reopen_Ticket`$
CREATE PROCEDURE `%TABLE_PREFIX%Stock_Reopen_Ticket`(p_etr_id INT)
BEGIN
	DECLARE l_ticket_id INT;
	DECLARE l_new_ticket_id INT;
	DECLARE l_Stock_id INT;
	DECLARE l_ticket_number INT;
	DECLARE l_loop_flag boolean;
	DECLARE l_tmp INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_table1;

	SELECT Stock_id, ticket_id 
	INTO l_Stock_id, l_ticket_id 
	FROM %TABLE_PREFIX%Stock_ticket_recurring 
	WHERE id=p_etr_id;
	
	IF l_ticket_id IS NOT NULL THEN	
		SET l_loop_flag = FALSE;
		WHILE l_loop_flag = FALSE DO
			SET l_tmp = NULL;
			SET l_ticket_number = FLOOR(RAND()*900000)+100000;
			SELECT ticket_id INTO l_tmp FROM %TABLE_PREFIX%ticket WHERE `number` = l_ticket_number;
			IF l_tmp IS NULL THEN
				SET l_loop_flag = TRUE;
			END IF;
		END WHILE;		

		CREATE TEMPORARY TABLE tmp_table1 
                SELECT * 
                FROM %TABLE_PREFIX%ticket 
                WHERE ticket_id = l_ticket_id;

		SET SQL_SAFE_UPDATES=0;
		ALTER TABLE tmp_table1 modify column ticket_id int;
		UPDATE tmp_table1 
		SET `number` = l_ticket_number, 
			ticket_id = NULL, 
			`status` = 'open', 
			closed = NULL, 
			created = NOW(), 
			updated = NOW(),
			isanswered = 0,
			lastmessage = NOW(),
			lastresponse = NULL;
		SET SQL_SAFE_UPDATES=1;		

		INSERT INTO %TABLE_PREFIX%ticket SELECT * FROM tmp_table1;
		DROP TEMPORARY TABLE IF EXISTS tmp_table1;

		SELECT ticket_id INTO l_new_ticket_id FROM %TABLE_PREFIX%ticket WHERE `number` = l_ticket_number;
		IF l_new_ticket_id IS NOT NULL THEN	
			CREATE TEMPORARY TABLE tmp_table1 SELECT * FROM %TABLE_PREFIX%ticket__cdata WHERE ticket_id = l_ticket_id;
			SET SQL_SAFE_UPDATES=0;
			ALTER TABLE tmp_table1 modify column ticket_id int;
			UPDATE tmp_table1 SET ticket_id = l_new_ticket_id;
			SET SQL_SAFE_UPDATES=1;	

			INSERT INTO %TABLE_PREFIX%ticket__cdata SELECT * FROM tmp_table1;
			DROP TEMPORARY TABLE IF EXISTS tmp_table1;

			CALL %TABLE_PREFIX%Stock_Copy_Form_Entry(l_ticket_id, l_new_ticket_id);

			CREATE TEMPORARY TABLE tmp_table1 SELECT * FROM %TABLE_PREFIX%ticket_event 
			WHERE ticket_id = l_ticket_id
			AND `state`='created';
			SET SQL_SAFE_UPDATES=0;
			UPDATE tmp_table1 SET ticket_id=l_new_ticket_id, `timestamp` = NOW();
			INSERT INTO %TABLE_PREFIX%ticket_event SELECT * FROM tmp_table1;
			SET SQL_SAFE_UPDATES=1;	
			DROP TEMPORARY TABLE IF EXISTS tmp_table1;
			
			
		END IF;
	END IF;
END$

DROP PROCEDURE IF EXISTS `%TABLE_PREFIX%StockCronProc`$
CREATE PROCEDURE `%TABLE_PREFIX%StockCronProc`()
BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE l_id INT;
	DECLARE l_next_date datetime;
	DECLARE l_interval double;
	DECLARE cur1 CURSOR FOR (SELECT id, `interval`, next_date
	FROM %TABLE_PREFIX%Stock_ticket_recurring
	WHERE active=1);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		

	OPEN cur1;

	read_loop: LOOP
		FETCH cur1 INTO l_id, l_interval, l_next_date;
		IF done then
			LEAVE read_loop;
		END IF;

		IF l_next_date <= NOW() 	THEN
			SET l_next_date = DATE_ADD(NOW(), INTERVAL l_interval SECOND);
			CALL %TABLE_PREFIX%Stock_Reopen_Ticket(l_id);
			UPDATE %TABLE_PREFIX%Stock_ticket_recurring 
                        SET next_date=l_next_date, last_opened=NOW() WHERE id=l_id;
		END IF;
	END LOOP;

	CLOSE cur1;
END$

UPDATE `%TABLE_PREFIX%plugin` SET version = '0.4' WHERE `name`='Stock Manager'$
SET SQL_SAFE_UPDATES=1$	