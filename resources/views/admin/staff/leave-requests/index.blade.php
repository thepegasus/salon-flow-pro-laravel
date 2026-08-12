@extends('layouts.admin')

@section('title', 'Leave Requests')

@section('content')
    <h1 class="h3">Pending Leave Requests</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Staff</th>
                <th>Dates</th>
                <th>Reason</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leaveRequests as $leaveRequest)
                <tr>
                    <td>{{ $leaveRequest->staffProfile->user->name }}</td>
                    <td>{{ $leaveRequest->start_date->toDateString() }} &ndash; {{ $leaveRequest->end_date->toDateString() }}</td>
                    <td>{{ $leaveRequest->reason }}</td>
                    <td>
                        @can('staff.edit')
                            <form action="{{ route('staff.leaveRequests.update', $leaveRequest) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form action="{{ route('staff.leaveRequests.update', $leaveRequest) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
