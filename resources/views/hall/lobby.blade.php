@extends('layouts.hall')
@section('title', 'Lobby')

@section('content')
  <x-hall-scene :scene="config('hall.lobby')">
    <div class="absolute start-4 top-4 z-10 rounded-xl bg-black/50 px-4 py-2 text-white backdrop-blur">
      <p class="text-xs uppercase tracking-widest opacity-80">Welcome</p>
      <p class="text-base font-bold md:text-lg">{{ auth()->user()->name }}</p>
    </div>
  </x-hall-scene>
@endsection
