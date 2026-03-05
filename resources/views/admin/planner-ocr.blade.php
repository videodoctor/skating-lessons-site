@extends('layouts.admin')
@section('title', 'Planner OCR — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .upload-zone{border:2.5px dashed #bfdbfe;border-radius:12px;background:#eff6ff;text-align:center;padding:3rem 2rem;cursor:pointer;transition:all .2s;}
  .upload-zone:hover,.upload-zone.drag-over{border-color:var(--navy);background:#dbeafe;}
  .upload-zone input{display:none;}
  .preview-img{max-width:100%;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.15);}
  .ocr-result{background:#fff;border-radius:10px;border:1.5px solid #e5eaf2;padding:1.5rem;}
  .lesson-card{background:#f8fafc;border:1.5px solid #e5eaf2;border-radius:8px;padding:1rem;margin-bottom:.75rem;position:relative;}
  .lesson-card.match-found{border-color:#a7f3d0;background:#ecfdf5;}
  .lesson-card.no-match{border-color:#fde68a;background:#fffbeb;}
  .match-badge{position:absolute;top:.6rem;right:.75rem;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;}
  .badge-match{background:#d1fae5;color:#065f46;}
  .badge-unmatched{background:#fef3c7;color:#92400e;}
  .btn-primary{background:var(--navy);color:#fff;padding:.75rem 1.75rem;border-radius:8px;font-weight:600;border:none;cursor:pointer;transition:background .2s;font-size:.95rem;}
  .btn-primary:hover{background:var(--red);}
  .btn-primary:disabled{background:#9ca3af;cursor:not-allowed;}
  .spin{display:inline-block;animation:spin 1s linear infinite;}
  @keyframes spin{to{transform:rotate(360deg)}}
  .confidence-bar{height:4px;border-radius:2px;background:#e5e7eb;margin-top:4px;}
  .confidence-fill{height:100%;border-radius:2px;background:#10b981;transition:width .5s;}
</style>

<div class="flex justify-between items-center mb-6">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy)">Planner OCR</h1>
    <p class="text-gray-500 text-sm">Photograph a page from Kristine's written planner and extract lessons to cross-reference with online bookings.</p>
  </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <!-- Upload panel -->
  <div>
    <div class="ocr-result mb-4">
      <h2 class="font-bold text-gray-800 mb-3">📷 Upload Planner Photo</h2>
      <div class="upload-zone" id="upload-zone" onclick="document.getElementById('photo-input').click()"
           ondragover="event.preventDefault();this.classList.add('drag-over')"
           ondragleave="this.classList.remove('drag-over')"
           ondrop="handleDrop(event)">
        <input type="file" id="photo-input" accept="image/*" onchange="handleFile(this.files[0])">
        <div id="upload-prompt">
          <div style="font-size:3rem" class="mb-3">📋</div>
          <p class="font-semibold text-blue-800 text-lg">Drop photo here or tap to browse</p>
          <p class="text-gray-500 text-sm mt-1">Supports JPG, PNG, HEIC from iPhone camera</p>
        </div>
        <img id="preview-img" class="preview-img hidden" alt="Planner preview">
      </div>

      <div class="mt-4">
        <label class="block font-semibold text-gray-700 text-sm mb-2">Context hint <span class="font-normal text-gray-400">(optional but helps)</span></label>
        <input type="text" id="context-hint" placeholder="e.g. 'March 2026, Creve Coeur rink, times are lesson start times'"
               class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-400">
      </div>

      <button onclick="runOCR()" id="run-btn" class="btn-primary mt-4 w-full" disabled>
        <span id="btn-label">Extract Lessons from Photo</span>
      </button>
    </div>

    <!-- Tips -->
    <div class="ocr-result">
      <h3 class="font-bold text-gray-700 mb-2 text-sm">📸 Tips for Best Results</h3>
      <ul class="text-sm text-gray-500 space-y-1">
        <li>• Flat surface, good lighting, no shadows</li>
        <li>• Keep the page fully in frame</li>
        <li>• Include dates / day labels in the shot</li>
        <li>• Works best with one page at a time</li>
        <li>• Times, names, and rink notes will be extracted</li>
      </ul>
    </div>
  </div>

  <!-- Results panel -->
  <div>
    <div class="ocr-result">
      <div class="flex justify-between items-center mb-3">
        <h2 class="font-bold text-gray-800">📊 Extracted Lessons</h2>
        <span id="match-summary" class="text-xs text-gray-400"></span>
      </div>

      <div id="results-placeholder" class="text-center py-12 text-gray-300">
        <div style="font-size:3rem" class="mb-2">✍️</div>
        <p>Upload and extract a planner photo to see results here</p>
      </div>

      <div id="results-container" class="hidden">
        <!-- Raw OCR text -->
        <div class="mb-4">
          <button onclick="toggleRaw()" class="text-xs text-gray-400 hover:text-gray-600">Show raw OCR text ▾</button>
          <pre id="raw-text" class="hidden bg-gray-50 rounded p-3 text-xs text-gray-600 mt-2 whitespace-pre-wrap max-h-32 overflow-auto"></pre>
        </div>

        <div id="lesson-cards"></div>

        <div class="mt-4 pt-4 border-t border-gray-100 flex gap-3">
          <button onclick="importMatched()" id="import-btn" class="btn-primary text-sm" style="padding:.6rem 1.2rem">
            ✓ Mark Matched as Reviewed
          </button>
          <button onclick="exportCSV()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
            Export CSV
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let extractedLessons = [];
let imageBase64 = '';

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('upload-zone').classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) handleFile(file);
}

function handleFile(file) {
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    imageBase64 = e.target.result;
    document.getElementById('upload-prompt').classList.add('hidden');
    const img = document.getElementById('preview-img');
    img.src = imageBase64;
    img.classList.remove('hidden');
    document.getElementById('run-btn').disabled = false;
    document.getElementById('btn-label').textContent = 'Extract Lessons from Photo';
  };
  reader.readAsDataURL(file);
}

async function runOCR() {
  const btn = document.getElementById('run-btn');
  const label = document.getElementById('btn-label');
  btn.disabled = true;
  label.innerHTML = '<span class="spin">⏳</span> Analyzing handwriting…';

  const hint = document.getElementById('context-hint').value;
  const base64Data = imageBase64.split(',')[1];
  const mediaType = imageBase64.split(';')[0].split(':')[1];

  const systemPrompt = `You are an expert at reading handwritten planners and appointment books. 
Extract all lesson/appointment entries from the image. 
Return ONLY valid JSON with this structure:
{
  "raw_text": "verbatim transcription of all text visible",
  "lessons": [
    {
      "date": "YYYY-MM-DD or partial like 'Mar 5' if year unclear",
      "time": "HH:MM 24h or '10am' format as written",
      "client_name": "name if visible, else null",
      "rink": "rink name if visible, else null",
      "notes": "any other notes",
      "confidence": 0.0-1.0
    }
  ]
}
If you can't determine a field, use null. Do not include markdown backticks, just raw JSON.`;

  const userPrompt = hint
    ? `Extract all lessons from this planner page. Context: ${hint}`
    : `Extract all lessons from this planner page.`;

  try {
    const response = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'claude-sonnet-4-20250514',
        max_tokens: 1500,
        system: systemPrompt,
        messages: [{
          role: 'user',
          content: [
            { type: 'image', source: { type: 'base64', media_type: mediaType, data: base64Data } },
            { type: 'text', text: userPrompt }
          ]
        }]
      })
    });

    const data = await response.json();
    const text = data.content.map(i => i.text || '').join('');
    const clean = text.replace(/```json|```/g, '').trim();
    const parsed = JSON.parse(clean);

    extractedLessons = parsed.lessons || [];
    document.getElementById('raw-text').textContent = parsed.raw_text || '(no raw text returned)';
    renderResults();
  } catch (err) {
    alert('OCR failed: ' + err.message);
  } finally {
    btn.disabled = false;
    label.textContent = 'Re-Extract';
  }
}

function renderResults() {
  const bookings = @json($recentBookings ?? []);
  document.getElementById('results-placeholder').classList.add('hidden');
  document.getElementById('results-container').classList.remove('hidden');

  let html = '';
  let matchCount = 0;

  extractedLessons.forEach((lesson, i) => {
    const match = findMatch(lesson, bookings);
    if (match) matchCount++;
    const cls = match ? 'match-found' : 'no-match';
    const badge = match ? '<span class="match-badge badge-match">✓ Matched</span>' : '<span class="match-badge badge-unmatched">⚠ Unmatched</span>';
    const confPct = Math.round((lesson.confidence || 0.5) * 100);

    html += `<div class="lesson-card ${cls}" data-index="${i}">
      ${badge}
      <div class="grid grid-cols-2 gap-2 text-sm">
        <div><span class="text-gray-400 text-xs">Date</span><div class="font-semibold">${lesson.date || '?'}</div></div>
        <div><span class="text-gray-400 text-xs">Time</span><div class="font-semibold">${lesson.time || '?'}</div></div>
        <div><span class="text-gray-400 text-xs">Client</span><div class="font-semibold">${lesson.client_name || '—'}</div></div>
        <div><span class="text-gray-400 text-xs">Rink</span><div class="font-semibold">${lesson.rink || '—'}</div></div>
      </div>
      ${lesson.notes ? `<div class="text-xs text-gray-500 mt-2">📝 ${lesson.notes}</div>` : ''}
      ${match ? `<div class="text-xs text-green-700 mt-2">→ Matches booking: <strong>${match.client_name}</strong> (${match.status})</div>` : ''}
      <div class="mt-2"><div class="text-xs text-gray-400">Confidence: ${confPct}%</div><div class="confidence-bar"><div class="confidence-fill" style="width:${confPct}%"></div></div></div>
    </div>`;
  });

  if (!html) html = '<p class="text-gray-400 text-sm">No lessons found in this image. Try a clearer photo or add a context hint.</p>';

  document.getElementById('lesson-cards').innerHTML = html;
  document.getElementById('match-summary').textContent = `${extractedLessons.length} extracted · ${matchCount} matched`;
}

function findMatch(lesson, bookings) {
  if (!lesson.date || !bookings.length) return null;
  return bookings.find(b => {
    const dateMatch = b.date && b.date.includes(lesson.date.replace(/[^0-9-]/g,'').substring(0,7));
    const nameMatch = lesson.client_name && b.client_name &&
      b.client_name.toLowerCase().includes(lesson.client_name.toLowerCase().split(' ')[0]);
    return dateMatch || nameMatch;
  });
}

function toggleRaw() {
  const el = document.getElementById('raw-text');
  el.classList.toggle('hidden');
}

function exportCSV() {
  const rows = [['Date','Time','Client','Rink','Notes','Confidence']];
  extractedLessons.forEach(l => rows.push([l.date||'',l.time||'',l.client_name||'',l.rink||'',l.notes||'',l.confidence||'']));
  const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'planner_lessons.csv';
  a.click();
}

function importMatched() {
  alert('Matched lessons marked as reviewed. In a full implementation this would update a review_log table.');
}
</script>
@endpush
@endsection
