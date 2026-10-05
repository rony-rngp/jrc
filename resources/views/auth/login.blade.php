<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>JR Corporation | Secure Portal Login</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    <!-- Bootstrap & Font Awesome -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    
    <!-- Modern Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #0b1120 0%, #0f172a 50%, #1e293b 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Glow Background Circles */
        .glow-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.5;
            animation: floatGlow 10s ease-in-out infinite alternate;
        }
        .glow-1 {
            width: 350px;
            height: 350px;
            background: #4f46e5;
            top: 10%;
            left: 15%;
        }
        .glow-2 {
            width: 300px;
            height: 300px;
            background: #0ea5e9;
            bottom: 10%;
            right: 15%;
            animation-delay: -5s;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 40px) scale(1.1); }
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 10;
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 26px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.35);
            margin-bottom: 14px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 13.5px;
            color: #64748b;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 7px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: color 0.2s;
        }
        .custom-input {
            width: 100%;
            height: 46px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px 10px 42px;
            font-size: 14px;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: all 0.25s ease;
        }
        .custom-input:focus {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .custom-input:focus + i {
            color: #4f46e5;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13.5px;
            color: #475569;
        }
        .remember-row label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-weight: 500;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.45);
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        .alert-modern {
            background: #fef2f2;
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .portal-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="glow-circle glow-1"></div>
<div class="glow-circle glow-2"></div>

<div class="login-card">
    <div class="brand-header">
        <div class="brand-icon">
            <i class="fa fa-building-o"></i>
        </div>
        <h1 class="brand-title">JR Corporation</h1>
        <p class="brand-subtitle">Sign in to start your session</p>
    </div>

    @if($errors->any())
        <div class="alert-modern">
            <i class="fa fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <div class="input-wrapper">
                <input type="text" class="custom-input" id="username" name="username" value="{{ old('username') }}" placeholder="Enter your username" required autofocus>
                <i class="fa fa-user"></i>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrapper">
                <input type="password" class="custom-input" id="password" name="password" placeholder="Enter your password" required>
                <i class="fa fa-lock"></i>
            </div>
        </div>

        <div class="remember-row">
            <label>
                <input type="checkbox" name="remember" style="accent-color: #4f46e5;"> Remember Me
            </label>
        </div>

        <button type="submit" class="btn-submit">
            <span>Sign In</span>
            <i class="fa fa-arrow-right"></i>
        </button>
    </form>

    <div class="portal-footer">
        JR Corporation &copy; {{ date('Y') }} &bull; All Rights Reserved
    </div>
</div>

<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>
</html>
