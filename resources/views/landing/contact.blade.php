@extends('layouts.qc-admin')

@section('title', 'Contact | '.$systemName)

@section('content')
@php
    $supportEmail = env('ADMIN_SUPPORT_EMAIL', 'energyconservemgmt@gmail.com');
    $supportPhone = env('ADMIN_SUPPORT_PHONE', '+1 (555) 123-4567');
    $supportPhoneHref = preg_replace('/[^0-9+]/', '', $supportPhone);
    $contactUser = auth()->user();
    $prefillName = old('name', $contactUser?->full_name ?? $contactUser?->name ?? $contactUser?->username ?? '');
    $prefillEmail = old('email', $contactUser?->email ?? '');
@endphp

<div class="contact-page" id="contact">
    <section class="contact-hero" aria-labelledby="contact-title">
        <div class="contact-hero__copy">
            <div class="contact-eyebrow"><span></span> Contact support</div>
            <h1 id="contact-title">Let’s solve your concern <em>together.</em></h1>
            <p>Questions, feedback, or technical concerns about {{ $systemName }}? Give us the details and our support team will help you move forward.</p>

            <div class="contact-hero__tags" aria-label="Support benefits">
                <span><i class="fa-solid fa-circle-check"></i> Direct assistance</span>
                <span><i class="fa-solid fa-shield-halved"></i> Secure inquiry</span>
                <span><i class="fa-solid fa-clock"></i> Timely response</span>
            </div>
        </div>

        <div class="contact-identity" aria-label="Support team">
            <div class="contact-identity__orbit" aria-hidden="true"></div>
            <span class="contact-identity__icon">
                <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }} logo">
            </span>
            <span class="contact-identity__label">LGU EMS support</span>
            <strong>We’re here to help</strong>
            <small>{{ $systemOrganization }}</small>
        </div>
    </section>

    <section class="contact-workspace" aria-label="Contact information and message form">
        <aside class="support-panel">
            <span class="section-kicker">Contact channels</span>
            <h2>Reach the right team.</h2>
            <p class="support-panel__intro">Send your message through the form or contact us directly using the details below.</p>

            <a class="contact-method" href="mailto:{{ $supportEmail }}">
                <span class="contact-method__icon"><i class="fa-solid fa-envelope"></i></span>
                <span class="contact-method__copy">
                    <small>Email support</small>
                    <strong>{{ $supportEmail }}</strong>
                </span>
                <i class="fa-solid fa-arrow-up-right-from-square contact-method__arrow"></i>
            </a>

            <a class="contact-method" href="tel:{{ $supportPhoneHref }}">
                <span class="contact-method__icon"><i class="fa-solid fa-phone"></i></span>
                <span class="contact-method__copy">
                    <small>Phone support</small>
                    <strong>{{ $supportPhone }}</strong>
                </span>
                <i class="fa-solid fa-arrow-up-right-from-square contact-method__arrow"></i>
            </a>

            <div class="response-card">
                <span><i class="fa-regular fa-clock"></i></span>
                <div>
                    <strong>Response availability</strong>
                    <p>Messages are reviewed during regular government office hours.</p>
                </div>
            </div>
        </aside>

        <div class="message-panel">
            <div class="message-panel__heading">
                <div>
                    <span class="section-kicker">Send an inquiry</span>
                    <h2>How can we help?</h2>
                </div>
                <span class="secure-badge"><i class="fa-solid fa-lock"></i> Secure form</span>
            </div>

            @if (session('contact_success'))
                <div class="contact-alert contact-alert--success" role="status">
                    <i class="fa-solid fa-circle-check"></i><span>{{ session('contact_success') }}</span>
                </div>
            @endif
            @if (session('contact_warning'))
                <div class="contact-alert contact-alert--warning" role="status">
                    <i class="fa-solid fa-triangle-exclamation"></i><span>{{ session('contact_warning') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="contact-alert contact-alert--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i><span>Please review the highlighted fields and try again.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('landing.contact.store') }}" id="contactForm" class="contact-form">
                @csrf
                <div class="contact-form__grid">
                    <div class="field-group">
                        <label for="contactName">Full name</label>
                        <div class="field-control">
                            <i class="fa-regular fa-user"></i>
                            <input id="contactName" type="text" name="name" maxlength="120" autocomplete="name" value="{{ $prefillName }}" class="@error('name') is-invalid @enderror" placeholder="e.g. Juan Dela Cruz" required>
                        </div>
                        <small>Use your personal name, not an office or facility name.</small>
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field-group">
                        <label for="contactEmail">Email address</label>
                        <div class="field-control">
                            <i class="fa-regular fa-envelope"></i>
                            <input id="contactEmail" type="email" name="email" maxlength="255" autocomplete="email" value="{{ $prefillEmail }}" class="@error('email') is-invalid @enderror" placeholder="name@example.com" required>
                        </div>
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field-group field-group--wide">
                        <label for="contactSubject">Subject <span>Optional</span></label>
                        <div class="field-control">
                            <i class="fa-regular fa-bookmark"></i>
                            <input id="contactSubject" type="text" name="subject" maxlength="150" value="{{ old('subject') }}" class="@error('subject') is-invalid @enderror" placeholder="e.g. Energy usage report concern">
                        </div>
                        @error('subject')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field-group field-group--wide">
                        <label for="contactMessage">Message</label>
                        <div class="field-control field-control--textarea">
                            <i class="fa-regular fa-message"></i>
                            <textarea id="contactMessage" name="message" maxlength="5000" rows="5" class="@error('message') is-invalid @enderror" placeholder="Describe your concern or inquiry..." required>{{ old('message') }}</textarea>
                        </div>
                        @error('message')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-footer">
                    <p><i class="fa-solid fa-shield-halved"></i> Your information is used only to respond to this inquiry.</p>
                    <button type="submit" id="sendButton">
                        <span class="button-label">Send message</span>
                        <i class="fa-solid fa-paper-plane button-icon"></i>
                        <i class="fa-solid fa-circle-notch fa-spin button-spinner" hidden></i>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
.contact-page {
    --contact-primary: #2563eb;
    --contact-indigo: #4f46e5;
    --contact-sky: #7dd3fc;
    --contact-ink: #0f172a;
    --contact-copy: #475569;
    --contact-muted: #64748b;
    --contact-line: #dce5f0;
    max-width: 1240px;
    margin: 0 auto;
    padding: 12px 0 30px;
}

.contact-hero {
    position: relative;
    min-height: 330px;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
    align-items: center;
    gap: 52px;
    overflow: hidden;
    padding: 48px 52px;
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 24px;
    background:
        radial-gradient(circle at 90% 12%, rgba(125,211,252,.24), transparent 32%),
        radial-gradient(circle at 12% 88%, rgba(99,102,241,.2), transparent 32%),
        linear-gradient(125deg, #172554, #1e40af 52%, #2563eb);
    box-shadow: 0 24px 55px rgba(30,64,175,.22);
}

.contact-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 45px 45px;
    mask-image: linear-gradient(90deg, transparent, #000);
}

.contact-hero__copy, .contact-identity { position: relative; z-index: 1; }
.contact-eyebrow { width: max-content; display: flex; align-items: center; gap: 9px; margin-bottom: 17px; color: #bfdbfe; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.contact-eyebrow span { width: 8px; height: 8px; border-radius: 50%; background: var(--contact-sky); box-shadow: 0 0 0 5px rgba(125,211,252,.12); }
.contact-hero h1 { max-width: 720px; margin: 0; color: #fff; font-size: clamp(2rem, 4vw, 3.35rem); line-height: 1.08; letter-spacing: -.05em; }
.contact-hero h1 em { color: var(--contact-sky); font-style: normal; }
.contact-hero__copy > p { max-width: 720px; margin: 19px 0 0; color: rgba(226,232,240,.8); font-size: .98rem; line-height: 1.75; }
.contact-hero__tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 25px; }
.contact-hero__tags span { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; color: rgba(255,255,255,.8); border: 1px solid rgba(255,255,255,.14); border-radius: 999px; background: rgba(255,255,255,.07); font-size: .73rem; font-weight: 700; }
.contact-hero__tags i { color: var(--contact-sky); }

.contact-identity { min-height: 225px; display: flex; align-items: center; justify-content: center; flex-direction: column; overflow: hidden; padding: 26px; border: 1px solid rgba(255,255,255,.17); border-radius: 20px; background: rgba(255,255,255,.09); backdrop-filter: blur(12px); text-align: center; }
.contact-identity__orbit { position: absolute; width: 210px; height: 210px; border: 1px solid rgba(255,255,255,.1); border-radius: 50%; box-shadow: 0 0 0 30px rgba(255,255,255,.025), 0 0 0 62px rgba(255,255,255,.018); }
.contact-identity__icon { position: relative; z-index: 1; width: 68px; height: 68px; display: grid; place-items: center; margin-bottom: 14px; overflow: hidden; border: 3px solid rgba(255,255,255,.32); border-radius: 20px; background: #fff; box-shadow: 0 14px 30px rgba(15,23,42,.22); }
.contact-identity__icon img { width: 100%; height: 100%; display: block; object-fit: contain; }
.contact-identity__label { position: relative; z-index: 1; margin-bottom: 5px; color: #bfdbfe; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.contact-identity strong, .contact-identity small { position: relative; z-index: 1; display: block; }
.contact-identity strong { color: #fff; font-size: 1.05rem; }
.contact-identity small { margin-top: 5px; color: rgba(226,232,240,.64); font-size: .76rem; }

.contact-workspace { display: grid; grid-template-columns: minmax(270px, .72fr) minmax(0, 1.55fr); gap: 18px; margin-top: 28px; }
.support-panel, .message-panel { border: 1px solid var(--contact-line); border-radius: 20px; background: #fff; box-shadow: 0 14px 36px rgba(15,23,42,.055); }
.support-panel { padding: 28px; }
.message-panel { padding: 30px; }
.section-kicker { display: block; margin-bottom: 8px; color: var(--contact-primary); font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.support-panel h2, .message-panel h2 { margin: 0; color: var(--contact-ink); font-size: 1.55rem; line-height: 1.25; letter-spacing: -.04em; }
.support-panel__intro { margin: 12px 0 23px; color: var(--contact-muted); font-size: .82rem; line-height: 1.7; }
.contact-method { display: flex; align-items: center; gap: 12px; margin-top: 10px; padding: 14px; color: var(--contact-ink); border: 1px solid #e5edf7; border-radius: 14px; background: linear-gradient(145deg, #f8fbff, #fff); text-decoration: none; transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
.contact-method:hover { transform: translateY(-2px); color: var(--contact-ink); border-color: #bfdbfe; box-shadow: 0 10px 22px rgba(37,99,235,.09); }
.contact-method__icon { width: 41px; height: 41px; flex: 0 0 auto; display: grid; place-items: center; color: var(--contact-primary); border: 1px solid #dbeafe; border-radius: 12px; background: #eff6ff; font-size: .85rem; }
.contact-method__copy { min-width: 0; display: grid; gap: 3px; }
.contact-method__copy small { color: var(--contact-muted); font-size: .65rem; }
.contact-method__copy strong { overflow-wrap: anywhere; color: var(--contact-ink); font-size: .74rem; }
.contact-method__arrow { margin-left: auto; color: #94a3b8; font-size: .66rem; }
.response-card { display: flex; align-items: flex-start; gap: 12px; margin-top: 20px; padding: 15px; color: #dbeafe; border: 1px solid rgba(255,255,255,.11); border-radius: 14px; background: linear-gradient(135deg, #172554, #1e40af); }
.response-card > span { width: 34px; height: 34px; flex: 0 0 auto; display: grid; place-items: center; color: var(--contact-sky); border-radius: 10px; background: rgba(125,211,252,.1); }
.response-card strong { display: block; color: #fff; font-size: .73rem; }
.response-card p { margin: 5px 0 0; color: rgba(203,213,225,.7); font-size: .66rem; line-height: 1.55; }

.message-panel__heading { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 23px; }
.secure-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 999px; background: #eff6ff; font-size: .65rem; font-weight: 700; white-space: nowrap; }
.contact-alert { display: flex; align-items: flex-start; gap: 9px; margin-bottom: 18px; padding: 12px 14px; border: 1px solid; border-radius: 12px; font-size: .75rem; line-height: 1.55; }
.contact-alert--success { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
.contact-alert--warning { color: #b45309; border-color: #fde68a; background: #fffbeb; }
.contact-alert--error { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
.contact-form__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.field-group { min-width: 0; }
.field-group--wide { grid-column: 1 / -1; }
.field-group label { display: block; margin-bottom: 7px; color: #334155; font-size: .73rem; font-weight: 700; }
.field-group label span { margin-left: 4px; color: #94a3b8; font-size: .62rem; font-weight: 500; }
.field-control { position: relative; }
.field-control > i { position: absolute; z-index: 1; top: 50%; left: 14px; transform: translateY(-50%); color: #94a3b8; font-size: .76rem; pointer-events: none; }
.field-control--textarea > i { top: 16px; transform: none; }
.field-control input, .field-control textarea { width: 100%; min-height: 46px; display: block; padding: 11px 13px 11px 40px; color: var(--contact-ink); border: 1px solid #dce5f0; outline: 0; border-radius: 11px; background: #fbfdff; font-size: .76rem; transition: border-color .16s ease, box-shadow .16s ease, background .16s ease; }
.field-control textarea { min-height: 125px; resize: vertical; line-height: 1.6; }
.field-control input::placeholder, .field-control textarea::placeholder { color: #a1afbf; }
.field-control input:focus, .field-control textarea:focus { border-color: #60a5fa; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,.09); }
.field-control:focus-within > i { color: var(--contact-primary); }
.field-control .is-invalid { border-color: #ef4444; }
.field-group > small { display: block; margin-top: 6px; color: #8493a3; font-size: .62rem; }
.field-error { display: block; margin-top: 5px; color: #dc2626; font-size: .65rem; }
.form-footer { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-top: 22px; padding-top: 20px; border-top: 1px solid #e8eef5; }
.form-footer p { margin: 0; color: #7b8b9b; font-size: .65rem; line-height: 1.55; }
.form-footer p i { margin-right: 5px; color: var(--contact-primary); }
.form-footer button { min-width: 155px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; color: #fff; border: 0; border-radius: 11px; background: linear-gradient(105deg, var(--contact-primary), var(--contact-indigo)); box-shadow: 0 10px 20px rgba(37,99,235,.2); font-size: .72rem; font-weight: 800; cursor: pointer; transition: transform .17s ease, box-shadow .17s ease; }
.form-footer button:hover { transform: translateY(-2px); box-shadow: 0 13px 24px rgba(37,99,235,.28); }
.form-footer button:disabled { opacity: .7; cursor: wait; transform: none; }

body.dark-mode .contact-page { --contact-ink: #f1f5f9; --contact-copy: #cbd5e1; --contact-muted: #94a3b8; --contact-line: #334155; }
body.dark-mode .support-panel, body.dark-mode .message-panel { border-color: #334155; background: #111c30; box-shadow: 0 16px 38px rgba(0,0,0,.2); }
body.dark-mode .contact-method { color: #e2e8f0; border-color: #334155; background: linear-gradient(145deg, #172033, #1e293b); }
body.dark-mode .contact-method:hover { border-color: #475b75; }
body.dark-mode .contact-method__copy strong { color: #e2e8f0; }
body.dark-mode .contact-method__icon { color: #7dd3fc; border-color: #334b70; background: rgba(37,99,235,.13); }
body.dark-mode .secure-badge { color: #bfdbfe; border-color: #334b70; background: rgba(37,99,235,.13); }
body.dark-mode .field-group label { color: #cbd5e1; }
body.dark-mode .field-control input, body.dark-mode .field-control textarea { color: #e5edf7; border-color: #334155; background: #0f172a; }
body.dark-mode .field-control input:focus, body.dark-mode .field-control textarea:focus { border-color: #3b82f6; background: #111c30; }
body.dark-mode .form-footer { border-top-color: #29384d; }
body.dark-mode .contact-alert--success { color: #a7f3d0; border-color: #176b54; background: rgba(5,150,105,.12); }
body.dark-mode .contact-alert--warning { color: #fde68a; border-color: #72551a; background: rgba(217,119,6,.1); }
body.dark-mode .contact-alert--error { color: #fecaca; border-color: #7f3030; background: rgba(220,38,38,.1); }

@media (max-width: 980px) {
    .contact-hero { grid-template-columns: 1fr; }
    .contact-identity { min-height: 190px; }
    .contact-workspace { grid-template-columns: 1fr; }
    .support-panel { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .support-panel > .section-kicker, .support-panel > h2, .support-panel__intro, .response-card { grid-column: 1 / -1; }
    .contact-method { margin-top: 0; }
}

@media (max-width: 680px) {
    .contact-page { padding-top: 0; }
    .contact-hero { gap: 30px; padding: 30px 22px; border-radius: 19px; }
    .contact-hero h1 { font-size: 2rem; }
    .contact-workspace { margin-top: 14px; }
    .support-panel, .message-panel { padding: 22px 18px; border-radius: 17px; }
    .support-panel, .contact-form__grid { grid-template-columns: 1fr; }
    .support-panel > *, .field-group--wide { grid-column: auto; }
    .message-panel__heading { align-items: flex-start; }
    .secure-badge { font-size: 0; }
    .secure-badge i { font-size: .7rem; }
    .form-footer { align-items: stretch; flex-direction: column; }
    .form-footer button { width: 100%; }
}

@media (prefers-reduced-motion: reduce) {
    .contact-method, .form-footer button { transition: none; }
}
</style>

<script>
document.getElementById('contactForm')?.addEventListener('submit', function () {
    const button = document.getElementById('sendButton');
    if (!button || !this.checkValidity()) return;
    button.disabled = true;
    button.querySelector('.button-label').textContent = 'Sending...';
    button.querySelector('.button-icon').hidden = true;
    button.querySelector('.button-spinner').hidden = false;
});
</script>
@endsection
