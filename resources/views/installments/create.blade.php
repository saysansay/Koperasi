@extends('layouts.app')
@section('title', __('app.create_installment'))
@section('content')
<form method="POST" action="{{ route('installments.store') }}">@csrf @include('installments.form')</form>
@endsection
