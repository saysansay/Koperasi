<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.log_on') }} - {{ __('app.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="sap-login">
    <div class="card login-card">
        <div class="card-body p-4 p-lg-4">
            <div class="login-title-bar">{{ __('app.app_name') }} Logon</div>
            <div class="mb-4">
                <h2 class="fw-bold mb-1 fs-4">{{ __('app.login_title') }}</h2>
                <div class="text-muted">{{ __('app.login_subtitle') }}</div>
            </div>
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.attempt') }}" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('app.email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@koperasi.test') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">{{ __('app.password') }}</label>
                    <input type="password" name="password" class="form-control" value="password" required>
                </div>
                <div class="col-12 form-check ms-1">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">{{ __('app.remember_me') }}</label>
                </div>
                <div class="col-12">
                    <button class="btn btn-dark w-100">{{ __('app.log_on') }}</button>
                </div>
                <div class="col-12 text-muted small">
                    {{ __('app.demo_user') }}: admin@koperasi.test / password
                </div>
            </form>
        </div>
    </div>
</body>
</html>
