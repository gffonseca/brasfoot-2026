<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Brasfoot 2026' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  @livewireStyles
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-5">
    <header class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-lg bg-emerald-500 grid place-items-center text-slate-900 font-black">B</div>
      <div>
        <h1 class="text-xl font-black text-emerald-400 leading-tight">Brasfoot 2026</h1>
        <div class="text-xs text-slate-400">Protótipo Laravel/Livewire</div>
      </div>
    </header>
    <nav class="flex gap-2 mb-5 text-sm">
      <a href="{{ route('game.home') }}"       class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">📊 Classificação</a>
      <a href="{{ route('game.escalacao') }}"  class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">👕 Escalação</a>
      <a href="{{ route('game.partida') }}"    class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">🏟️ Partida</a>
      <a href="{{ route('game.mercado') }}"    class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">💱 Mercado</a>
      <a href="{{ route('game.financas') }}"   class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">💰 Finanças</a>
    </nav>
    {{ $slot }}
  </div>
  @livewireScripts
</body>
</html>
