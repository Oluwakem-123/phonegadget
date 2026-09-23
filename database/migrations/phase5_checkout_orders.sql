-- Phase 5: Checkout and Order Management
-- Convert required tables to InnoDB to support transactions (Strict requirement for Phase 5)

ALTER TABLE `users` ENGINE=InnoDB;
ALTER TABLE `phones` ENGINE=InnoDB;
ALTER TABLE `cart_items` ENGINE=InnoDB;
ALTER TABLE `orders` ENGINE=InnoDB;
ALTER TABLE `order_items` ENGINE=InnoDB;
ALTER TABLE `favorites` ENGINE=InnoDB;

-- Add delivery snapshot columns to orders safely (Only if they do not exist)
-- Since we are running this manually, we will just add them. In a real system, we'd check if they exist first.
-- However, we can use a safe stored procedure to add columns if they don't exist.

DELIMITER $$
CREATE PROCEDURE AddDeliveryColumnsToOrders()
BEGIN
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'customer_name') THEN
        ALTER TABLE `orders` 
        ADD COLUMN `customer_name` varchar(100) DEFAULT NULL AFTER `user_id`,
        ADD COLUMN `customer_phone` varchar(20) DEFAULT NULL AFTER `customer_name`,
        ADD COLUMN `delivery_address` text DEFAULT NULL AFTER `customer_phone`,
        ADD COLUMN `delivery_city` varchar(100) DEFAULT NULL AFTER `delivery_address`,
        ADD COLUMN `delivery_state` varchar(100) DEFAULT NULL AFTER `delivery_city`;
    END IF;
END $$
DELIMITER ;

CALL AddDeliveryColumnsToOrders();
DROP PROCEDURE AddDeliveryColumnsToOrders;

-- Ensure foreign keys exist safely
DELIMITER $$
CREATE PROCEDURE AddOrderForeignKeys()
BEGIN
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'fk_orders_user') THEN
        ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;
    END IF;
    
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'fk_order_items_order') THEN
        ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
    END IF;
    
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'fk_order_items_phone') THEN
        ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_phone` FOREIGN KEY (`phone_id`) REFERENCES `phones`(`id`) ON DELETE RESTRICT;
    END IF;
END $$
DELIMITER ;

CALL AddOrderForeignKeys();
DROP PROCEDURE AddOrderForeignKeys;
