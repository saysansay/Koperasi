@extends('layouts.app')
@section('title', __('app.create_loan'))
@section('content')
<form method="POST" action="{{ route('loans.store') }}">@csrf @include('loans.form')</form>
@endsection
