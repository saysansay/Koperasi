@extends('layouts.app')
@section('title', __('app.create_member'))
@section('content')
<form method="POST" action="{{ route('members.store') }}">@csrf @include('members.form')</form>
@endsection
