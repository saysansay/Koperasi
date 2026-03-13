@extends('layouts.app')
@section('title', __('app.edit_sales_payment'))
@section('content')
<form method="POST" action="{{ route('sale-payments.update', $payment) }}">@csrf @method('PUT') @include('sales-payments.form')</form>
@endsection
