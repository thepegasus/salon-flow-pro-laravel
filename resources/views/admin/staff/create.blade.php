@extends('layouts.admin')

@section('title', 'Add Staff')

@section('content')
    <h1 class="h3">Add Staff</h1>

    <form action="{{ route('staff.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" name="role" class="form-control" value="{{ old('role') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Job Title</label>
            <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}">
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
