@extends('layouts.app')
@section('title', __('app.edit_installment'))
@section('content')
<form method="POST" action="{{ route('installments.update', $installment) }}">@csrf @method('PUT') @include('installments.form')</form>
@endsection
