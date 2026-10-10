@extends('layouts.app')

@section('title', 'Thêm sinh viên')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Thêm sinh viên</h1>
    @include('master.students._form', ['action' => route('students.store'), 'student' => null])
@endsection
