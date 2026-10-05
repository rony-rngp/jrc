<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>JRC | Log in</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 380px;
            margin: 0;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .login-logo {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            padding: 20px;
            margin-bottom: 0;
            color: #fff;
            font-weight: 700;
        }
        .login-logo a { color: #fff; text-decoration: none; }
        .login-box-body { padding: 30px; }
        .btn-primary {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 10px;
            font-weight: 600;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo text-center">
        <a href="#"><b>JR</b> Corporation</a>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>

        @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 6px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="post">
            @csrf
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-xs-8">
                    <div class="checkbox" style="margin-top: 5px;">
                        <label>
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                    </div>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>
</html>
