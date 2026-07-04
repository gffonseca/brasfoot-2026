<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Brasfoot 2026</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
<div class="max-w-5xl mx-auto p-5">
  <header class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded-lg bg-emerald-500 grid place-items-center text-slate-900 font-black">B</div>
    <div><h1 class="text-xl font-black text-emerald-400 leading-tight">Brasfoot 2026</h1>
    <div class="text-xs text-slate-400">Protótipo Laravel/Livewire</div></div>
  </header>
  <nav class="flex flex-wrap gap-2 mb-5 text-sm">
    <a href="{{ route('game.home') }}" class="px-3 py-1.5 rounded bg-slate-700">📊 Classificação</a>
    @if($season)
      <a href="{{ route('game.escalacao') }}" class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">👕 Escalação</a>
      <a href="{{ route('game.partida') }}" class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">🏟️ Partida</a>
      <a href="{{ route('game.mercado') }}" class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">💱 Mercado</a>
      <a href="{{ route('game.financas') }}" class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700">💰 Finanças</a>
    @endif
  </nav>

  @if(!$season)
    <div class="bg-slate-800 rounded-xl p-5">
      <h2 class="font-bold mb-3">Escolha seu clube</h2>
      <p class="text-xs text-slate-400 mb-3">A divisão inicial depende do clube. Séries A e B com acesso/rebaixamento (2 sobem, 2 descem).</p>
      <form method="POST" action="{{ route('game.start') }}" class="flex flex-wrap gap-2 items-center">
        @csrf
        <select name="club_id" class="bg-slate-700 rounded px-3 py-2">
          @foreach(['A'=>'Série A','B'=>'Série B'] as $d => $label)
            <optgroup label="{{ $label }}">
              @foreach($clubs->where('divisao',$d) as $c)
                <option value="{{ $c->id }}">{{ $c->nome }} — {{ $c->uf }} (força {{ $c->base_forca }})</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
        <button class="bg-emerald-500 text-slate-900 font-bold px-4 py-2 rounded">Iniciar temporada</button>
      </form>
    </div>
  @else
    <div class="bg-slate-800 rounded-xl p-5 mb-5 flex flex-wrap gap-6 items-center text-sm">
      <div><div class="text-xs text-slate-400">Competição</div><div class="font-bold">{{ $season->nome }}</div></div>
      <div><div class="text-xs text-slate-400">Rodada</div><div class="font-bold">{{ $season->rodada_atual }}/{{ $season->rounds()->count() }}</div></div>
      <div><div class="text-xs text-slate-400">Status</div><div class="font-bold">{{ $season->status }}</div></div>
      @if($season->status !== 'encerrada')
        <a href="{{ route('game.partida') }}" class="ml-auto bg-emerald-500 text-slate-900 font-bold px-4 py-2 rounded">▶ Ir para a partida</a>
      @endif
    </div>

    {{-- Painel de competições --}}
    <div class="bg-slate-800 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-center">
      <span class="text-sm font-bold">Competições:</span>
      @if($season->status === 'encerrada')
        <form method="POST" action="{{ route('game.advance') }}">@csrf
          <button class="bg-emerald-500 text-slate-900 font-bold px-3 py-1.5 rounded text-sm">🏆 Encerrar e avançar temporada →</button>
        </form>
        <span class="text-xs text-slate-400">Aplica acesso/rebaixamento e inicia o próximo ano.</span>
      @else
        <span class="text-xs text-slate-400">Termine a competição atual para avançar de temporada.</span>
      @endif
      @if($podeEstadual && $season->tipo !== 'estadual')
        <form method="POST" action="{{ route('game.estadual') }}" class="ml-auto"
              onsubmit="return confirm('Iniciar o Campeonato Estadual? A competição atual será substituída.')">@csrf
          <button class="bg-sky-500 text-slate-900 font-bold px-3 py-1.5 rounded text-sm">🏟️ Iniciar Estadual ({{ $ufNome }})</button>
        </form>
      @endif
    </div>

    <div class="bg-slate-800 rounded-xl p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold">{{ $season->nome }}</h2>
        @if($season->tipo === 'nacional')
          <span class="text-xs text-slate-400">🟢 acesso · 🔴 rebaixamento</span>
        @endif
      </div>
      <table class="w-full text-sm">
        <thead class="text-slate-400 text-xs"><tr>
          <th class="text-left py-1">#</th><th class="text-left">Clube</th>
          <th>P</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th></tr></thead>
        <tbody>
        @php $n = count($tabela); @endphp
        @foreach($tabela as $i => $t)
          @php
            $zona = '';
            if($season->tipo==='nacional'){
              if($season->divisao==='B' && $i<2) $zona='border-l-4 border-emerald-500';
              elseif($season->divisao==='A' && $i>=$n-2) $zona='border-l-4 border-red-500';
            }
          @endphp
          <tr class="border-t border-slate-700 {{ $zona }} {{ $t['id']===$season->club_do_usuario_id ? 'bg-emerald-500/10' : '' }}">
            <td class="py-1 pl-2">{{ $i+1 }}</td><td>{{ $t['nome'] }}</td>
            <td class="text-center font-bold">{{ $t['pts'] }}</td><td class="text-center">{{ $t['j'] }}</td>
            <td class="text-center">{{ $t['v'] }}</td><td class="text-center">{{ $t['e'] }}</td><td class="text-center">{{ $t['d'] }}</td>
            <td class="text-center">{{ $t['gp'] }}</td><td class="text-center">{{ $t['gc'] }}</td>
            <td class="text-center">{{ ($t['gp']-$t['gc']>0?'+':'').($t['gp']-$t['gc']) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
</body>
</html>
