{{-- @extends('errors::minimal') --}}
@extends('errors.layout')

@section('title', __('Unprocessable Content'))
@section('code', '422')
@section('message', __($exception->getMessage() ?: 'Unprocessable Content'))
