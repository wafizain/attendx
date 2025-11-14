@extends('layouts.master')
@section('content')
<div class="container mt-4">
    <h2>Ganti Password Pertama Kali</h2>
    <form action="{{ route('password.first-change.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Password Lama (sementara)</label>
            <input type="password" name="old_password" class="form-control" required>
            @error('old_password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="password" class="form-control" required>
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Ganti Password</button>
    </form>
</div>
@endsection
