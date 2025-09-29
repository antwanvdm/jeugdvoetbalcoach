@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Positions</h1>
    <a href="{{ route('positions.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">New Position</a>
</div>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="border-b">
            <th class="text-left p-3">Name</th>
            <th class="text-right p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($positions as $position)
            <tr class="border-b">
                <td class="p-3">{{ $position->name }}</td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('positions.show', $position) }}">Show</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('positions.edit', $position) }}">Edit</a>
                    <form action="{{ route('positions.destroy', $position) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Delete this position?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="p-3 text-center text-gray-500">No positions yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">{{ $positions->links() }}</div>
@endsection
