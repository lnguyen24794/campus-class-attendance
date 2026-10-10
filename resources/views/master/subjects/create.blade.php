@extends('layouts.app')

@section('title', 'Thêm môn học')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Thêm môn học</h1>
    @include('master.subjects._form', ['action' => route('subjects.store'), 'subject' => null])
@endsection
