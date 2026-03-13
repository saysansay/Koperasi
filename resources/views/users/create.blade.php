@extends('layouts.app')
@section('title', __('app.create_user'))
@section('content')
<form method="POST" action="{{ route('users.store') }}">@csrf @include('users.form')</form>
@endsection
