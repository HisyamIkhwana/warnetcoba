@extends(Auth::user()->role == 'admin' ? 'layouts.admin' : 'layouts.warnet')

@section('title', 'Manajemen Sesi')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold leading-tight tracking-tight text-white">Manajemen Sesi</h1>
            <p class="mt-2 text-sm text-gray-400">Kelola sesi penggunaan komputer yang sedang berjalan atau sudah selesai.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            @if(in_array(Auth::user()->role, ['admin', 'warnet']))
                <button type="button" onclick="openModal('addSesiModal')" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors duration-200">
                    <i class="fas fa-plus-circle mr-2 -ml-1"></i>Tambah Sesi
                </button>
            @endif
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="rounded-md bg-green-500/20 p-4 mb-6"><p class="text-sm font-medium text-green-400">{{ session('success') }}</p></div>
    @endif
    @if ($errors->any())
        <div class="rounded-md bg-red-500/20 p-4 mb-6">
            <ul class="list-disc list-inside text-sm text-red-400">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-white/10 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-800">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Komputer</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Jadwal Sesi</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-center text-white">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-gray-900/50">
                            @forelse ($sesis as $sesi)
                            <tr class="hover:bg-gray-800/50 transition-colors duration-200">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-medium text-white">{{ $sesi->komputer->merk ?? 'N/A' }}</div>
                                    <div class="text-gray-400">{{ $sesi->komputer->spesifikasi ?? 'Spesifikasi tidak ada' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                                    {{-- Menggabungkan waktu & durasi --}}
                                    <div>Mulai: {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('d M, H:i') }}</div>
                                    <div class="text-gray-400">Selesai: {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('d M, H:i') }} ({{$sesi->durasi}} Jam)</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    {{-- Menggunakan Badge untuk Status Sesi --}}
                                    @if(now()->gt($sesi->waktu_selesai))
                                        <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Selesai</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-400/20 animate-pulse">Aktif</span>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                                    <button onclick="openEditModal({{ $sesi->toJson() }}, {{ $komputers->toJson() }})" class="text-indigo-400 hover:text-indigo-300">Edit</button>
                                    <form action="{{ route('sesi.destroy', $sesi->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Yakin ingin menghapus sesi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="whitespace-nowrap px-3 py-4 text-sm text-center text-gray-400">Belum ada data sesi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('sesi.modals')
</div>
@endsection

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openEditModal(sesi, komputers) {
            const form = document.getElementById('editSesiForm');
            form.action = `/sesi/${sesi.id}`;
            form.querySelector('[name="komputer_id"]').value = sesi.komputer_id;
            form.querySelector('[name="durasi"]').value = sesi.durasi;
            openModal('editSesiModal');
        }
        @if($errors->any())
            @if(old('_method') === 'PUT') openModal('editSesiModal');
            @else openModal('addSesiModal'); @endif
        @endif
    </script>
@endpush
