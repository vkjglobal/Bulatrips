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
    public static function getReissueQuoteResponse($passengers, $requestData = []) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => 'PTR_' . time() . '_' . rand(1000, 9999),
                'PTRType' => 'ReissueQuote',
                'MFRef' => $requestData['mFRef'] ?? 'MF31554025',
                'SLAInMinutes' => 60,
                'PTRStatus' => 'InProcess',
                'Message' => 'Reissue quote request processed successfully'
            ],
            'Message' => 'Reissue quote request processed successfully'
        ];
    }
    
    /**
     * Generate GetExchangeQuote Response (Per Mystifly Documentation)
     */
    public static function getGetExchangeQuoteResponse($ptrId) {
        return [
            'Success' => true,
            'Data' => [
                'PTRId' => $ptrId,
                'PTRType' => 'ReIssueQuote',
                'Status' => 'Completed',
                'MFRef' => 'MF31554025',
                'CreatedOn' => date('Y-m-d\TH:i:s.v'),
                'RequestCompletionTime' => date('Y-m-d\TH:i:s.v'),
                'CreatedByName' => 'Mystifly API Team',
                'Resolution' => 'QuoteUpdated',
                'Passengers' => [
                    [
                        'ETicket' => 'TKT475564',
                        'PassengerType' => 'ADT',
                        'Tittle' => 'MISS',
                        'FirstName' => 'Vaughan',
                        'LastName' => 'Butler'
                    ]
                ],
                'RequestedPreferences' => [
                    [
                        'Option' => 1,
                        'CreatedOn' => date('Y-m-d\TH:i:s.v'),
                        'QuotedSegments' => [
                            [
                                'Origin' => 'LHE',
                                'Destination' => 'JED',
                                'CabinClass' => 'Y',
                                'DepartureDatetime' => date('Y-m-d\T10:00:00', strtotime('+2 days')),
                                'ArrivalDateTime' => date('Y-m-d\T14:30:00', strtotime('+2 days')),
                                'AirlineCode' => 'QR',
                                'FlightNumber' => 629,
                                'Duration' => '4.30',
                                'Stops' => 0,
                                'BookingClass' => 'S',
                                'isReturn' => false
                            ]
                        ],
                        'QuotedFares' => [
                            [
                                'PassengerType' => 'ADT',
                                'BaseFareDifference' => 45.50,
                                'TaxDifference' => 8.25,
                                'AdminFee' => 0,
                                'GST' => 0,
                                'NoShowPenalty' => 0,
                                'Currency' => 'USD',
                                'Penalty' => 25.00,
                                'PassengerCount' => 1,
                                'TotalFareDifference' => 78.75
                            ]
                        ],
                        'PTRRemarks' => [
                            [
                                'Remarks' => 'Reissue available with fare difference',
                                'Reason' => '',
                                'RemarksType' => 'QuoteRemarks'
                            ]
                        ]
                    ],
                    [
                        'Option' => 2,
                        'CreatedOn' => date('Y-m-d\TH:i:s.v'),
                        'QuotedSegments' => [
                            [
                                'Origin' => 'LHE',
                                'Destination' => 'JED',
                                'CabinClass' => 'C',
                                'DepartureDatetime' => date('Y-m-d\T15:45:00', strtotime('+3 days')),
                                'ArrivalDateTime' => date('Y-m-d\T20:15:00', strtotime('+3 days')),
                                'AirlineCode' => 'QR',
                                'FlightNumber' => 631,
                                'Duration' => '4.30',
                                'Stops' => 0,
                                'BookingClass' => 'J',
                                'isReturn' => false
                            ]
                        ],
                        'QuotedFares' => [
                            [
                                'PassengerType' => 'ADT',
                                'BaseFareDifference' => 125.00,
                                'TaxDifference' => 15.50,
                                'AdminFee' => 0,
                                'GST' => 0,
                                'NoShowPenalty' => 0,
                                'Currency' => 'USD',
                                'Penalty' => 25.00,
                                'PassengerCount' => 1,
                                'TotalFareDifference' => 165.50
                            ]
                        ],
                        'PTRRemarks' => [
                            [
                                'Remarks' => 'Business class upgrade available',
                                'Reason' => '',
                                'RemarksType' => 'QuoteRemarks'
                            ]
                        ]
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

