@extends('layouts.app')

@section('title', 'Sửa môn học')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Sửa môn học</h1>
    @include('master.subjects._form', ['action' => route('subjects.update', $subject), 'subject' => $subject, 'method' => 'PUT'])
@endsection
