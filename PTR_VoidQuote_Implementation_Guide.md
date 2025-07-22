# PTR VoidQuote API Implementation Guide

## 🎯 Overview
This document outlines the implementation of **VoidQuote PTR (Post Ticketing Request)** functionality for Bulatrips.com using MyFareBox API.

## 📋 What Has Been Implemented

### 1. **Enhanced Backend (cancel_post_ticket.php)**
- ✅ **API Integration**: Properly integrated with MyFareBox VoidQuote API
- ✅ **Request Validation**: Validates passenger data and booking details
- ✅ **Error Handling**: Handles specific API error scenarios:
  - `NOT_ELIGIBLE`: Booking not eligible for voiding
  - `INVALID_REFERENCE`: Invalid MF Reference
  - `CONNECTION_ERROR`: API connection issues
- ✅ **Response Processing**: Extracts refund details for each passenger
- ✅ **Database Storage**: Stores VoidQuote responses for tracking

### 2. **Enhanced Frontend (cancel_user.php)**
- ✅ **Improved UI**: Professional VoidQuote results modal
- ✅ **Passenger Breakdown**: Shows individual refund calculations
- ✅ **Error Handling**: User-friendly error messages
- ✅ **Confirmation Flow**: Two-step process (Quote → Process)

### 3. **Database Structure**
- ✅ **New Table**: `void_quotes` table for tracking VoidQuote requests
- ✅ **Data Storage**: Stores complete API responses and processing status

## 🔧 API Implementation Details

### **VoidQuote API Request Format**
```json
{
  "ptrType": "VoidQuote",
  "mFRef": "MF15171220",
  "AllowChildPassenger": false,
  "passengers": [
    {
      "firstName": "Alex",
      "lastName": "Tan", 
      "title": "Mr",
      "eTicket": "5654345667787",
      "passengerType": "ADT"
    }
  ]
}
```

### **Success Response Processing**
```json
{
  "Success": true,
  "Data": {
    "PTRType": "VoidQuote",
    "PTRStatus": "Completed",
    "VoidingWindow": "2025-04-19T21:59:00",
    "VoidQuotes": [
      {
        "FirstName": "Alex",
        "LastName": "Tan",
        "AdminCharges": "0.00",
        "GSTCharge": "0.00", 
        "TotalVoidingFee": "0.00",
        "TotalRefundAmount": "249.75",
        "Currency": "USD"
      }
    ]
  }
}
```

## 🎨 User Experience Flow

### **1. Initial State**
- User sees "Void Request" button for eligible bookings
- Button appears only for ticketed bookings within voiding window

### **2. VoidQuote Process**
- User selects passengers to void
- Clicks "Void Request" button
- System calls VoidQuote API
- Shows detailed refund breakdown in modal

### **3. Confirmation**
- User reviews refund amounts per passenger
- Can proceed with actual void process or cancel
- Clear breakdown of charges and refunds

## 📊 Database Schema

### **void_quotes Table**
```sql
CREATE TABLE `void_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `mf_ref` varchar(20) NOT NULL,
  `ptr_type` varchar(50) DEFAULT 'VoidQuote',
  `ptr_status` varchar(50) DEFAULT NULL,
  `voiding_window` datetime DEFAULT NULL,
  `total_refund_amount` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `passenger_details` longtext DEFAULT NULL,
  `full_response` longtext DEFAULT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

## 🚀 Next Steps for Complete PTR Implementation

### **Phase 2: Actual Void Processing**
1. **Void API**: Implement actual void processing after VoidQuote
2. **Status Tracking**: Real-time PTR status updates
3. **Notification System**: Email/SMS confirmations

### **Phase 3: Reschedule PTR**
1. **Reschedule Quote API**: Get reschedule costs
2. **Flight Search Integration**: Show alternative flights
3. **Fare Difference Calculation**: Handle price differences

### **Phase 4: Refund PTR**
1. **Refund Quote API**: For bookings outside void window
2. **Refund Processing**: Handle partial/full refunds
3. **Refund Tracking**: Monitor refund status

## 🔍 Testing Scenarios

### **Test Case 1: Successful VoidQuote**
- **Input**: Valid MF Reference with ticketed passengers
- **Expected**: Detailed refund breakdown displayed
- **API Response**: Success with VoidQuotes array

### **Test Case 2: Not Eligible for Voiding**
- **Input**: Booking outside voiding window
- **Expected**: User-friendly error message
- **API Response**: "Booking is not eligible for voiding"

### **Test Case 3: Invalid MF Reference**
- **Input**: Non-existent or malformed MF Reference
- **Expected**: Clear error about invalid reference
- **API Response**: "Invalid MFRef"

## 📝 Configuration

### **Required Constants (common_const.php)**
```php
define("APIENDPOINT", "https://restapidemo.myfarebox.com/api/");
define("BEARER", "YOUR_API_TOKEN");
```

### **Log Files**
- **VoidQuote Logs**: `uploads/logFiles/voidQuote.txt`
- **Request/Response**: Complete API communication logged

## 🛡️ Security Features

1. **Input Sanitization**: All user inputs properly sanitized
2. **Data Validation**: Passenger details validated before API call
3. **Error Logging**: Comprehensive logging for debugging
4. **SQL Injection Protection**: PDO prepared statements used

## 📈 Performance Optimizations

1. **Parallel Processing**: Multiple passengers processed efficiently
2. **Caching**: VoidQuote results cached in database
3. **Asynchronous UI**: Non-blocking user interface updates
4. **Error Recovery**: Graceful handling of API failures

## 🎯 Business Value

### **For Customers**
- ✅ **Transparency**: Clear refund breakdown before processing
- ✅ **Control**: Select specific passengers to void
- ✅ **Speed**: Real-time quotes without commitment

### **For Business**
- ✅ **Efficiency**: Automated PTR processing
- ✅ **Tracking**: Complete audit trail
- ✅ **Compliance**: Proper handling of airline policies

---

## 📞 Support & Maintenance

For any issues or enhancements:
1. Check log files in `uploads/logFiles/`
2. Verify API credentials and endpoints
3. Test with different booking scenarios
4. Monitor database performance

**Implementation Status**: ✅ **Phase 1 Complete - VoidQuote API Fully Integrated** 