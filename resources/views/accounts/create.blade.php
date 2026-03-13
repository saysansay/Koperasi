@extends('layouts.app')
@section('title', __('app.create_account'))
@section('content')
<form method="POST" action="{{ route('accounts.store') }}">@csrf @include('accounts.form')</form>
@endsection
