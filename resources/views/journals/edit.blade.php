@extends('layouts.app')
@section('title', __('app.edit_journal'))
@section('content')
<form method="POST" action="{{ route('journals.update', $journal) }}">@csrf @method('PUT') @include('journals.form')</form>
@endsection
