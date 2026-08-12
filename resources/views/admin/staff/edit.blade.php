@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('content')
    <h1 class="h3">Edit {{ $staff->user->name }}</h1>

    <form action="{{ route('staff.update', $staff) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Job Title</label>
            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $staff->job_title) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($staff->is_active)>
            <label class="form-check-label">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
