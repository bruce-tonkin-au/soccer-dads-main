@extends('admin.layout')
@section('title', 'Edit Newsletter')
@section('content')

@include('admin.newsletters._form', ['action' => '/admin/newsletters/' . $newsletter->newsletterID, 'method' => 'PUT'])

@endsection
