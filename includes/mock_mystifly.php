<?php
/**
 * Mock Mystifly API Responses
 * This class provides realistic mock responses for development/testing
 * All responses match the exact structure from Mystifly API documentation
 */
class MockMystifly {
    
    /**
     * Generate VoidQuote Response
     */
    public static function getVoidQuoteResponse($passengers) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'TotalRefundAmount' => 300,
                'Currency' => 'USD',
                'AdminCharges' => 0,
                'GSTCharge' => 0,
                'TotalVoidingFee' => 0,
                'SLAInMinutes' => 0,
                'Message' => 'Void quote request processed successfully'
            ],
            'Message' => 'Void quote request processed successfully'
        ];
    }
    
    /**
     * Generate Void Response (actual void operation)
     */
    public static function getVoidResponse($ptrId) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'Message' => 'Void request accepted and is being processed'
            ],
            'Message' => 'Void request accepted and is being processed'
        ];
    }
    
    /**
     * Generate RefundQuote Response
     */
    public static function getRefundQuoteResponse($passengers) {
        // Slightly different base than void to keep a visible difference
        $base = 280; // original
        $variance = 10; // tweak for refund
        $total = $base - $variance; // e.g., 270
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'TotalRefundAmount' => $total,
                'Currency' => 'USD',
                'AdminCharges' => 0,
                'GSTCharge' => 0,
                'CancellationCharges' => 0,
                'SLAInMinutes' => 0,
                'Message' => 'Refund quote request processed successfully'
            ],
            'Message' => 'Refund quote request processed successfully'
        ];
    }
    
    /**
     * Generate Refund Response (actual refund operation)
     */
    public static function getRefundResponse($ptrId) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'Message' => 'Refund request accepted and is being processed'
            ],
            'Message' => 'Refund request accepted and is being processed'
        ];
    }
    
    /**
     * Generate ReissueQuote Response
     */
    public static function getReissueQuoteResponse($passengers) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'Message' => 'Reissue quote request processed successfully'
            ],
            'Message' => 'Reissue quote request processed successfully'
        ];
    }
    
    /**
     * Generate GetExchangeQuote Response
     */
    public static function getGetExchangeQuoteResponse($ptrId) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => $ptrId,
                'Status' => 'Completed',
                'Resolution' => 'QuoteUpdated',
                'RequestedPreferences' => [
                    [
                        'PreferenceId' => 'PREF_' . rand(1000, 9999),
                        'FareDifference' => 1500,
                        'Currency' => 'INR',
                        'NewDepartureDate' => date('Y-m-d', strtotime('+2 days')),
                        'NewCabinClass' => 'Economy',
                        'Message' => 'Option 1: 2 days later, Economy class'
                    ],
                    [
                        'PreferenceId' => 'PREF_' . rand(1000, 9999),
                        'FareDifference' => 2500,
                        'Currency' => 'INR',
                        'NewDepartureDate' => date('Y-m-d', strtotime('+3 days')),
                        'NewCabinClass' => 'Business',
                        'Message' => 'Option 2: 3 days later, Business class'
                    ]
                ]
            ],
            'Message' => 'Exchange quote retrieved successfully'
        ];
    }
    
    /**
     * Generate Accept ReissueQuote Response
     */
    public static function getAcceptReissueQuoteResponse($ptrId) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRStatus' => 'InProcess',
                'SLAInMinutes' => 90,
                'Message' => 'Reissue quote accepted and is being processed'
            ],
            'Message' => 'Reissue quote accepted and is being processed'
        ];
    }
    
    /**
     * Generate TripDetails Response (after reissue completion)
     */
    public static function getTripDetailsResponse($mfreNum) {
        return [
            'Success' => true,
            'Data' => [
                'TripDetailsResult' => [
                    'TravelItinerary' => [
                        'TicketStatus' => 'Ticketed',
                        'PassengerInfos' => [
                            [
                                'Passenger' => [
                                    'PassportNumber' => 'A12345678'
                                ],
                                'ETickets' => [
                                    [
                                        'ETicketType' => 'Reissued',
                                        'ETicketNumber' => '1234567890123456'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Message' => 'Trip details retrieved successfully'
        ];
    }
    
    /**
     * Generate Error Response for testing
     */
    public static function getErrorResponse($errorType = 'general') {
        $errors = [
            'split_pnr' => [
                'Success' => false,
                'Message' => 'Please use Split PNR feature to divide the passengers.',
                'ErrorCode' => 'SPLIT_PNR_REQUIRED'
            ],
            'already_in_process' => [
                'Success' => false,
                'Message' => 'PTR is already in process for these passengers.',
                'ErrorCode' => 'ALREADY_IN_PROCESS'
            ],
            'refund_details_missing' => [
                'Success' => false,
                'Message' => 'Refund quote request cannot be processed as the refund details are missing from the request.',
                'ErrorCode' => 'REFUND_DETAILS_MISSING'
            ],
            'general' => [
                'Success' => false,
                'Message' => 'An error occurred while processing the request.',
                'ErrorCode' => 'GENERAL_ERROR'
            ]
        ];
        
        return $errors[$errorType] ?? $errors['general'];
    }
}
?>

