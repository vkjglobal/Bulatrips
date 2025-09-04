-- Manual Refunds Management Tables

-- Main manual refunds table
CREATE TABLE IF NOT EXISTS `manual_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mf_reference` varchar(50) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `refund_reason` text NOT NULL,
  `status` enum('pending','processing','processed','failed') NOT NULL DEFAULT 'pending',
  `admin_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Refund notes table for tracking admin actions
CREATE TABLE IF NOT EXISTS `refund_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refund_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `refund_id` (`refund_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Refund status history table
CREATE TABLE IF NOT EXISTS `refund_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refund_id` int(11) NOT NULL,
  `old_status` enum('pending','processing','processed','failed') NOT NULL,
  `new_status` enum('pending','processing','processed','failed') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `refund_id` (`refund_id`),
  KEY `changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing
INSERT INTO `manual_refunds` (`booking_id`, `user_id`, `mf_reference`, `refund_amount`, `currency`, `refund_reason`, `status`, `admin_notes`) VALUES
(148, 1, 'MF148123', 559.02, 'USD', 'Void window expired - manual refund required', 'pending', 'Customer requested refund due to schedule change'),
(149, 2, 'MF149456', 125.50, 'USD', 'API failure - manual processing needed', 'processing', 'Contacting airline for refund confirmation'),
(150, 3, 'MF150789', 89.99, 'USD', 'Non-refundable fare - partial refund possible', 'processed', 'Refund processed successfully');

-- Sample refund notes
INSERT INTO `refund_notes` (`refund_id`, `note`, `created_by`) VALUES
(1, 'Contacted airline - refund approved', 1),
(1, 'Processing refund to original payment method', 1),
(2, 'Waiting for airline confirmation', 1),
(3, 'Refund completed - customer notified', 1);

-- Sample status history
INSERT INTO `refund_status_history` (`refund_id`, `old_status`, `new_status`, `changed_by`, `notes`) VALUES
(1, 'pending', 'processing', 1, 'Started processing refund'),
(2, 'pending', 'processing', 1, 'Contacting airline'),
(3, 'processing', 'processed', 1, 'Refund completed successfully'); 