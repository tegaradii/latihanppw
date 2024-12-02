@extends('layout.master')

@section('title', 'Dashboard')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    Selamat Datang, {{ Auth::user()->name }}
                    {{-- rahasia123 --}}
                </div>
            </div>
        </div>
    </div>
@endsection
