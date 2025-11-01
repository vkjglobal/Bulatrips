-- Add new JSON column to travellers_details table for storing complete passenger response data
ALTER TABLE `travellers_details` 
ADD COLUMN `mystifly_response_json` JSON DEFAULT NULL COMMENT 'Complete passenger data from Mystifly TripDetails API response';

-- Add last_updated column to track when data was refreshed
ALTER TABLE `travellers_details` 
ADD COLUMN `last_api_update` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last time data was updated from Mystifly API';

-- Add index for last_api_update column (JSON columns cannot be directly indexed)
ALTER TABLE `travellers_details` 
ADD KEY `idx_last_api_update` (`last_api_update`);
