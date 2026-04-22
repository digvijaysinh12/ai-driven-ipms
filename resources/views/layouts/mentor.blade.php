@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="mentor" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'Mentor Panel'))" subtitle="Mentor workspace" />
@endsection
