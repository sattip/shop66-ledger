@extends('layouts.app')

@section('page-title', $title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">{{ $title }} Module</h4>
                <p class="text-muted">This module is currently under development.</p>
                <p class="text-muted">Please check back later or contact your administrator.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection