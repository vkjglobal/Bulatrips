# 🚀 QUICK REFERENCE - Void & Refund APIs

## 📋 **WHICH API TO USE?**

```
┌────────────────────────────────────────────────┐
│ Is ticket within 24 hours? │ Use VOID          │
│ Is ticket after 24 hours?  │ Use REFUND        │
└────────────────────────────────────────────────┘
```

---

## ⚡ **VOID FLOW (Quick)**

```
User → VoidQuote (instant) → Accept → Void API → Cron monitors → Done (2 hrs)
```

**Files:**
- `cancel_post_ticket_process_VoidQuote.php` - Get quote
- `cancel_post_ticket_process_Void.php` - Process void
- `CronJob/cronVoidStatus.php` - Monitor completion

---

## 📧 **REFUND FLOW (Email-based)**

```
User → RefundQuote → "Check email" → Cron sends email → User accepts in email → Cron monitors → Done (24 hrs)
```

**Files:**
- `refund_post_ticket.php` - Request quote
- `CronJob/cronRefundQuoteStatus.php` - Email when ready
- `accept_refund_quote.php` - Process acceptance
- `CronJob/cronRefundConfirmation.php` - Monitor completion

---

## 🔧 **CRON JOBS**

```bash
# Every 5 minutes
*/5 * * * * php CronJob/cronVoidStatus.php
*/5 * * * * php CronJob/cronRefundQuoteStatus.php

# Every 10 minutes
*/10 * * * * php CronJob/cronRefundConfirmation.php
```

---

## 💰 **SERVICE FEE CALCULATION**

```
Mystifly Amount:  $126.30
refund_fee:       -$10.00  ← From settings table
refund_addition:  -$5.00   ← From settings table
─────────────────────────
Final to Customer: $111.30  ← This goes to Windcave
```

---

## 🔐 **SECURE LINKS**

```php
// Generate token
$token = hash('sha256', $ptr_id . $booking_id . $action . 'secret');

// URL format
accept_refund_quote.php?ptr_id=12668&booking_id=196&action=yes&token=abc123...
```

---

## 📊 **DATABASE STATES**

```sql
-- Void
ptr_type='Void', ptr_status='InProcess' → 'Completed'

-- RefundQuote
ptr_type='RefundQuote', ptr_status='InProcess' → 'Completed'
message='RefundQuote emailed to customer'

-- Refund (after accept)
ptr_type='Refund', ptr_status='InProcess' → 'Completed'
```

---

## 🚨 **ALERT TRIGGERS**

```
SLA + 45 mins → Customer delay email 📧
SLA + 60 mins → Admin alert 🚨 (admin + mindinstructions)
```

---

## 📧 **EMAIL FLOW**

**Void:** 1 email (completion)  
**Refund:** 3 emails (quote → accepted → completed)

---

## 🔍 **QUICK DEBUG**

```bash
# Check logs
tail -f uploads/logFiles/refundQuoteCron.txt
tail -f uploads/logFiles/voidStatusCron.txt

# Manual test
php CronJob/cronRefundQuoteStatus.php

# Check settings
mysql> SELECT * FROM settings WHERE `key` IN ('refund_fee', 'refund_addition');
```

---

## ✅ **CHECKLIST FOR GO-LIVE**

- [ ] Cron jobs added to crontab
- [ ] Settings configured (refund_fee, refund_addition)
- [ ] Email templates tested
- [ ] Token security verified
- [ ] Windcave integration tested
- [ ] Admin emails receiving alerts
- [ ] Customer emails delivering
- [ ] Log files writable
- [ ] Database permissions OK

---

**Quick Support:** Check `REFUND_CRON_IMPLEMENTATION_SUMMARY.md` for detailed documentation.

