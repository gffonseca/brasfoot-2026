<div class="grid md:grid-cols-5 gap-4" x-data="{}"
     x-on:flash.window="alert($event.detail.msg)">
  {{-- Elenco --}}
  <div class="md:col-span-3 bg-slate-800 rounded-xl p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-bold">Elenco</h2>
      <div class="flex gap-2 items-center">
        @foreach(['4-4-2','4-3-3','3-5-2'] as $f)
          <button wire:click="trocarFormacao('{{ $f }}')"
            class="text-xs px-2 py-1 rounded {{ $formacao===$f ? 'bg-emerald-500 text-slate-900 font-bold' : 'bg-slate-700' }}">{{ $f }}</button>
        @endforeach
        <button wire:click="melhorXI" class="text-xs px-2 py-1 rounded bg-sky-500 text-slate-900 font-bold">⚡ Melhor XI</button>
      </div>
    </div>
    <p class="text-xs text-slate-400 mb-2">Clique num reserva para escalá-lo no lugar do pior titular do setor.</p>
    <div class="max-h-[460px] overflow-auto">
      <table class="w-full text-sm">
        <thead class="text-slate-400 text-xs sticky top-0 bg-slate-800">
          <tr><th></th><th class="text-left">Pos</th><th class="text-left">Jogador</th><th>Ida</th><th>OVR</th><th class="text-left">Traços</th></tr>
        </thead>
        <tbody>
        @foreach($this->players as $p)
          @php $tit = in_array($p->id, $starters); @endphp
          <tr class="border-t border-slate-700 {{ $tit ? 'bg-emerald-500/10' : 'cursor-pointer hover:bg-slate-700/40' }}"
              @if(!$tit) wire:click="escalar({{ $p->id }})" @endif>
            <td class="py-1 text-center">{{ $tit ? '⭐' : '·' }}</td>
            <td><span class="text-xs font-bold px-1.5 py-0.5 rounded bg-slate-700">{{ $p->posicao }}</span></td>
            <td>{{ $p->nome }}</td>
            <td class="text-center">{{ $p->idade }}</td>
            <td class="text-center"><span class="font-bold px-1.5 rounded {{ $p->over>=75?'text-emerald-400':($p->over>=62?'text-amber-300':'text-red-300') }}">{{ $p->over }}</span></td>
            <td class="text-xs text-sky-300">{{ trim($p->traco1.' '.$p->traco2) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Campo --}}
  <div class="md:col-span-2 bg-slate-800 rounded-xl p-4">
    <h2 class="font-bold mb-3">Campo — {{ $formacao }}</h2>
    <div class="relative rounded-lg border border-emerald-800 bg-gradient-to-b from-emerald-900 to-emerald-950" style="aspect-ratio:3/4;max-height:420px">
      @php
        $coords = [
          '4-4-2'=>[[50,92],[18,74],[39,78],[61,78],[82,74],[18,48],[39,52],[61,52],[82,48],[38,22],[62,22]],
          '4-3-3'=>[[50,92],[18,74],[39,78],[61,78],[82,74],[30,52],[50,55],[70,52],[22,24],[50,18],[78,24]],
          '3-5-2'=>[[50,92],[30,76],[50,80],[70,76],[12,52],[34,54],[50,50],[66,54],[88,52],[40,22],[60,22]],
        ][$formacao] ?? [];
      @endphp
      @foreach($this->xiObjs as $i => $p)
        @php $c = $coords[$i] ?? [50,50]; @endphp
        <div class="absolute -translate-x-1/2 -translate-y-1/2 text-center" style="left:{{ $c[0] }}%;top:{{ $c[1] }}%">
          <div class="w-8 h-8 rounded-full grid place-items-center text-xs font-bold bg-emerald-800 border-2 border-emerald-400 mx-auto">{{ $p['over'] }}</div>
          <div class="text-[9px] mt-0.5 text-emerald-100 truncate w-16">{{ explode(' ', $p['nome'])[0] }}</div>
        </div>
      @endforeach
    </div>
    <div class="mt-3 text-sm">
      <div class="flex justify-between"><span class="text-slate-400">Força defensiva</span><b>{{ round($this->forca['def']) }}</b></div>
      <div class="h-1.5 bg-slate-700 rounded mt-1 mb-2"><div class="h-full bg-emerald-400 rounded" style="width:{{ min(100,$this->forca['def']) }}%"></div></div>
      <div class="flex justify-between"><span class="text-slate-400">Força ofensiva</span><b>{{ round($this->forca['atq']) }}</b></div>
      <div class="h-1.5 bg-slate-700 rounded mt-1"><div class="h-full bg-sky-400 rounded" style="width:{{ min(100,$this->forca['atq']) }}%"></div></div>
    </div>
  </div>
</div>
