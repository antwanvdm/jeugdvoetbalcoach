
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Nieuwe formatie</h1>
    <form action="{{ route('formations.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
        @csrf
        @include('formations._form')

        <div class="flex gap-2 mt-4">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('formations.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
@endsection
