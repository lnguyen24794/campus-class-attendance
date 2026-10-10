@extends('layouts.app')

@section('title', 'Sửa tiết học')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Sửa tiết học</h1>
    @include('master.periods._form', ['action' => route('periods.update', $period), 'period' => $period, 'method' => 'PUT'])
@endsection
