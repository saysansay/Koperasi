@extends('layouts.app')
@section('title', __('app.create_savings_transaction'))
@section('content')
<form method="POST" action="{{ route('savings.store') }}">@csrf @include('savings.form')</form>
@endsection
