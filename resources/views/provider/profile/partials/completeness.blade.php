{{-- Completeness score: progress bar + list of what is missing. --}}
<section class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-2">Profile completeness: {{ $score }}%</h2>

    <div class="w-full bg-gray-200 rounded h-4">
        <div class="bg-green-500 h-4 rounded" style="width: {{ $score }}%"></div>
    </div>

    @if (count($missing) > 0)
        <p class="mt-3 text-sm font-medium">To reach 100%:</p>
        <ul class="list-disc ml-5 text-sm text-gray-700">
            @foreach ($missing as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else
        <p class="mt-3 text-sm text-green-700">Your profile is complete.</p>
    @endif
</section>
