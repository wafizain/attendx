<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AttendX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden; /* cegah scrollbar horizontal */
        }

        .login-container {
            width: 100%;
            min-height: calc(100vh - 64px); /* kurangi tinggi navbar */
            display: flex;
            align-items: stretch;
            padding: 32px 56px; /* kurangi padding vertical agar tidak overflow */
            box-sizing: border-box;
        }

        .top-navbar {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 10;
        }

        .top-navbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #1f2937;
            font-size: 18px;
        }

        .top-navbar .brand img {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
        }

        .login-left {
            flex: 1;
            padding: 72px 64px; /* sedikit diperkecil agar pas layar 14" */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .login-left .content {
            max-width: 380px; /* kecilkan lebar kolom form */
            width: 100%;
        }

        .login-right {
            flex: 1.4;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }

        .brand-logo i {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
        }

        .brand-img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            margin-right: 12px;
            object-fit: cover;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 40px;
            line-height: 1.2;
        }

        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 32px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
            position: relative;
        }

        .social-btn:hover {
            border-color: #d1d5db;
            background: #f9fafb;
            color: #374151;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .social-btn i {
            margin-right: 12px;
            font-size: 18px;
        }

        .google-btn i {
            background: linear-gradient(45deg, #ea4335, #fbbc05, #34a853, #4285f4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .apple-btn i {
            color: #000;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            color: #9ca3af;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 16px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            background: white;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .btn-continue {
            width: 100%;
            padding: 14px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 24px;
        }

        .btn-continue:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .terms-text {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        .terms-text a {
            color: #4f46e5;
            text-decoration: none;
        }

        .terms-text a:hover {
            text-decoration: underline;
        }

        .forgot-link {
            font-size: 13px;
            color: #4f46e5;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .illustration-img {
            max-width: 520px;
            width: 100%;
            height: auto;
            max-height: 60vh; /* kurangi agar aman di layar 14" */
            object-fit: contain;
            display: block;
        }

        .illustration {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .illustration-person {
            position: absolute;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .person-1 {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            top: 20%;
            left: 20%;
            animation: float 6s ease-in-out infinite;
        }

        .person-2 {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            top: 15%;
            right: 25%;
            animation: float 6s ease-in-out infinite 2s;
        }

        .person-3 {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            bottom: 30%;
            left: 30%;
            animation: float 6s ease-in-out infinite 4s;
        }

        .person-icon {
            color: white;
            font-size: 40px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px;
            margin-bottom: 24px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        @media (max-width: 1200px) {
            .login-container {
                flex-direction: column;
                padding: 24px 24px;
            }

            .login-right {
                min-height: 300px;
            }

            .login-left {
                padding: 40px 28px;
            }

            .login-title {
                font-size: 28px;
            }

            .illustration-img { max-width: 420px; max-height: 38vh; }
        }

        @media (max-width: 768px) {
            .illustration-img {
                max-width: 320px;
                max-height: 30vh;
            }
        }
    </style>
</head>
<body>
    <div class="top-navbar">
        <div class="brand">
            <img src="images/logo-attendx.png" alt="AttendX">
        </div>
    </div>
    <div class="login-container">
        <div class="login-left">
            <div class="content">
            <h1 class="login-title">Login</h1>
            <div style="margin-top:-24px; margin-bottom:24px; color:#6b7280; font-size:16px;">Selamat datang di <strong>AttendX</strong></div>


            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username"
                           placeholder="Enter your username"
                           value="{{ old('username') }}" 
                           required 
                           autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password"
                           placeholder="Enter your password" 
                           required>
                </div>

                <div class="text-end" style="margin-top:-8px;margin-bottom:16px;">
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-continue">Submit</button>
            </form>

            </div>
        </div>

        <div class="login-right">
            <img src="images/elemen-login.png" alt="AttendX" class="illustration-img">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

