@extends('layouts.app')
@section('title', __('app.edit_account'))
@section('content')
<form method="POST" action="{{ route('accounts.update', $account) }}">@csrf @method('PUT') @include('accounts.form')</form>
@endsection
