@php
    $flashMessages = [];

    foreach (['success', 'error', 'status'] as $flashType) {
        $flashValue = session()->pull($flashType);

        if (! $flashValue) {
            continue;
        }

        if ($flashType === 'status') {
            $flashText = match ($flashValue) {
                'profile-updated' => 'Profile updated successfully.',
                'password-updated' => 'Password updated successfully.',
                'verification-link-sent' => 'Verification link sent.',
                default => (string) $flashValue,
            };
            $flashType = 'success';
        } else {
            $flashText = (string) $flashValue;
        }

        $flashMessages[] = [
            'type' => $flashType,
            'text' => $flashText,
        ];
    }
@endphp

@if(! empty($flashMessages))
    <style>
        .global-toast-stack {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 100000;
            display: grid;
            gap: 10px;
            width: min(420px, calc(100vw - 32px));
            pointer-events: none;
        }

        .global-toast {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            padding: 14px 16px;
            font-weight: 800;
            border: 1px solid transparent;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
            opacity: 1;
            transform: translateY(0);
            transition: opacity .22s ease, transform .22s ease;
            pointer-events: auto;
        }

        .global-toast-success {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .global-toast-error {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .global-toast.is-hidden {
            opacity: 0;
            transform: translateY(-8px);
            pointer-events: none;
        }
    </style>

    <div class="global-toast-stack" aria-live="polite" aria-atomic="true">
        @foreach($flashMessages as $flash)
            <div class="global-toast global-toast-{{ $flash['type'] }}" data-global-toast>
                <i class="fa-solid {{ $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
                <span>{{ $flash['text'] }}</span>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-global-toast]').forEach(function(toast) {
                setTimeout(function() {
                    toast.classList.add('is-hidden');
                }, 2800);

                setTimeout(function() {
                    toast.remove();
                }, 3300);
            });
        });
    </script>
@endif
