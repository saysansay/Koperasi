@extends('layouts.app')
@section('title', __('app.create_sales_payment'))
@section('content')
<form method="POST" action="{{ route('sale-payments.store') }}">@csrf @include('sales-payments.form')</form>
@endsection
