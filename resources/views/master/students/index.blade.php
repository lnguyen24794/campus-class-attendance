@extends('layouts.app')

@section('title', 'Quản lý Sinh viên')
@section('page', 'students')

@section('content')
    <x-crud-page
        title="Quản lý Sinh viên"
        breadcrumb="Sinh viên"
        :columns="['Mã SV', 'Họ tên', 'Email']"
        :fields="[
            ['name' => 'code', 'label' => 'Mã SV', 'required' => true],
            ['name' => 'name', 'label' => 'Họ tên', 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ['name' => 'password', 'label' => 'Mật khẩu', 'type' => 'password'],
        ]"
    />
@endsection
