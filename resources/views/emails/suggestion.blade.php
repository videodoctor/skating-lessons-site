<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
  .wrap { max-width: 580px; margin: 40px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
  .header { background: #001F5B; padding: 2rem; text-align: center; }
  .header h1 { color: #fff; font-size: 1.5rem; margin: 0; font-family: Arial, sans-serif; }
  .header p { color: rgba(255,255,255,.7); font-size: .9rem; margin: .5rem 0 0; }
  .body { padding: 2rem; }
  .slot-box { background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; padding: 1.25rem 1.5rem; margin: 1.5rem 0; }
  .slot-box .date { font-size: 1.3rem; font-weight: 700; color: #001F5B; }
  .slot-box .details { color: #374151; font-size: .9rem; margin-top: .3rem; }
  .message-box { background: #fffbf0; border-left: 4px solid #C9A84C; padding: 1rem 1.25rem; border-radius: 0 8px 8px 0; margin: 1.25rem 0; font-style: italic; color: #374151; }
  .btn-row { display: flex; gap: 1rem; margin: 2rem 0; }
  .btn { display: inline-block; padding: .85rem 2rem; border-radius: 7px; font-weight: 700; font-size: 1rem; text-decoration: none; text-align: center; flex: 1; }
  .btn-accept { background: #065f46; color: #fff; }
  .btn-decline { background: #f3f4f6; color: #374151; border: 1.5px solid #e5e7eb; }
  .footer { background: #f8fafc; padding: 1.25rem 2rem; text-align: center; font-size: .78rem; color: #9ca3af; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>⛸️ New Time Suggestion</h1>
    <p>Coach Kristine would like to suggest a different lesson time</p>
  </div>
  <div class="body">
    <p>Hi {{ $booking->client_name }},</p>
    <p>Coach Kristine has reviewed your lesson request and would like to suggest a different time that works better for her schedule.</p>

    <p><strong>Suggested new time:</strong></p>
    <div class="slot-box">
      <div class="date">{{ $date }}</div>
      <div class="details">🕐 {{ $time }} &nbsp;·&nbsp; 📍 {{ $rink }}</div>
      @if($booking->service)
      <div class="details" style="margin-top:.25rem;">📋 {{ $booking->service->name }}</div>
      @endif
    </div>

    @if($booking->suggestion_message)
    <div class="message-box">
      <strong>Message from Coach Kristine:</strong><br>
      "{{ $booking->suggestion_message }}"
    </div>
    @endif

    <p>Please let Coach Kristine know if this new time works for you:</p>

    <div class="btn-row">
      <a href="{{ $acceptUrl }}" class="btn btn-accept">✅ Accept This Time</a>
      <a href="{{ $declineUrl }}" class="btn btn-decline">❌ Decline</a>
    </div>

    <p style="font-size:.8rem;color:#9ca3af;">These links are unique to your booking and will expire once used. If you have questions, reply to this email or contact Kristine at kristine@kristineskates.com.</p>
  </div>
  <div class="footer">
    Kristine Skates · St. Louis, MO · <a href="https://kristineskates.com" style="color:#001F5B;">kristineskates.com</a>
  </div>
</div>
</body>
</html>
