@extends('layouts.admin')
@section('title', 'Notifications — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .tmpl-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;margin-bottom:.75rem;overflow:hidden;}
  .tmpl-header{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;cursor:pointer;user-select:none;}
  .tmpl-header:hover{background:#f8fafc;}
  .tmpl-title{font-weight:700;font-size:.88rem;color:#374151;display:flex;align-items:center;gap:.5rem;}
  .tmpl-badge{font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:10px;text-transform:uppercase;letter-spacing:.04em;}
  .tmpl-badge.sms{background:#dbeafe;color:#1e40af;}
  .tmpl-badge.email{background:#fce7f3;color:#be185d;}
  .tmpl-body{display:none;padding:1rem;border-top:1px solid #f3f4f6;}
  .tmpl-body.open{display:block;}
  .var-chip{display:inline-block;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:4px;padding:1px 6px;font-size:.7rem;font-family:monospace;cursor:pointer;margin:1px;}
  .var-chip:hover{background:#dbeafe;}
  .preview-box{background:#f8fafc;border:1.5px solid #e5eaf2;border-radius:8px;padding:.75rem 1rem;font-size:.85rem;white-space:pre-wrap;word-break:break-word;min-height:40px;}
  .preview-box.sms{background:#dcfce7;border-color:#bbf7d0;font-family:monospace;font-size:.82rem;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Notification Templates</h1>
    <p style="color:#6b7280;font-size:.85rem;">Edit SMS and email message templates. Use <code style="background:#eff6ff;padding:1px 4px;border-radius:3px;font-size:.8rem;">@{{variable}}</code> placeholders for dynamic content.</p>
  </div>
</div>

{{-- SMS Templates --}}
<h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0 0 .75rem;">📱 SMS Templates</h2>
@foreach($smsTemplates as $t)
<div class="tmpl-card">
  <div class="tmpl-header" onclick="this.nextElementSibling.classList.toggle('open')">
    <div class="tmpl-title">
      <span class="tmpl-badge sms">SMS</span>
      {{ $t->label }}
      @if(!$t->is_active)<span style="color:#dc2626;font-size:.72rem;font-weight:700;">DISABLED</span>@endif
    </div>
    <div style="display:flex;gap:.4rem;align-items:center;">
      <span style="font-size:.72rem;color:#9ca3af;">{{ $categories[$t->category] ?? $t->category }}</span>
      <span style="color:#9ca3af;">▾</span>
    </div>
  </div>
  <div class="tmpl-body">
    <form method="POST" action="{{ route('admin.notifications.update', $t) }}">
      @csrf @method('PATCH')
      <div style="margin-bottom:.6rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Message Body</label>
        <textarea name="body" rows="4" style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.85rem;font-family:monospace;resize:vertical;">{{ $t->body }}</textarea>
      </div>
      <div style="margin-bottom:.6rem;">
        <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Available Variables</label>
        <div style="margin-top:3px;">
          @foreach($t->variables ?? [] as $var)
            <span class="var-chip" onclick="insertVar(this, '{{ $var }}')" title="Click to copy">@{{{{ $var }}}}</span>
          @endforeach
        </div>
      </div>
      <div style="margin-bottom:.6rem;">
        <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Preview</label>
        <div class="preview-box sms" id="preview-{{ $t->id }}">
          <em style="color:#9ca3af;">Click "Preview" to see rendered message</em>
        </div>
      </div>
      <div style="display:flex;gap:.4rem;align-items:center;">
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:6px;padding:6px 16px;font-weight:700;font-size:.82rem;cursor:pointer;">Save</button>
        <button type="button" onclick="previewTemplate({{ $t->id }})" style="background:#f3f4f6;color:#374151;border:none;border-radius:6px;padding:6px 16px;font-weight:600;font-size:.82rem;cursor:pointer;">Preview</button>
        <form method="POST" action="{{ route('admin.notifications.toggle', $t) }}" style="display:inline;margin-left:auto;">
          @csrf
          <button type="submit" style="background:{{ $t->is_active ? '#fee2e2' : '#d1fae5' }};color:{{ $t->is_active ? '#991b1b' : '#065f46' }};border:none;border-radius:6px;padding:6px 12px;font-weight:600;font-size:.78rem;cursor:pointer;">
            {{ $t->is_active ? 'Disable' : 'Enable' }}
          </button>
        </form>
      </div>
    </form>
  </div>
</div>
@endforeach

{{-- Email Templates --}}
<h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:1.5rem 0 .75rem;">📧 Email Templates</h2>
@foreach($emailTemplates as $t)
<div class="tmpl-card">
  <div class="tmpl-header" onclick="this.nextElementSibling.classList.toggle('open')">
    <div class="tmpl-title">
      <span class="tmpl-badge email">Email</span>
      {{ $t->label }}
      @if(!$t->is_active)<span style="color:#dc2626;font-size:.72rem;font-weight:700;">DISABLED</span>@endif
    </div>
    <div style="display:flex;gap:.4rem;align-items:center;">
      <span style="font-size:.72rem;color:#9ca3af;">{{ $categories[$t->category] ?? $t->category }}</span>
      <span style="color:#9ca3af;">▾</span>
    </div>
  </div>
  <div class="tmpl-body">
    <form method="POST" action="{{ route('admin.notifications.update', $t) }}">
      @csrf @method('PATCH')
      <div style="margin-bottom:.6rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Subject Line</label>
        <input type="text" name="subject" value="{{ $t->subject }}" style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.85rem;">
      </div>
      <div style="margin-bottom:.6rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Message Body</label>
        <textarea name="body" rows="4" style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.85rem;resize:vertical;">{{ $t->body }}</textarea>
      </div>
      <div style="margin-bottom:.6rem;">
        <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Available Variables</label>
        <div style="margin-top:3px;">
          @foreach($t->variables ?? [] as $var)
            <span class="var-chip" onclick="insertVar(this, '{{ $var }}')" title="Click to copy">@{{{{ $var }}}}</span>
          @endforeach
        </div>
      </div>
      <div style="margin-bottom:.6rem;">
        <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Preview</label>
        <div class="preview-box" id="preview-{{ $t->id }}">
          <em style="color:#9ca3af;">Click "Preview" to see rendered message</em>
        </div>
      </div>
      <div style="display:flex;gap:.4rem;align-items:center;">
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:6px;padding:6px 16px;font-weight:700;font-size:.82rem;cursor:pointer;">Save</button>
        <button type="button" onclick="previewTemplate({{ $t->id }})" style="background:#f3f4f6;color:#374151;border:none;border-radius:6px;padding:6px 16px;font-weight:600;font-size:.82rem;cursor:pointer;">Preview</button>
        <form method="POST" action="{{ route('admin.notifications.toggle', $t) }}" style="display:inline;margin-left:auto;">
          @csrf
          <button type="submit" style="background:{{ $t->is_active ? '#fee2e2' : '#d1fae5' }};color:{{ $t->is_active ? '#991b1b' : '#065f46' }};border:none;border-radius:6px;padding:6px 12px;font-weight:600;font-size:.78rem;cursor:pointer;">
            {{ $t->is_active ? 'Disable' : 'Enable' }}
          </button>
        </form>
      </div>
    </form>
  </div>
</div>
@endforeach

<script>
function previewTemplate(id) {
  fetch('/admin/notifications/' + id + '/preview')
    .then(r => r.json())
    .then(data => {
      var box = document.getElementById('preview-' + id);
      var html = '';
      if (data.subject) html += '<strong>Subject:</strong> ' + data.subject + '\n\n';
      html += data.body;
      box.innerHTML = html;
    });
}

function insertVar(el, varName) {
  var textarea = el.closest('.tmpl-body').querySelector('textarea[name="body"]');
  if (textarea) {
    var pos = textarea.selectionStart;
    var before = textarea.value.substring(0, pos);
    var after = textarea.value.substring(pos);
    textarea.value = before + '{{' + varName + '}}' + after;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = pos + varName.length + 4;
  }
}
</script>
@endsection
