@extends('layouts.admin')

@section('title', 'Leave Requests')

@section('content')
    <div class="sfp-page-header">
        <div>
            <h1 class="sfp-page-title">Leave requests</h1>
            <p class="sfp-page-subtitle">Approved leave is blocked from booking automatically</p>
        </div>
    </div>

    <div class="sfp-card">
        <div class="sfp-card-title">Pending leave requests</div>

        @foreach ($leaveRequests as $leaveRequest)
            <div style="padding: 15px; border-radius: 14px; background: #F3F6F5; border: 1px solid #F0E7E1; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div style="font-weight: 500;">{{ $leaveRequest->staffProfile->name }}</div>
                    <span class="sfp-pill sfp-pill-amber">Pending</span>
                </div>
                <div class="sfp-mono" style="font-size: 12.5px; color: #66736F; margin-top: 6px;">
                    {{ $leaveRequest->start_date->toDateString() }} &ndash; {{ $leaveRequest->end_date->toDateString() }}
                </div>
                <p style="font-size: 13.5px; color: #66736F; margin: 8px 0 0;">{{ $leaveRequest->reason }}</p>

                @can('staff.edit')
                    <div class="sfp-form-actions" style="margin-top: 12px;">
                        <form action="{{ $tenantUrl->route('staff.leaveRequests.update', $leaveRequest) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" style="border: none; background: #E7F0EA; color: #2F6849; border-radius: 999px; padding: 9px 16px; font-size: 12.5px; cursor: pointer;">Approve</button>
                        </form>
                        <form action="{{ $tenantUrl->route('staff.leaveRequests.update', $leaveRequest) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" style="background: #fff; border: 1px solid #E4D8D1; color: #66736F; border-radius: 999px; padding: 9px 16px; font-size: 12.5px; cursor: pointer;">Reject</button>
                        </form>
                    </div>
                @endcan
            </div>
        @endforeach
    </div>
@endsection
