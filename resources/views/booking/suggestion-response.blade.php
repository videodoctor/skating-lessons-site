@extends('layouts.app')
@section('title', $success ? 'Lesson Confirmed!' : 'Response Recorded')
@section('content')
<div style="max-width:540px;margin:5rem auto;padding:0 1.5rem;text-align:center;">
  <div style="background:#fff;border-radius:14px;padding:3rem 2.5rem;box-shadow:0 8px 40px rgba(0,31,91,.1);">

    @if($success)
      <div style="font-size:4rem;margin-bottom:1rem;">✅</div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:#001F5B;margin:0 0 1rem;">Lesson Confirmed!</h1>
      <p style="color:#374151;font-size:1rem;line-height:1.7;margin-bottom:1.5rem;">{{ $message }}</p>
      @if(isset($booking))
      <div style="background:#eff6ff;border-radius:8px;padding:1rem;margin-bottom:1.5rem;font-size:.88rem;color:#374151;">
        <strong>Confirmation #:</strong> {{ $booking->confirmation_code }}
      </div>
      @endif
    @elseif(isset($declined) && $declined)
      <div style="font-size:4rem;margin-bottom:1rem;">👍</div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:#001F5B;margin:0 0 1rem;">Got It!</h1>
      <p style="color:#374151;font-size:1rem;line-height:1.7;margin-bottom:1.5rem;">{{ $message }}</p>
    @else
      <div style="font-size:4rem;margin-bottom:1rem;">⚠️</div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:#001F5B;margin:0 0 1rem;">Oops!</h1>
      <p style="color:#374151;font-size:1rem;line-height:1.7;margin-bottom:1.5rem;">{{ $message }}</p>
    @endif

    <a href="https://kristineskates.com" style="display:inline-block;background:#001F5B;color:#fff;padding:.75rem 2rem;border-radius:7px;font-weight:700;text-decoration:none;font-size:.9rem;">
      Back to Kristine Skates →
    </a>
  </div>
</div>
@endsection
