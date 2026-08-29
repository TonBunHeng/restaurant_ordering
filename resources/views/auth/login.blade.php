@extends('layouts.app')

@section('title', 'Login — Royal Khmer Kitchen')

@section('content')
<div style="max-width: 440px; margin: 40px auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Sign In to Your Account</h2>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me on this computer</span>
                </label>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </div>
        </form>

        <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

        <!-- Quick Demo Login Buttons for convenience -->
        <div style="background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px dashed #cbd5e1; margin-bottom: 15px;">
            <div style="font-size: 11px; font-weight: bold; color: #475569; margin-bottom: 8px;">Quick Demo Accounts:</div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('admin@aitourism.kh', 'password123')">Super Admin</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('staff@aitourism.kh', 'password123')">Staff Chef</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('traveler@example.com', 'password123')">Customer</button>
            </div>
        </div>

        <div style="text-align: center; font-size: 13px;">
            Don't have an account? <a href="{{ route('register') }}"><strong>Register here</strong></a>
        </div>
    </div>
</div>

<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endsection
