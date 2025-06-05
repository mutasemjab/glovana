@extends('layouts.admin')
@section('title')
الرئيسية
@endsection

@section('css')
<style>


.dashboard {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 20px;
    background-color: #e9f7f6;
    border-radius: 10px;
}

.card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
}

.card h2 {
    font-size: 18px;
    color: #555;
    margin-bottom: 10px;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

</style>
@endsection

@section('contentheaderlink')
<a href="{{ route('admin.dashboard') }}"> الرئيسية </a>
@endsection

@section('contentheaderactive')
عرض
@endsection



@section('content')
<div class="dashboard">
    <div class="card">
        <h2>Students</h2>
        <p> {{ $usersCount }}</p>
    </div>
    <div class="card">
        <h2>Drivers</h2>
        <p>{{ $driversCount }}</p>
    </div>
 
   
</div>


@endsection



