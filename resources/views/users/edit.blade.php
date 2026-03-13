@extends('layouts.app')
@section('title', __('app.edit_user'))
@section('content')
<form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT') @include('users.form')</form>
@endsection
