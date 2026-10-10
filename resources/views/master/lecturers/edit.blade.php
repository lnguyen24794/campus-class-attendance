@extends('layouts.app')

@section('title', 'Sửa giảng viên')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Sửa giảng viên</h1>
    @include('master.lecturers._form', ['action' => route('lecturers.update', $lecturer), 'lecturer' => $lecturer, 'method' => 'PUT'])
@endsection
