# Mystifly Support - RefundQuote API Issue

**Subject:** RefundQuote API Error - "refund details are missing from the request"

## **Issue:**
RefundQuote API returning error: **"Refund quote request cannot be processed as the refund details are missing from the request."**

## **Our Request:**
```json
{
  "ptrType": "RefundQuote",
  "mFRef": "MF31170725",
  "AllowChildPassenger": false,
  "passengers": [
    {
      "firstName": "Jameson",
      "lastName": "Welch", 
      "title": "Mr",
      "eTicket": "TKT470949",
      "passengerType": "ADT"
    }
  ],
  "AdditionalNote": "Refund quote request"
}
```

## **Response:**
```json
{
  "Success": false,
  "Data": null,
  "Message": "Refund quote request cannot be processed as the refund details are missing from the request."
}
```

## **Booking Info:**
- **MF Reference:** MF31170725
- **E-Ticket:** TKT470949
- **Status:** Ticketed
- **Void Window:** Expired

## **Questions:**
1. What "refund details" are missing?
2. Is our request structure correct?
3. Is this booking eligible for RefundQuote?
4. What's the correct request format?

## **Need:**
- Correct request format
- Required parameters list
- Working example

**Note:** VoidQuote API is working fine. Only RefundQuote has this issue.

**Please provide correct implementation details.** 