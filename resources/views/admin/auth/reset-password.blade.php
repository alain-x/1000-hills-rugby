<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password | 1000 Hills Rugby</title>
    <link rel="icon" type="image/png" href="{{ media_url('images/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-card-header">
            <img src="{{ site_logo() }}" alt="1000 Hills Rugby">
            <h1>Set a new password</h1>
            <p class="text-muted mb-0">Choose a strong password for your staff account.</p>
        </div>
        <div class="auth-card-body">
            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('admin.password.update') }}" method="POST" class="form-admin">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" autocomplete="email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required autofocus>
                    </div>
                    <div class="form-text">At least 8 characters.</div>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-forest w-100 py-2">Reset password <i class="bi bi-check2 ms-1"></i></button>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="small"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
                </div>
            </form>
        </div>
        <div class="auth-card-footer">
            <i class="bi bi-shield-lock me-1"></i>This link expires after 60 minutes and can only be used once
        </div>
    </div>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
