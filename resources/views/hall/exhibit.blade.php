@extends('layouts.hall')
@section('title', 'Exhibit Halls')

@section('content')
  <x-hall-scene :scene="config('hall.exhibit')">
    <div class="absolute inset-x-0 top-6 z-10 text-center text-white drop-shadow-lg">
      <h1 class="text-2xl font-extrabold md:text-4xl">Exhibit Halls</h1>
      <p class="text-sm md:text-lg">Company booths will appear here.</p>
    </div>
  </x-hall-scene>
@endsection
