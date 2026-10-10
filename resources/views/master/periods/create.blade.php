@extends('layouts.app')

@section('title', 'Thêm tiết học')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Thêm tiết học</h1>
    @include('master.periods._form', ['action' => route('periods.store'), 'period' => null])
@endsection
