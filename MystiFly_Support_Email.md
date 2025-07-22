# MystiFly Support Email - RefundQuote API Issue

**Subject:** RefundQuote API Failing with "refund details missing" Error - Urgent Support Needed

---

**Dear MystiFly Support Team,**

We are experiencing a persistent issue with the **RefundQuote** PostTicketingRequest (PTR) API and need your urgent assistance.

## **Issue Description**
Our RefundQuote API calls are consistently failing with the error message:
```
"Refund quote request cannot be processed as the refund details are missing from the request."
```

Despite following the API documentation exactly, we cannot identify what "refund details" are missing from our request.

## **API Request Details**

**Endpoint:** `PostTicketingRequest`  
**Method:** `POST`  
**HTTP Response Code:** `200`

**Request Payload:**
```json
{
  "ptrType": "RefundQuote",
  "mFRef": "MF30839425",
  "AllowChildPassenger": false,
  "passengers": [
    {
      "firstName": "Ursula",
      "lastName": "Peters", 
      "title": "Mr",
      "eTicket": "1765457557578",
      "passengerType": "ADT"
    }
  ],
  "AdditionalNote": "Pls quote refund for tkt 1765457557578"
}
```

**API Response:**
```json
{
  "Success": false,
  "Data": null,
  "Message": "Refund quote request cannot be processed as the refund details are missing from the request."
}
```

## **Booking Information**

- **MF Reference:** MF30839425
- **Booking Status:** Booked
- **Ticket Status:** Ticketed
- **Ticket Time Limit:** 2025-07-07 16:49:08
- **Departure Date:** 2025-07-16 03:25:00
- **Fare Type:** Public
- **Void Window:** 2025-07-05T16:29:59.997

## **Passenger Details**

- **Name:** Mr Ursula Peters
- **Passenger Type:** ADT (Adult)
- **E-Ticket Number:** 1765457557578

## **What We've Tried**

1. ✅ Verified all required fields are present as per API documentation
2. ✅ Confirmed booking is in "Ticketed" status
3. ✅ Validated passenger details match exactly with booking
4. ✅ Tested with both `AllowChildPassenger: true` and `false`
5. ✅ Tried different AdditionalNote formats
6. ✅ Confirmed e-ticket number is correct
7. ✅ Verified MF reference is valid and active

## **API Documentation Compliance**

Our request includes all required fields as specified in your documentation:
- ✅ `ptrType`: "RefundQuote"
- ✅ `mFRef`: Valid MF reference
- ✅ `passengers`: Array with passenger details
- ✅ `firstName`, `lastName`, `title`, `eTicket`, `passengerType`
- ✅ `AdditionalNote`: Free text message

## **Questions for Support**

1. **What specific "refund details" are missing from our request?**
2. Are there any additional fields required that are not mentioned in the documentation?
3. Is there a specific format requirement for any of the fields?
4. Could this be related to the booking's fare type or airline restrictions?
5. Is there a way to validate the booking's refund eligibility before making the RefundQuote request?

## **Environment Details**

- **API Environment:** Test/Staging
- **Bearer Token:** 18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560
- **API Endpoint:** https://restapidemo.myfarebox.com/api/
- **Integration:** REST API

## **Urgency**

This issue is blocking our flight cancellation feature for customers. We need this resolved urgently to maintain our service quality.

**Please provide:**
1. Detailed explanation of what refund details are missing
2. A corrected example request for our specific booking
3. Any additional validation steps we should perform

Thank you for your prompt assistance.

**Best regards,**  
[Your Name]  
[Your Company]  
[Contact Details]

---

**Request ID:** [Generate a unique ID for tracking]  
**Date:** $(date)  
**Booking Reference:** MF30839425 