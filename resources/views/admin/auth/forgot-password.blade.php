<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password | 1000 Hills Rugby</title>
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
            <h1>Forgot password</h1>
            <p class="text-muted mb-0">Enter your staff email and we will send you a secure reset link.</p>
        </div>
        <div class="auth-card-body">
            @if(session('status'))
                <div class="alert alert-success py-2 small">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('admin.password.email') }}" method="POST" class="form-admin">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" autocomplete="email" placeholder="name@1000hillsrugby.rw" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin-forest w-100 py-2">Send reset link <i class="bi bi-send ms-1"></i></button>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="small"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
                </div>
            </form>
        </div>
        <div class="auth-card-footer">
            <i class="bi bi-envelope-check me-1"></i>Reset emails are sent from our no-reply address
        </div>
    </div>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
