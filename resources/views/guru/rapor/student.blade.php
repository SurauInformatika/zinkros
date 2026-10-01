@extends('layouts.rapor')

@section('title', 'Rapor — ' . $student->name)

@section('content')
@include('rapor.render', ['blocks' => $blocks, 'ctx' => $ctx])
@endsection