@extends('layouts.app')

@section('title', 'Thêm giảng viên')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Thêm giảng viên</h1>
    @include('master.lecturers._form', ['action' => route('lecturers.store'), 'lecturer' => null])
@endsection
