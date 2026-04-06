# Twilio A2P 10DLC Campaign — Kristine Skates

**Last Updated:** 2026-04-06
**Campaign Status:** Pending (resubmitted)
**Brand:** Kristine Skates
**Business:** Private skating instruction, St. Louis, MO
**Website:** https://kristineskates.com

---

## Campaign Description

This campaign sends automated SMS messages to clients of Kristine Skates, a private skating instruction business based in St. Louis, MO. Message types include:

- **Lesson reminders** approximately 30 hours before scheduled skating lessons
- **Booking confirmations and updates** when lesson requests are approved, rejected, or modified
- **Schedule change notifications** for cancellations or time changes
- **Payment reminders** for outstanding lesson balances
- **Availability notifications** for waitlisted clients when lesson times open
- **Public skate schedule updates** for clients who opt in to receive daily rink schedules
- **One-time passcodes** for account verification

Users can reply LESSONS to receive a list of upcoming lessons, and SKATE to receive today's public skating times at area rinks.

All messages are sent only to users who have provided explicit opt-in consent. Users can manage their notification preferences per category (SMS and/or email) from their account dashboard at https://kristineskates.com/client/dashboard. Users can opt out at any time by replying STOP.

---

## Opt-In Description

End users consent to receive SMS text messages from Kristine Skates through three methods:

**Client Registration:** During account registration at https://kristineskates.com/client/register, users are presented with an optional, unchecked checkbox labeled: "I agree to receive SMS text messages from Kristine Skates, including lesson reminders, booking confirmations, schedule changes, payment reminders, availability notifications, and public skate schedules. You will receive a confirmation text upon opting in. Message frequency varies. Message and data rates may apply. Reply STOP to opt out or HELP for help." The checkbox is never pre-checked, and phone number entry is optional. After submitting the form with the checkbox checked, users immediately receive a confirmation SMS.

**Guest Booking:** During lesson booking at https://kristineskates.com/book, the same optional, unchecked checkbox is presented with identical disclosure. Users who check the box and submit receive an opt-in confirmation SMS.

**Waitlist Sign-up:** When booking is paused, users may join a waitlist at https://kristineskates.com/book. The same optional, unchecked SMS consent checkbox with identical disclosure is presented. Users who opt in will receive availability notifications when lesson times open.

Registered clients can manage per-category notification preferences (SMS and/or email) from their account dashboard at https://kristineskates.com/client/dashboard.

**Verification:** Opt-in forms and disclosures are publicly viewable at the URLs above with no login required. Screenshots of the opt-in process including the checkbox and disclosure are available at https://kristineskates.com/sms-opt-in. No mobile information is shared with third parties for marketing purposes.

Reply STOP to unsubscribe — user receives confirmation no further messages will be sent. Reply HELP for assistance or email kristine@kristineskates.com.

Terms: https://kristineskates.com/terms-and-conditions
Privacy: https://kristineskates.com/privacy-policy

---

## Consent Checkbox Text (exact, on all forms)

> **Optional:** I agree to receive SMS text messages from Kristine Skates, including lesson reminders, booking confirmations, schedule changes, payment reminders, availability notifications, and public skate schedules. You will receive a confirmation text upon opting in. Message frequency varies. Message and data rates may apply. Reply **STOP** to opt out or **HELP** for help. View our [Privacy Policy](https://kristineskates.com/privacy-policy).

### Forms where this checkbox appears:
1. Client Registration — https://kristineskates.com/client/register
2. Guest Booking — https://kristineskates.com/book (select time step)
3. Waitlist (booking paused) — https://kristineskates.com/book
4. Home Page Waitlist Modal — https://kristineskates.com (coming soon services)
5. Accept Terms (first login) — https://kristineskates.com/client/accept-terms
6. SMS Opt-In Screenshots — https://kristineskates.com/sms-opt-in

---

## Sample Messages

### Sample 1 — Lesson Reminder
```
Reminder: Your skating lesson for [Student] is tomorrow at 3:30 PM at Creve Coeur Ice Arena. $55 due at lesson. Reply YES to confirm or NO to cancel. Cancellations less than 24 hours before the lesson will be billed at the full rate. Reply STOP to opt out or HELP for assistance. — Kristine Skates
```

### Sample 2 — Opt-In Confirmation
```
You are now opted in to SMS notifications from Kristine Skates. Msg frequency varies. Msg & data rates may apply. Reply STOP to cancel or HELP for help. — Kristine Skates
```

### Sample 3 — Upcoming Lessons (LESSONS keyword reply)
```
Upcoming lessons for Jane: Sat Mar 15 12:00PM Creve Coeur, Wed Mar 19 3:30PM Brentwood. Reply HELP for assistance or STOP to opt out. — Kristine Skates
```

### Sample 4 — Booking Confirmation
```
Confirmed! Your skating lesson for [Student] is Saturday, March 15 at 12:00 PM at Creve Coeur Ice Arena. Pay $55 at https://kristineskates.com/pay/ABC123. Reply STOP to opt out or HELP for assistance. — Kristine Skates
```

### Sample 5 — Public Skate Times (SKATE keyword reply)
```
Today's public skate times: Creve Coeur 9:15AM-1:00PM, Brentwood 2:00PM-4:00PM, Webster Groves 1:00PM-3:00PM. Reply STOP to opt out or HELP for assistance. — Kristine Skates
```

---

## Keywords

| Keyword | Response |
|---------|----------|
| STOP | Opt-out confirmation, no further messages sent |
| HELP | Assistance info + contact email |
| LESSONS | List of upcoming lessons for the user |
| SKATE | Today's public skate times at area rinks |

---

## Opt-Out / STOP Handling

When a user replies STOP:
1. Twilio automatically marks the number as opted out
2. No further messages are sent
3. User receives confirmation: "You have been unsubscribed from Kristine Skates SMS notifications. No further messages will be sent. Reply START to re-subscribe or email kristine@kristineskates.com for help."

---

## Compliance Links

- **Privacy Policy:** https://kristineskates.com/privacy-policy
- **Terms & Conditions:** https://kristineskates.com/terms-and-conditions
- **SMS Opt-In Page (screenshots):** https://kristineskates.com/sms-opt-in

---

## Version History

| Date | Change |
|------|--------|
| 2026-04-06 | Updated Sample 2 opt-in confirmation: "lesson reminders" → "notifications" |
| 2026-04-06 | Updated SmsService opt-in confirmation message to match |
| 2026-04-06 | Added payment reminders to consent text and campaign description |
| 2026-04-06 | Expanded consent to list all 6 message types explicitly |
| 2026-04-06 | Added waitlist sign-up as third opt-in method |
| 2026-04-06 | Added notification preferences dashboard reference |
| 2026-04-05 | Updated consent from "lesson reminders" to include "availability notifications" |
| 2026-04-01 | Standardized consent text across all forms |
| 2026-03-29 | Initial A2P campaign submission |
