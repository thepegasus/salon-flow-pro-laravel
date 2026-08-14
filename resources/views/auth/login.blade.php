<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sign in') }} — SalonFlow Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #F3F6F5;
            color: #16201D;
            font-family: Outfit, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .brand-font {
            font-family: 'Bricolage Grotesque', Outfit, sans-serif;
        }

        .mono-font {
            font-family: 'IBM Plex Mono', monospace;
        }

        .login-grid {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
        }

        @media (max-width: 860px) {
            .login-grid {
                grid-template-columns: 1fr;
            }

            .login-panel {
                display: none;
            }
        }

        .login-panel {
            background: #E2EDE7;
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .login-form-input {
            width: 100%;
            padding: 15px 16px;
            border: 1px solid #E4D8D1;
            border-radius: 14px;
            background: #fff;
            font-size: 15px;
            font-family: inherit;
            margin-bottom: 18px;
            outline: none;
            box-sizing: border-box;
        }

        .login-form-input:focus {
            border-color: #1B4B8F;
        }

        .login-submit {
            width: 100%;
            border: none;
            background: #1B4B8F;
            color: #fff;
            padding: 16px;
            border-radius: 14px;
            font-size: 15.5px;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 10px 26px -10px rgba(27, 75, 143, .55);
        }

        .login-submit:hover {
            background: #153B70;
        }

        .login-error {
            background: #FBEAEA;
            border: 1px solid #E7B9B9;
            color: #8A2C2C;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="login-grid">
        <div class="login-panel">
            <div style="display:flex;align-items:center;gap:10px">
                <div class="brand-font" style="width:30px;height:30px;border-radius:10px;background:#1B4B8F;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:18px;color:#fff">S</div>
                <span class="brand-font" style="font-weight:600;font-size:22px">SalonFlow <span style="color:#1B4B8F">Pro</span></span>
            </div>
            <div>
                <div class="brand-font" style="font-weight:600;font-size:42px;line-height:1.1;letter-spacing:-.02em;max-width:460px">&ldquo;The diary, the calculator and the sticky notes all went in the bin the same week.&rdquo;</div>
                <div style="font-size:13.5px;color:#2E5F4C;margin-top:22px">Front desk &middot; 6-chair studio, Chennai</div>
            </div>
            <div class="mono-font" style="font-size:11px;color:#7BA795">GST-ready &middot; UPI reconciliation &middot; WhatsApp reminders</div>
        </div>

        <div style="display:flex;align-items:center;justify-content:center;padding:48px">
            <div style="width:100%;max-width:392px">
                <h1 class="brand-font" style="font-weight:600;font-size:36px;letter-spacing:-.02em;margin:0 0 8px">{{ __('Welcome back') }}</h1>
                <p style="font-size:15px;color:#66736F;margin:0 0 32px">{{ __('Sign in to your studio.') }}</p>

                @if ($errors->any())
                    <div class="login-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <label style="display:block;font-size:12.5px;letter-spacing:.05em;text-transform:uppercase;color:#788582;margin-bottom:8px">{{ __('Username') }}</label>
                    <input type="text" name="username" value="{{ old('username') }}" autofocus class="login-form-input">

                    <label style="display:block;font-size:12.5px;letter-spacing:.05em;text-transform:uppercase;color:#788582;margin-bottom:8px">{{ __('Password') }}</label>
                    <input type="password" name="password" class="login-form-input" style="margin-bottom:14px">

                    <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:26px;font-size:13.5px">
                        <a href="#" style="color:#1B4B8F;text-decoration:none">{{ __('Forgot password?') }}</a>
                    </div>

                    <button type="submit" class="login-submit">{{ __('Sign in') }}</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
