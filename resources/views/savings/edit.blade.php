@extends('layouts.app')
@section('title', __('app.edit_savings_transaction'))
@section('content')
<form method="POST" action="{{ route('savings.update', $saving) }}">@csrf @method('PUT') @include('savings.form')</form>
@endsection
