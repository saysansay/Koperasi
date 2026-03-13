@extends('layouts.app')
@section('title', __('app.edit_member'))
@section('content')
<form method="POST" action="{{ route('members.update', $member) }}">@csrf @method('PUT') @include('members.form')</form>
@endsection
