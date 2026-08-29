@extends('layouts.app')

@section('title', 'Register — Royal Khmer Kitchen')

@section('content')
<div style="max-width: 460px; margin: 40px auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create a Customer Account</h2>
        </div>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="John Doe">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+855 12 345 678">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password (Minimum 6 characters) *</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </div>
        </form>

        <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">

        <div style="text-align: center; font-size: 13px;">
            Already have an account? <a href="{{ route('login') }}"><strong>Sign in here</strong></a>
        </div>
    </div>
</div>
@endsection
