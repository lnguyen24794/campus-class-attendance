@extends('layouts.app')

@section('title', 'Quản lý Tiết học')
@section('page', 'periods')

@section('content')
    <x-crud-page
        title="Quản lý Tiết học"
        breadcrumb="Tiết học"
        :columns="['Số tiết', 'Tên', 'Bắt đầu', 'Kết thúc']"
        :fields="[
            ['name' => 'number', 'label' => 'Số tiết', 'type' => 'number', 'required' => true],
            ['name' => 'name', 'label' => 'Tên tiết', 'required' => true],
            ['name' => 'starts_at', 'label' => 'Bắt đầu', 'type' => 'time', 'required' => true],
            ['name' => 'ends_at', 'label' => 'Kết thúc', 'type' => 'time', 'required' => true],
        ]"
    />
@endsection
