@extends('layouts.admin')

@section('title', $staff->user->name)

@section('content')
    <h1 class="h3">{{ $staff->user->name }}</h1>
    <p>{{ $staff->job_title }}</p>
    <p>{{ $staff->phone }}</p>

    <h2 class="h5 mt-4">Services</h2>
    <ul>
        @foreach ($staff->services as $service)
            <li>{{ $service->name }}</li>
        @endforeach
    </ul>
@endsection
