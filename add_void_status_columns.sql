-- Add void status tracking columns to travellers_details table
ALTER TABLE travellers_details 
ADD COLUMN void_status VARCHAR(50) DEFAULT NULL COMMENT 'Void status: InProcess, Completed, Failed, NULL',
ADD COLUMN ptr_id VARCHAR(50) DEFAULT NULL COMMENT 'PTR ID from Mystifly for void requests';

-- Add index for better performance
CREATE INDEX idx_void_status ON travellers_details(void_status);
CREATE INDEX idx_ptr_id ON travellers_details(ptr_id); 