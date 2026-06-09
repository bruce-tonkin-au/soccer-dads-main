@extends('admin.layout')
@section('title', 'New Newsletter')
@section('content')

@include('admin.newsletters._form', ['action' => '/admin/newsletters', 'method' => 'POST'])

@endsection
