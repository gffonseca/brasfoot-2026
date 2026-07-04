<div class="grid md:grid-cols-2 gap-4">
  <div class="bg-slate-800 rounded-xl p-5">
    <h2 class="font-bold mb-3">Próxima partida</h2>
    @php $jogo = $this->proximoJogo; @endphp
    @if(!$jogo)
      <div class="text-center py-8 text-slate-400">🏆 Campeonato encerrado</div>
    @else
      @php $souHome = $jogo->home_club_id === $season->club_do_usuario_id; @endphp
      <div class="rounded-lg bg-slate-900 p-5 text-center mb-4">
        <div class="flex justify-center items-center gap-5">
          <div><div class="w-11 h-11 rounded-lg grid place-items-center font-bold text-white mx-auto" style="background:{{ $jogo->homeClub->cor }}">{{ substr($jogo->homeClub->abbr,0,2) }}</div><div class="text-xs mt-1">{{ $jogo->homeClub->nome }}</div></div>
          <div class="text-2xl font-black text-slate-500">×</div>
          <div><div class="w-11 h-11 rounded-lg grid place-items-center font-bold text-white mx-auto" style="background:{{ $jogo->awayClub->cor }}">{{ substr($jogo->awayClub->abbr,0,2) }}</div><div class="text-xs mt-1">{{ $jogo->awayClub->nome }}</div></div>
        </div>
        <div class="text-xs text-slate-400 mt-2">{{ $souHome ? '🏟️ Você é o mandante' : '✈️ Você joga fora' }}</div>
      </div>

      <label class="text-xs text-slate-400">Postura tática</label>
      <input type="range" min="-1" max="1" step="1" wire:model.live="tatica" class="w-full accent-emerald-400">
      <div class="flex justify-between text-[11px] text-slate-400 mb-3"><span>🛡️ Defensiva</span><span>⚖️ Equilibrada</span><span>⚔️ Ofensiva</span></div>

      <div class="flex items-center gap-2 mb-4">
        <label class="text-xs text-slate-400">Ingresso R$</label>
        <input type="number" min="5" max="300" step="5" wire:model.live="ingresso" class="bg-slate-700 rounded px-2 py-1 w-24 text-sm">
        <span class="text-xs text-slate-400">
          @if($souHome) Público estimado: {{ number_format($this->publicoEstimado,0,',','.') }} @else (sem bilheteria fora) @endif
        </span>
      </div>

      <button wire:click="jogar" wire:loading.attr="disabled"
        class="bg-emerald-500 text-slate-900 font-bold px-5 py-2.5 rounded-lg w-full">
        <span wire:loading.remove>▶️ Jogar partida</span>
        <span wire:loading>Simulando…</span>
      </button>
    @endif
  </div>

  <div class="bg-slate-800 rounded-xl p-5">
    <h2 class="font-bold mb-3">Súmula</h2>
    @php $m = $this->ultimoMatch; @endphp
    @if(!$m)
      <p class="text-slate-400 text-sm">Jogue a partida para ver o resultado, gols e renda.</p>
    @else
      @php
        $souHome = $m->home_club_id === $season->club_do_usuario_id;
        $meus = $souHome ? $m->gols_home : $m->gols_away;
        $sofri = $souHome ? $m->gols_away : $m->gols_home;
        $res = $meus > $sofri ? 'VITÓRIA' : ($meus < $sofri ? 'DERROTA' : 'EMPATE');
        $cor = $meus > $sofri ? 'text-emerald-400' : ($meus < $sofri ? 'text-red-400' : 'text-amber-300');
      @endphp
      <div class="rounded-lg bg-slate-900 p-4 text-center">
        <div class="text-xs text-slate-400">{{ $m->homeClub->nome }} × {{ $m->awayClub->nome }}</div>
        <div class="text-4xl font-black {{ $cor }}">{{ $m->gols_home }} <span class="text-slate-500 text-2xl">×</span> {{ $m->gols_away }}</div>
        <div class="text-xs font-bold {{ $cor }}">{{ $res }}</div>
      </div>
      <h3 class="font-bold text-sm mt-4 mb-1">Gols</h3>
      @forelse($m->eventos ?? [] as $e)
        <div class="text-sm py-1 border-b border-dashed border-slate-700">
          <span class="text-emerald-400 font-bold">{{ $e['min'] }}'</span> ⚽
          <b>{{ $e['time']==='home' ? $m->homeClub->abbr : $m->awayClub->abbr }}</b> — {{ $e['autor'] }}
        </div>
      @empty
        <p class="text-slate-400 text-sm">Sem gols.</p>
      @endforelse
      @if($m->renda)
        <h3 class="font-bold text-sm mt-4 mb-1">Bilheteria</h3>
        <div class="text-sm flex justify-between"><span>{{ number_format($m->publico,0,',','.') }} torcedores</span>
          <span class="text-emerald-400">R$ {{ number_format($m->renda/100,2,',','.') }}</span></div>
      @endif
    @endif
  </div>
</div>
