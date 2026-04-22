@extends('layouts.base')

@section('sidebar')
    <x-layout.sidebar role="intern" />
@endsection

@section('topbar')
    <x-layout.topbar :title="trim($__env->yieldContent('title', 'Intern Panel'))" subtitle="Intern workspace" />
@endsection
