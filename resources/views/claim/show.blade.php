@extends('layouts.app')

@section('title', 'Claim your account — Soccer Dads')

@section('content')

<div style="max-width:640px; margin:3rem auto; padding:0 1.5rem;">

    {{-- Header --}}
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; margin-bottom:0.25rem;">
            Hey {{ $member->memberNameFirst }}!
        </h1>
        <p style="font-size:14px; color:#888;">Claim your Soccer Dads account</p>
    </div>

    <div style="background:#262c39; color:#fff; border-radius:16px; padding:1.25rem 1.5rem; margin-bottom:1.5rem; font-size:14px; line-height:1.6;">
        <i class="fa-solid fa-circle-info" style="color:rgba(255,255,255,0.6);"></i>
        Finish setting up your account so we can keep your details up to date and contact someone if you ever get hurt on the pitch.
    </div>

    @if($errors->any())
    <div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
        <i class="fa-solid fa-circle-exclamation" style="color:#e24b4a;"></i>
        <strong>Please fix the following:</strong>
        <ul style="margin:8px 0 0 24px;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="/claim/{{ $member->memberCode }}" enctype="multipart/form-data">
        @csrf

        {{-- Section 1: Personal details --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1rem;">
            <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1.25rem;">Your details</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">First name</label>
                    <input type="text" name="firstName" value="{{ old('firstName', $member->memberNameFirst) }}" required
                        style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">Last name</label>
                    <input type="text" name="lastName" value="{{ old('lastName', $member->memberNameLast) }}" required
                        style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">Email address</label>
                <input type="email" name="email" value="{{ old('email', $member->memberEmail) }}" required
                    style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">Mobile</label>
                <input type="tel" name="mobile" value="{{ old('mobile', $member->memberPhoneMobile) }}" required placeholder="e.g. 0412 345 678"
                    style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">Date of birth</label>
                    <input type="date" name="birthday" value="{{ old('birthday', $member->memberBirthday) }}" required
                        style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:6px;">Country of origin</label>
                    @php $selectedCountry = old('country', $member->memberCountry ?: 'AU'); @endphp
                    <select name="country" required
                        style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:12px 14px; font-size:15px; color:#262c39; outline:none; background:#fff;">
                        @foreach([
                            'AU'=>'🇦🇺 Australia','AF'=>'🇦🇫 Afghanistan','AL'=>'🇦🇱 Albania','DZ'=>'🇩🇿 Algeria',
                            'AR'=>'🇦🇷 Argentina','AT'=>'🇦🇹 Austria','BE'=>'🇧🇪 Belgium','BR'=>'🇧🇷 Brazil',
                            'BG'=>'🇧🇬 Bulgaria','CA'=>'🇨🇦 Canada','CL'=>'🇨🇱 Chile','CN'=>'🇨🇳 China',
                            'CO'=>'🇨🇴 Colombia','HR'=>'🇭🇷 Croatia','CZ'=>'🇨🇿 Czech Republic','DK'=>'🇩🇰 Denmark',
                            'EG'=>'🇪🇬 Egypt','ET'=>'🇪🇹 Ethiopia','FI'=>'🇫🇮 Finland','FR'=>'🇫🇷 France',
                            'DE'=>'🇩🇪 Germany','GH'=>'🇬🇭 Ghana','GR'=>'🇬🇷 Greece','HU'=>'🇭🇺 Hungary',
                            'IN'=>'🇮🇳 India','ID'=>'🇮🇩 Indonesia','IR'=>'🇮🇷 Iran','IQ'=>'🇮🇶 Iraq',
                            'IE'=>'🇮🇪 Ireland','IL'=>'🇮🇱 Israel','IT'=>'🇮🇹 Italy','JP'=>'🇯🇵 Japan',
                            'JO'=>'🇯🇴 Jordan','KE'=>'🇰🇪 Kenya','KR'=>'🇰🇷 Korea','LB'=>'🇱🇧 Lebanon',
                            'MY'=>'🇲🇾 Malaysia','MX'=>'🇲🇽 Mexico','NL'=>'🇳🇱 Netherlands','NZ'=>'🇳🇿 New Zealand',
                            'NG'=>'🇳🇬 Nigeria','NO'=>'🇳🇴 Norway','PK'=>'🇵🇰 Pakistan','PE'=>'🇵🇪 Peru',
                            'PH'=>'🇵🇭 Philippines','PL'=>'🇵🇱 Poland','PT'=>'🇵🇹 Portugal','RO'=>'🇷🇴 Romania',
                            'RU'=>'🇷🇺 Russia','SA'=>'🇸🇦 Saudi Arabia','ZA'=>'🇿🇦 South Africa','ES'=>'🇪🇸 Spain',
                            'LK'=>'🇱🇰 Sri Lanka','SE'=>'🇸🇪 Sweden','CH'=>'🇨🇭 Switzerland','TH'=>'🇹🇭 Thailand',
                            'TN'=>'🇹🇳 Tunisia','TR'=>'🇹🇷 Turkey','UA'=>'🇺🇦 Ukraine','AE'=>'🇦🇪 United Arab Emirates',
                            'GB'=>'🇬🇧 United Kingdom','US'=>'🇺🇸 United States','VN'=>'🇻🇳 Vietnam','ZW'=>'🇿🇼 Zimbabwe',
                        ] as $code => $label)
                            <option value="{{ $code }}" {{ $selectedCountry === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2: Profile photo --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1rem;">
            <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">Profile photo</h2>
            <p style="font-size:13px; color:#888; margin-bottom:1rem;">Optional — JPG, PNG or WebP, up to 5MB. Best size 400×400 square.</p>

            @if($member->memberPhoto)
            <div style="margin-bottom:8px;">
                <img src="{{ Storage::url($member->memberPhoto) }}" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #e8e8e8;">
            </div>
            @else
            <div style="width:80px; height:80px; border-radius:50%; background:#f4f4f4; display:flex; align-items:center; justify-content:center; margin-bottom:8px;">
                <i class="fa-solid fa-user-large" style="font-size:32px; color:#ccc;"></i>
            </div>
            @endif
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="font-size:14px; color:#444;">
        </div>

        {{-- Section 3: Emergency contacts --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1.5rem;" id="contactsCard">
            <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">Emergency contacts</h2>
            <p style="font-size:13px; color:#888; margin-bottom:1rem;">Add at least two people we can call if something happens to you on the pitch. Mark one as your primary contact.</p>

            <div id="contactsList">
                @php
                    $oldContacts = old('contacts', [['name'=>'','relationship'=>'','phone'=>'','email'=>''], ['name'=>'','relationship'=>'','phone'=>'','email'=>'']]);
                    $oldPrimary  = (int) old('primaryContact', 0);
                @endphp
                @foreach($oldContacts as $i => $c)
                <div class="contact-block" data-index="{{ $i }}" style="border:1px solid #e8e8e8; border-radius:12px; padding:1rem; margin-bottom:12px; background:#fafafa;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <strong style="font-size:14px; color:#262c39;">Contact <span class="contact-number">{{ $i + 1 }}</span></strong>
                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:#262c39; cursor:pointer;">
                            <input type="radio" name="primaryContact" value="{{ $i }}" {{ $oldPrimary === $i ? 'checked' : '' }}>
                            Primary
                        </label>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                        <input type="text" name="contacts[{{ $i }}][name]" value="{{ $c['name'] ?? '' }}" placeholder="Name" required
                            style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                        <input type="text" name="contacts[{{ $i }}][relationship]" value="{{ $c['relationship'] ?? '' }}" placeholder="Relationship (e.g. partner)"
                            style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <input type="tel" name="contacts[{{ $i }}][phone]" value="{{ $c['phone'] ?? '' }}" placeholder="Phone" required
                            style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                        <input type="email" name="contacts[{{ $i }}][email]" value="{{ $c['email'] ?? '' }}" placeholder="Email (optional)"
                            style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                    </div>
                    @if($i >= 2)
                    <button type="button" onclick="removeContact(this)" style="margin-top:10px; background:none; border:none; color:#e24b4a; font-size:13px; cursor:pointer; padding:0;">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                    @endif
                </div>
                @endforeach
            </div>

            <button type="button" onclick="addContact()" style="background:none; border:1px dashed #ccc; color:#666; border-radius:8px; padding:10px 14px; font-size:14px; cursor:pointer; width:100%;">
                <i class="fa-solid fa-plus"></i> Add another contact
            </button>
        </div>

        <button type="submit" style="width:100%; background:#262c39; color:#fff; border:none; border-radius:12px; padding:16px; font-size:16px; font-weight:600; cursor:pointer;">
            Claim my account
        </button>
    </form>
</div>

<script>
let contactIndex = {{ count($oldContacts) }};

function addContact() {
    const i = contactIndex++;
    const block = document.createElement('div');
    block.className = 'contact-block';
    block.dataset.index = i;
    block.style.cssText = 'border:1px solid #e8e8e8; border-radius:12px; padding:1rem; margin-bottom:12px; background:#fafafa;';
    block.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong style="font-size:14px; color:#262c39;">Contact <span class="contact-number"></span></strong>
            <label style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:#262c39; cursor:pointer;">
                <input type="radio" name="primaryContact" value="${i}"> Primary
            </label>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
            <input type="text" name="contacts[${i}][name]" placeholder="Name" required style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
            <input type="text" name="contacts[${i}][relationship]" placeholder="Relationship (e.g. partner)" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <input type="tel" name="contacts[${i}][phone]" placeholder="Phone" required style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
            <input type="email" name="contacts[${i}][email]" placeholder="Email (optional)" style="width:100%; border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
        </div>
        <button type="button" onclick="removeContact(this)" style="margin-top:10px; background:none; border:none; color:#e24b4a; font-size:13px; cursor:pointer; padding:0;">
            <i class="fa-solid fa-trash"></i> Remove
        </button>
    `;
    document.getElementById('contactsList').appendChild(block);
    renumberContacts();
}

function removeContact(btn) {
    btn.closest('.contact-block').remove();
    renumberContacts();
}

function renumberContacts() {
    document.querySelectorAll('#contactsList .contact-block').forEach((block, idx) => {
        const numSpan = block.querySelector('.contact-number');
        if (numSpan) numSpan.textContent = (idx + 1);
    });
}
renumberContacts();
</script>

@endsection
