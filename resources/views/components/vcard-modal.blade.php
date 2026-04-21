@if (session('show_vcard_modal'))
<div id="vcardModal" class="vcard-modal" onclick="if(event.target===this)vcardClose()">
  <div class="vcard-modal-content">
    <button type="button" onclick="vcardClose()" class="vcard-close" aria-label="Close">&times;</button>
    <div class="vcard-icon">📇</div>
    <h2>Save Kristine to your contacts</h2>
    <p>That way, texts and calls from <strong>314-314-SKATE</strong> show up as "Kristine Skates" instead of just a phone number.</p>
    <a href="{{ route('vcard') }}" class="vcard-download-btn" onclick="vcardClose()">Download Contact Card</a>
    <button type="button" onclick="vcardClose()" class="vcard-skip">No thanks</button>
  </div>
</div>
<style>
  .vcard-modal { position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:10000; padding:16px; }
  .vcard-modal-content { background:#fff; border-radius:12px; padding:32px 28px 24px; max-width:420px; width:100%; text-align:center; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.3); font-family:'DM Sans',sans-serif; }
  .vcard-close { position:absolute; top:8px; right:12px; background:none; border:none; font-size:28px; color:#888; cursor:pointer; line-height:1; padding:4px 8px; }
  .vcard-close:hover { color:#333; }
  .vcard-icon { font-size:40px; margin-bottom:8px; }
  .vcard-modal-content h2 { color:#001F5B; margin:0 0 12px; font-size:22px; font-weight:700; }
  .vcard-modal-content p { color:#555; margin:0 0 20px; line-height:1.5; }
  .vcard-download-btn { display:inline-block; background:#001F5B; color:#fff !important; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:600; margin-bottom:12px; }
  .vcard-download-btn:hover { background:#002d7a; }
  .vcard-skip { background:none; border:none; color:#888; font-size:14px; cursor:pointer; display:block; margin:0 auto; padding:4px 8px; }
  .vcard-skip:hover { color:#555; }
</style>
<script>
  function vcardClose() { document.getElementById('vcardModal')?.remove(); }
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') vcardClose(); });
</script>
@endif
