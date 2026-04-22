@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="hr" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'HR Panel'))" subtitle="HR workspace" />
@endsection
