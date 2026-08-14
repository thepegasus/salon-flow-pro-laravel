@extends('layouts.admin')

@section('title', 'Add Staff')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Add staff</h1>
            <p class="sfp-page-subtitle">Create a new staff profile and login</p>
        </div>
    </div>

    <div class="sfp-card">
        <form action="{{ $tenantUrl->route('staff.store') }}" method="POST">
            @csrf

            <div class="sfp-field">
                <label class="sfp-label">Name</label>
                <input type="text" name="name" class="sfp-input" value="{{ old('name') }}">
                @error('name')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Username</label>
                <input type="text" name="username" class="sfp-input" value="{{ old('username') }}">
                @error('username')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Email</label>
                <input type="email" name="email" class="sfp-input" value="{{ old('email') }}">
                @error('email')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Password</label>
                <input type="password" name="password" class="sfp-input">
                @error('password')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Role</label>
                <input type="text" name="role" class="sfp-input" value="{{ old('role') }}">
                @error('role')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-field">
                <label class="sfp-label">Job Title</label>
                <input type="text" name="job_title" class="sfp-input" value="{{ old('job_title') }}">
                @error('job_title')
                    <span class="sfp-invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="sfp-form-actions">
                <button type="submit" class="sfp-btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
