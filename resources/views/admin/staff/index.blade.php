@extends('layouts.admin')

@section('title', 'Staff')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Staff</h1>
        @can('staff.create')
            <a href="{{ route('staff.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Staff
            </a>
        @endcan
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Job Title</th>
                <th>Phone</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staff as $member)
                <tr>
                    <td>{{ $member->user->name }}</td>
                    <td>{{ $member->job_title }}</td>
                    <td>{{ $member->phone }}</td>
                    <td>{{ $member->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('staff.show', $member) }}">View</a>
                        @can('staff.edit')
                            <a href="{{ route('staff.edit', $member) }}">Edit</a>
                        @endcan
                        @can('staff.delete')
                            <form action="{{ route('staff.destroy', $member) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0">Remove</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
