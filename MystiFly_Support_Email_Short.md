# MystiFly Support - Quick Email Template

**Subject:** RefundQuote API Error: "refund details missing" - Need Assistance

---

**Dear MystiFly Support,**

We're experiencing an issue with the RefundQuote PTR API. Our requests are failing with:

**Error:** `"Refund quote request cannot be processed as the refund details are missing from the request."`

**Request:**
```json
{
  "ptrType": "RefundQuote",
  "mFRef": "MF30839425",
  "AllowChildPassenger": false,
  "passengers": [{"firstName": "Ursula", "lastName": "Peters", "title": "Mr", "eTicket": "1765457557578", "passengerType": "ADT"}],
  "AdditionalNote": "Pls quote refund for tkt 1765457557578"
}
```

**Response:**
```json
{"Success": false, "Data": null, "Message": "Refund quote request cannot be processed as the refund details are missing from the request."}
```

**Booking Details:**
- MF Reference: MF30839425
- Status: Ticketed
- Passenger: Mr Ursula Peters (ADT)
- E-ticket: 1765457557578

All required fields are present per documentation. Could you please clarify what "refund details" are missing?

**Environment:** Test API (restapidemo.myfarebox.com)

Thanks for your assistance.

**Best regards,**  
[Your Name] 