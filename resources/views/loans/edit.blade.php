@extends('layouts.app')
@section('title', __('app.edit_loan'))
@section('content')
<form method="POST" action="{{ route('loans.update', $loan) }}">@csrf @method('PUT') @include('loans.form')</form>
@endsection
