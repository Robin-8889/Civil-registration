<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Restricted - Civil Registration System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #101418;
            --slate: #425466;
            --paper: #f8f5ef;
            --sun: #f4c430;
            --ember: #d35400;
            --ocean: #0b7285;
            --shadow: rgba(15, 23, 42, 0.2);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: "Source Sans 3", "Segoe UI", system-ui, sans-serif;
            color: var(--ink);
            background: radial-gradient(1200px 500px at 20% 10%, rgba(244, 196, 48, 0.15), transparent 60%),
                        radial-gradient(900px 450px at 90% 20%, rgba(11, 114, 133, 0.18), transparent 60%),
                        linear-gradient(135deg, #f9f2e7 0%, #eef3f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .frame {
            width: min(980px, 100%);
            background: rgba(255, 255, 255, 0.92);
            border-radius: 20px;
            box-shadow: 0 24px 60px var(--shadow);
            overflow: hidden;
            position: relative;
        }
        .accent {
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(244, 196, 48, 0.25), rgba(211, 84, 0, 0.15));
            clip-path: polygon(0 0, 62% 0, 45% 40%, 0 55%);
            pointer-events: none;
        }
        .content {
            position: relative;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 32px;
            padding: 48px 56px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fef6db;
            color: #8a5a00;
            border: 1px solid #f4c430;
            border-radius: 999px;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .title {
            font-family: "Playfair Display", "Times New Roman", serif;
            font-size: 2.4rem;
            margin: 16px 0 12px;
        }
        .subtitle {
            color: var(--slate);
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            border: 0;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--ocean);
            color: white;
            box-shadow: 0 12px 24px rgba(11, 114, 133, 0.25);
        }
        .btn-secondary {
            background: #e9eef2;
            color: var(--ink);
        }
        .panel {
            background: var(--paper);
            border-radius: 16px;
            padding: 28px;
            border: 1px solid rgba(16, 20, 24, 0.08);
            align-self: center;
        }
        .code {
            font-size: 3.8rem;
            font-weight: 700;
            color: var(--ember);
            line-height: 1;
        }
        .panel-title {
            font-weight: 700;
            margin: 14px 0 8px;
        }
        .panel-text {
            color: var(--slate);
            margin: 0;
            line-height: 1.6;
        }
        .lock {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #f2e7d5;
            display: grid;
            place-items: center;
            border: 1px solid rgba(16, 20, 24, 0.08);
        }
        @media (max-width: 860px) {
            .content {
                grid-template-columns: 1fr;
                padding: 36px 28px;
            }
            .title {
                font-size: 2.05rem;
            }
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="accent"></div>
        <div class="content">
            <div>
                <span class="badge">
                    Access Check
                </span>
                <h1 class="title">This area is restricted</h1>
                <p class="subtitle">
                    @if(auth()->check())
                        @if(!auth()->user()->is_approved && auth()->user()->role !== 'citizen')
                            Your account is pending approval. Please contact the system administrator to activate your account.
                        @else
                            You do not have the required admin permission to access this page.
                        @endif
                    @else
                        You need to be logged in to access this page.
                    @endif
                </p>
                <div class="actions">
                    @if(auth()->check())
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Create Account</a>
                    @endif
                </div>
            </div>
            <div class="panel">
                <div class="lock" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#8a5a00" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                        <path d="M8 10V8a4 4 0 0 1 8 0v2"></path>
                        <path d="M12 14v3"></path>
                    </svg>
                </div>
                <div class="code">403</div>
                <div class="panel-title">Authorization required</div>
                <p class="panel-text">
                    If you believe this is a mistake, contact your registration office administrator to request access.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
