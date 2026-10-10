@extends('layouts.app')

@section('title', 'Quản lý Giảng viên')
@section('page', 'lecturers')

@section('content')
    <x-crud-page
        title="Quản lý Giảng viên"
        breadcrumb="Giảng viên"
        :columns="['Mã GV', 'Họ tên', 'Email']"
        :fields="[
            ['name' => 'code', 'label' => 'Mã GV', 'required' => true],
            ['name' => 'name', 'label' => 'Họ tên', 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ['name' => 'password', 'label' => 'Mật khẩu', 'type' => 'password'],
        ]"
    />
@endsection
