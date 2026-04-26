@extends('layouts.app')
@section('title', 'My Profile — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <a href="/portal" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> Portal
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">My Profile</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container" style="max-width:600px;">

        @if(session('success'))
        <div style="background:#f0fdf4; border:1px solid #7bba56; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
            <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> {{ session('success') }}
        </div>
        @endif

        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem;">
            <form method="POST" action="/portal/profile" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Full name</label>
                    <div style="font-size:18px; font-weight:600; color:#262c39;">{{ $player->memberNameFirst }} {{ $player->memberNameLast }}</div>
                    <div style="font-size:12px; color:#aaa; margin-top:4px;">To change your name please contact admin.</div>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Email address</label>
                    <input type="email" name="email" value="{{ $player->memberEmail }}" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Mobile number</label>
                    <input type="tel" name="mobile" value="{{ $player->memberPhoneMobile ?? '' }}" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;" placeholder="e.g. 0412 345 678">
                </div>
                {{-- Profile photo --}}
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Profile photo</label>
                    @if($player->memberPhoto)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($player->memberPhoto) }}" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #e8e8e8;">
                    </div>
                    @else
                    <div style="width:80px; height:80px; border-radius:50%; background:#f4f4f4; display:flex; align-items:center; justify-content:center; margin-bottom:8px;">
                        <i class="fa-solid fa-user-large" style="font-size:32px; color:#ccc;"></i>
                    </div>
                    @endif
                    <input type="file" name="photo" accept="image/*" style="font-size:14px; color:#444;">
                    <div style="font-size:12px; color:#aaa; margin-top:4px; line-height:1.6;">
                        JPG or PNG. <strong style="color:#888;">Best size: 400×400px square.</strong> Your face should be centred. Used on your public player profile.
                    </div>
                </div>

                {{-- FIFA card photo --}}
                <div style="margin-bottom:2rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Player card photo</label>
                    @if($player->memberPhotoCard)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($player->memberPhotoCard) }}" style="width:80px; height:100px; border-radius:8px; object-fit:cover; border:2px solid #e8e8e8;">
                    </div>
                    @else
                    <div style="width:80px; height:100px; border-radius:8px; background:#f4f4f4; display:flex; align-items:center; justify-content:center; margin-bottom:8px;">
                        <i class="fa-solid fa-id-card" style="font-size:32px; color:#ccc;"></i>
                    </div>
                    @endif
                    <input type="file" name="photo_card" accept="image/*" style="font-size:14px; color:#444;">
                    <div style="font-size:12px; color:#aaa; margin-top:4px; line-height:1.6;">
                        JPG or PNG. <strong style="color:#888;">Best size: 400×500px portrait.</strong> Stand against a plain background with your full upper body visible. This photo appears on your player card.
                    </div>
                </div>

                <button type="submit" style="background:#262c39; color:#fff; border:none; border-radius:8px; padding:12px 24px; font-size:15px; font-weight:600; cursor:pointer;">
                    Save changes
                </button>
            </form>
        </div>

        <div style="margin-top:2rem; text-align:center;">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" style="background:none; border:none; color:#aaa; font-size:14px; cursor:pointer; text-decoration:underline;">
                    Log out
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
