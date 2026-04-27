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
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Birthday</label>
                    @if($player->memberBirthday)
                        <div style="font-size:18px; font-weight:600; color:#262c39;">{{ \Carbon\Carbon::parse($player->memberBirthday)->format('d/m/Y') }}</div>
                        <div style="font-size:12px; color:#aaa; margin-top:4px;">To update your birthday, please contact admin.</div>
                    @else
                        <input type="date" name="birthday" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none; margin-bottom:8px;">
                        <div style="font-size:12px; color:#aaa; margin-bottom:12px;">Enter your birthday — this can only be set once.</div>
                        <button type="submit" formaction="/portal/birthday" style="background:#262c39; color:#fff; border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">
                            Save birthday
                        </button>
                    @endif
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Country of origin</label>
                    <select name="country" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none; background:#fff;">
                        <option value="AU" {{ ($player->memberCountry ?? 'AU') == 'AU' ? 'selected' : '' }}>🇦🇺 Australia</option>
                        <option value="AF" {{ ($player->memberCountry ?? '') == 'AF' ? 'selected' : '' }}>🇦🇫 Afghanistan</option>
                        <option value="AL" {{ ($player->memberCountry ?? '') == 'AL' ? 'selected' : '' }}>🇦🇱 Albania</option>
                        <option value="DZ" {{ ($player->memberCountry ?? '') == 'DZ' ? 'selected' : '' }}>🇩🇿 Algeria</option>
                        <option value="AR" {{ ($player->memberCountry ?? '') == 'AR' ? 'selected' : '' }}>🇦🇷 Argentina</option>
                        <option value="AT" {{ ($player->memberCountry ?? '') == 'AT' ? 'selected' : '' }}>🇦🇹 Austria</option>
                        <option value="BE" {{ ($player->memberCountry ?? '') == 'BE' ? 'selected' : '' }}>🇧🇪 Belgium</option>
                        <option value="BR" {{ ($player->memberCountry ?? '') == 'BR' ? 'selected' : '' }}>🇧🇷 Brazil</option>
                        <option value="BG" {{ ($player->memberCountry ?? '') == 'BG' ? 'selected' : '' }}>🇧🇬 Bulgaria</option>
                        <option value="CA" {{ ($player->memberCountry ?? '') == 'CA' ? 'selected' : '' }}>🇨🇦 Canada</option>
                        <option value="CL" {{ ($player->memberCountry ?? '') == 'CL' ? 'selected' : '' }}>🇨🇱 Chile</option>
                        <option value="CN" {{ ($player->memberCountry ?? '') == 'CN' ? 'selected' : '' }}>🇨🇳 China</option>
                        <option value="CO" {{ ($player->memberCountry ?? '') == 'CO' ? 'selected' : '' }}>🇨🇴 Colombia</option>
                        <option value="HR" {{ ($player->memberCountry ?? '') == 'HR' ? 'selected' : '' }}>🇭🇷 Croatia</option>
                        <option value="CZ" {{ ($player->memberCountry ?? '') == 'CZ' ? 'selected' : '' }}>🇨🇿 Czech Republic</option>
                        <option value="DK" {{ ($player->memberCountry ?? '') == 'DK' ? 'selected' : '' }}>🇩🇰 Denmark</option>
                        <option value="EG" {{ ($player->memberCountry ?? '') == 'EG' ? 'selected' : '' }}>🇪🇬 Egypt</option>
                        <option value="ET" {{ ($player->memberCountry ?? '') == 'ET' ? 'selected' : '' }}>🇪🇹 Ethiopia</option>
                        <option value="FI" {{ ($player->memberCountry ?? '') == 'FI' ? 'selected' : '' }}>🇫🇮 Finland</option>
                        <option value="FR" {{ ($player->memberCountry ?? '') == 'FR' ? 'selected' : '' }}>🇫🇷 France</option>
                        <option value="DE" {{ ($player->memberCountry ?? '') == 'DE' ? 'selected' : '' }}>🇩🇪 Germany</option>
                        <option value="GH" {{ ($player->memberCountry ?? '') == 'GH' ? 'selected' : '' }}>🇬🇭 Ghana</option>
                        <option value="GR" {{ ($player->memberCountry ?? '') == 'GR' ? 'selected' : '' }}>🇬🇷 Greece</option>
                        <option value="HU" {{ ($player->memberCountry ?? '') == 'HU' ? 'selected' : '' }}>🇭🇺 Hungary</option>
                        <option value="IN" {{ ($player->memberCountry ?? '') == 'IN' ? 'selected' : '' }}>🇮🇳 India</option>
                        <option value="ID" {{ ($player->memberCountry ?? '') == 'ID' ? 'selected' : '' }}>🇮🇩 Indonesia</option>
                        <option value="IR" {{ ($player->memberCountry ?? '') == 'IR' ? 'selected' : '' }}>🇮🇷 Iran</option>
                        <option value="IQ" {{ ($player->memberCountry ?? '') == 'IQ' ? 'selected' : '' }}>🇮🇶 Iraq</option>
                        <option value="IE" {{ ($player->memberCountry ?? '') == 'IE' ? 'selected' : '' }}>🇮🇪 Ireland</option>
                        <option value="IL" {{ ($player->memberCountry ?? '') == 'IL' ? 'selected' : '' }}>🇮🇱 Israel</option>
                        <option value="IT" {{ ($player->memberCountry ?? '') == 'IT' ? 'selected' : '' }}>🇮🇹 Italy</option>
                        <option value="JP" {{ ($player->memberCountry ?? '') == 'JP' ? 'selected' : '' }}>🇯🇵 Japan</option>
                        <option value="JO" {{ ($player->memberCountry ?? '') == 'JO' ? 'selected' : '' }}>🇯🇴 Jordan</option>
                        <option value="KE" {{ ($player->memberCountry ?? '') == 'KE' ? 'selected' : '' }}>🇰🇪 Kenya</option>
                        <option value="KR" {{ ($player->memberCountry ?? '') == 'KR' ? 'selected' : '' }}>🇰🇷 Korea</option>
                        <option value="LB" {{ ($player->memberCountry ?? '') == 'LB' ? 'selected' : '' }}>🇱🇧 Lebanon</option>
                        <option value="MY" {{ ($player->memberCountry ?? '') == 'MY' ? 'selected' : '' }}>🇲🇾 Malaysia</option>
                        <option value="MX" {{ ($player->memberCountry ?? '') == 'MX' ? 'selected' : '' }}>🇲🇽 Mexico</option>
                        <option value="NL" {{ ($player->memberCountry ?? '') == 'NL' ? 'selected' : '' }}>🇳🇱 Netherlands</option>
                        <option value="NZ" {{ ($player->memberCountry ?? '') == 'NZ' ? 'selected' : '' }}>🇳🇿 New Zealand</option>
                        <option value="NG" {{ ($player->memberCountry ?? '') == 'NG' ? 'selected' : '' }}>🇳🇬 Nigeria</option>
                        <option value="NO" {{ ($player->memberCountry ?? '') == 'NO' ? 'selected' : '' }}>🇳🇴 Norway</option>
                        <option value="PK" {{ ($player->memberCountry ?? '') == 'PK' ? 'selected' : '' }}>🇵🇰 Pakistan</option>
                        <option value="PE" {{ ($player->memberCountry ?? '') == 'PE' ? 'selected' : '' }}>🇵🇪 Peru</option>
                        <option value="PH" {{ ($player->memberCountry ?? '') == 'PH' ? 'selected' : '' }}>🇵🇭 Philippines</option>
                        <option value="PL" {{ ($player->memberCountry ?? '') == 'PL' ? 'selected' : '' }}>🇵🇱 Poland</option>
                        <option value="PT" {{ ($player->memberCountry ?? '') == 'PT' ? 'selected' : '' }}>🇵🇹 Portugal</option>
                        <option value="RO" {{ ($player->memberCountry ?? '') == 'RO' ? 'selected' : '' }}>🇷🇴 Romania</option>
                        <option value="RU" {{ ($player->memberCountry ?? '') == 'RU' ? 'selected' : '' }}>🇷🇺 Russia</option>
                        <option value="SA" {{ ($player->memberCountry ?? '') == 'SA' ? 'selected' : '' }}>🇸🇦 Saudi Arabia</option>
                        <option value="ZA" {{ ($player->memberCountry ?? '') == 'ZA' ? 'selected' : '' }}>🇿🇦 South Africa</option>
                        <option value="ES" {{ ($player->memberCountry ?? '') == 'ES' ? 'selected' : '' }}>🇪🇸 Spain</option>
                        <option value="LK" {{ ($player->memberCountry ?? '') == 'LK' ? 'selected' : '' }}>🇱🇰 Sri Lanka</option>
                        <option value="SE" {{ ($player->memberCountry ?? '') == 'SE' ? 'selected' : '' }}>🇸🇪 Sweden</option>
                        <option value="CH" {{ ($player->memberCountry ?? '') == 'CH' ? 'selected' : '' }}>🇨🇭 Switzerland</option>
                        <option value="TH" {{ ($player->memberCountry ?? '') == 'TH' ? 'selected' : '' }}>🇹🇭 Thailand</option>
                        <option value="TN" {{ ($player->memberCountry ?? '') == 'TN' ? 'selected' : '' }}>🇹🇳 Tunisia</option>
                        <option value="TR" {{ ($player->memberCountry ?? '') == 'TR' ? 'selected' : '' }}>🇹🇷 Turkey</option>
                        <option value="UA" {{ ($player->memberCountry ?? '') == 'UA' ? 'selected' : '' }}>🇺🇦 Ukraine</option>
                        <option value="AE" {{ ($player->memberCountry ?? '') == 'AE' ? 'selected' : '' }}>🇦🇪 United Arab Emirates</option>
                        <option value="GB" {{ ($player->memberCountry ?? '') == 'GB' ? 'selected' : '' }}>🇬🇧 United Kingdom</option>
                        <option value="US" {{ ($player->memberCountry ?? '') == 'US' ? 'selected' : '' }}>🇺🇸 United States</option>
                        <option value="VN" {{ ($player->memberCountry ?? '') == 'VN' ? 'selected' : '' }}>🇻🇳 Vietnam</option>
                        <option value="ZW" {{ ($player->memberCountry ?? '') == 'ZW' ? 'selected' : '' }}>🇿🇼 Zimbabwe</option>
                    </select>
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
