# 🚀 Postman Setup for Mystifly API Testing

## 📋 **Postman Collection Setup**

### **1. Create New Collection**
- Collection Name: `Mystifly API Testing`
- Description: `Testing PTR operations for Bulatrips`

### **2. Environment Variables**
Create new environment: `Mystifly Staging`

| Variable | Value |
|----------|-------|
| `base_url` | `https://restapidemo.myfarebox.com/api` |
| `bearer_token` | `18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560` |
| `mf_ref` | `MF31170625` |
| `booking_id` | `181` |

---

## 🎯 **API Request Configurations**

### **1. RefundQuote Request (Current Issue)**

#### **Request Details:**
- **Method:** `POST`
- **URL:** `{{base_url}}/PostTicketingRequest`
- **Headers:**
```
Content-Type: application/json
Authorization: Bearer {{bearer_token}}
```

#### **Request Body (Raw JSON):**
```json
{
    "ptrType": "RefundQuote",
    "mFRef": "{{mf_ref}}",
    "AllowChildPassenger": false,
    "passengers": [
        {
            "firstName": "Dillon",
            "lastName": "Blevins", 
            "title": "MISS",
            "eTicket": "157279645316",
            "passengerType": "ADT"
        }
    ],
    "AdditionalNote": "Refund quote request - user requested refund"
}
```

### **2. VoidQuote Request (For Comparison)**

#### **Request Details:**
- **Method:** `POST`
- **URL:** `{{base_url}}/PostTicketingRequest`
- **Headers:**
```
Content-Type: application/json
Authorization: Bearer {{bearer_token}}
```

#### **Request Body (Raw JSON):**
```json
{
    "ptrType": "VoidQuote",
    "mFRef": "{{mf_ref}}",
    "AllowChildPassenger": false,
    "passengers": [
        {
            "firstName": "Dillon",
            "lastName": "Blevins",
            "title": "MISS", 
            "eTicket": "157279645316",
            "passengerType": "ADT"
        }
    ],
    "AdditionalNote": "Void quote request"
}
```

### **3. TripDetails Request (Working Endpoint)**

#### **Request Details:**
- **Method:** `GET`
- **URL:** `{{base_url}}/v1.1/TripDetails/{{mf_ref}}`
- **Headers:**
```
Content-Type: application/json
Authorization: Bearer {{bearer_token}}
```

---

## 🧪 **Test Scenarios**

### **Scenario 1: RefundQuote (Current Issue)**
- **Expected:** Success response with PTR ID
- **Current:** 500 Internal Server Error
- **Purpose:** Reproduce the error for Mystifly support

### **Scenario 2: VoidQuote (Comparison)**
- **Expected:** Success or proper error message
- **Purpose:** Check if issue is specific to RefundQuote

### **Scenario 3: TripDetails (Control Test)**
- **Expected:** Success response
- **Purpose:** Verify API connectivity and auth

---

## 📊 **Expected Responses**

### **Successful RefundQuote Response:**
```json
{
    "Success": true,
    "Data": {
        "PTRId": 5275,
        "PTRType": "RefundQuote", 
        "MFRef": "MF31170625",
        "SLAInMinutes": 60,
        "PTRStatus": "InProcess",
        "RefundQuotes": [],
        "Message": "Request for refund quote has been submitted successfully. Your Request# is 5275."
    }
}
```

### **Current Error Response:**
```json
{
    "Data": null,
    "Success": false,
    "Message": "The remote server returned an error: (500) Internal Server Error."
}
```

---

## 🔧 **Postman Test Steps**

### **Step 1: Import Collection**
1. Open Postman
2. Create new collection: "Mystifly API Testing"
3. Add environment variables
4. Import the 3 requests above

### **Step 2: Test API Connectivity**
1. **First test TripDetails** (GET request)
2. Should return booking details
3. Confirms API auth is working

### **Step 3: Test VoidQuote** 
1. Send VoidQuote request
2. Check if 500 error is specific to RefundQuote
3. Document response

### **Step 4: Test RefundQuote (Problem)**
1. Send RefundQuote request
2. **Should reproduce 500 error**
3. Capture exact response for email

### **Step 5: Document Results**
1. Screenshot error responses
2. Copy request/response details
3. Include in Mystifly support email

---

## 📧 **For Mystifly Email**

### **Include These Postman Results:**
1. **Request Headers** (with auth token)
2. **Request Body** (exact JSON)
3. **Response Status** (500 error)
4. **Response Body** (error message)
5. **Timestamp** of test

### **Additional Context:**
- **Environment:** Staging (restapidemo.myfarebox.com)
- **Affected Operations:** RefundQuote
- **Working Operations:** TripDetails, some VoidQuote
- **Impact:** Production customer operations

---

## 🎯 **Quick Postman Import**

### **Copy-Paste Ready Collection:**
```json
{
    "info": {
        "name": "Mystifly API Testing",
        "description": "Testing PTR operations for Bulatrips"
    },
    "item": [
        {
            "name": "RefundQuote - Error Test",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    },
                    {
                        "key": "Authorization", 
                        "value": "Bearer {{bearer_token}}"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"ptrType\": \"RefundQuote\",\n    \"mFRef\": \"{{mf_ref}}\",\n    \"AllowChildPassenger\": false,\n    \"passengers\": [\n        {\n            \"firstName\": \"Dillon\",\n            \"lastName\": \"Blevins\",\n            \"title\": \"MISS\",\n            \"eTicket\": \"157279645316\",\n            \"passengerType\": \"ADT\"\n        }\n    ],\n    \"AdditionalNote\": \"Refund quote request - user requested refund\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/PostTicketingRequest",
                    "host": ["{{base_url}}"],
                    "path": ["PostTicketingRequest"]
                }
            }
        }
    ]
}
```

**Postman setup ready hai - test kar ke results Mystifly ko send karo!** 🚀
