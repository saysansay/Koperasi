@extends('layouts.app')
@section('title', __('app.create_journal'))
@section('content')
<form method="POST" action="{{ route('journals.store') }}">@csrf @include('journals.form')</form>
@endsection
