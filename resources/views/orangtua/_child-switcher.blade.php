@if ($children->count() > 1)
    <form method="POST" action="{{ route('ortu.child.switch') }}" class="w-full sm:w-auto">
        @csrf
        <label class="block text-xs font-medium text-slate-500 dark:text-white/50 mb-1">Lihat sebagai anak</label>
        <select name="student_id" onchange="this.form.submit()"
            class="w-full sm:w-64 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            @foreach ($children as $c)
                <option value="{{ $c->id }}" {{ $child && $c->id === $child->id ? 'selected' : '' }}>
                    {{ $c->name }} · {{ $c->classRoom?->class_name ?? '-' }}
                </option>
            @endforeach
        </select>
    </form>
@endif
