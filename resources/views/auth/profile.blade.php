@extends('layout.master')

@section('title', 'Profile')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <div class="text-center">
                        @if (Auth::user()->photo !== 'noimage.png')
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Avatar"
                                class="rounded-circle border border-3 border-primary me-2" width="200" height="200">
                        @else
                            <img src="{{ asset('storage/avada_kedavra/noimage.png') }}" alt="Avatar"
                                class="rounded-circle border border-3 border-primary me-2" width="200" height="200">
                        @endif
                    </div>

                    <div>Selamat Datang, {{ Auth::user()->name }}</div>
                    <div>Email Anda, {{ Auth::user()->email }}</div>
                </div>
            </div>
        </div>
    @endsection
