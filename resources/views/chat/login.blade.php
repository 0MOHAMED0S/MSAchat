@extends('layouts.app')
@section('styles')
<style>
    .card-body{
        justify-content: center;
    display: flex;
    align-items: center;
    }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-center align-items-center h-100">
    <div class="text-center bg-white shadow-lg rounded-4 p-5 mx-auto" style="max-width: 420px; width: 100%;">

        <!-- Title -->
        <h3 class="fw-bold text-dark mb-3">
            <i class="bi bi-chat-dots-fill text-primary me-2"></i> Welcome to MSA Chat
        </h3>

        <!-- Subtitle -->
        <p class="text-muted mb-4">Connect instantly with your friends and colleagues.</p>

        <!-- Google Login Button -->
        <a href="{{ route('login.google') }}"
           class="btn btn-light border d-flex align-items-center justify-content-center py-2 px-4 rounded-pill shadow-sm w-100">
            <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" width="22" class="me-2">
            <span class="fw-semibold">Continue with Google</span>
        </a>

    </div>
</div>
@endsection
