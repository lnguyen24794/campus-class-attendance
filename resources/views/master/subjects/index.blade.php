@extends('layouts.app')

@section('title', 'Quản lý Môn học')
@section('page', 'subjects')

@section('content')
    <x-crud-page
        title="Quản lý Môn học"
        breadcrumb="Môn học"
        :columns="['Mã môn', 'Tên môn', 'Tín chỉ']"
        :fields="[
            ['name' => 'code', 'label' => 'Mã môn', 'required' => true],
            ['name' => 'name', 'label' => 'Tên môn', 'required' => true],
            ['name' => 'credits', 'label' => 'Số tín chỉ', 'type' => 'number', 'required' => true],
            ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea'],
        ]"
    />
@endsection
