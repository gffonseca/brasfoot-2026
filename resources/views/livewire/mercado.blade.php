<div>
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">💱 Mercado de transferências</h2>
    <div class="text-sm">Caixa: <b class="text-emerald-400">R$ {{ number_format($this->caixa/100,2,',','.') }}</b></div>
  </div>
  @if($flash)
    <div class="bg-sky-500/15 border border-sky-500/40 text-sky-200 text-sm rounded-lg px-3 py-2 mb-4">{{ $flash }}</div>
  @endif
  <div class="grid md:grid-cols-2 gap-4">
    {{-- Comprar --}}
    <div class="bg-slate-800 rounded-xl p-4">
      <h3 class="font-bold mb-2 text-sm">Contratações</h3>
      <div class="max-h-[460px] overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-slate-400 text-xs sticky top-0 bg-slate-800"><tr>
            <th class="text-left">Pos</th><th class="text-left">Jogador</th><th>Clube</th><th>Ida</th><th>OVR</th><th class="text-right">Preço</th><th></th></tr></thead>
          <tbody>
          @foreach($this->alvos as $p)
            @php $pode = $this->caixa >= $p->valor; @endphp
            <tr class="border-t border-slate-700">
              <td><span class="text-xs font-bold px-1.5 py-0.5 rounded bg-slate-700">{{ $p->posicao }}</span></td>
              <td>{{ $p->nome }}</td><td class="text-center text-xs">{{ $p->club->abbr ?? '-' }}</td>
              <td class="text-center">{{ $p->idade }}</td>
              <td class="text-center font-bold {{ $p->over>=75?'text-emerald-400':($p->over>=62?'text-amber-300':'text-red-300') }}">{{ $p->over }}</td>
              <td class="text-right">R$ {{ number_format($p->valor/100,0,',','.') }}</td>
              <td class="text-right">
                <button wire:click="comprar({{ $p->id }})" wire:loading.attr="disabled" @disabled(!$pode)
                  class="text-xs px-2 py-1 rounded {{ $pode?'bg-emerald-500 text-slate-900 font-bold':'bg-slate-700 text-slate-500' }}">Comprar</button>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
    {{-- Vender --}}
    <div class="bg-slate-800 rounded-xl p-4">
      <h3 class="font-bold mb-2 text-sm">Vender (recebe 85%)</h3>
      <p class="text-xs text-slate-400 mb-2">Mantenha ao menos 14 jogadores.</p>
      <div class="max-h-[430px] overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-slate-400 text-xs sticky top-0 bg-slate-800"><tr>
            <th class="text-left">Pos</th><th class="text-left">Jogador</th><th>OVR</th><th class="text-right">Venda</th><th></th></tr></thead>
          <tbody>
          @foreach($this->elenco as $p)
            <tr class="border-t border-slate-700">
              <td><span class="text-xs font-bold px-1.5 py-0.5 rounded bg-slate-700">{{ $p->posicao }}</span></td>
              <td>{{ $p->nome }}</td>
              <td class="text-center font-bold {{ $p->over>=75?'text-emerald-400':($p->over>=62?'text-amber-300':'text-red-300') }}">{{ $p->over }}</td>
              <td class="text-right text-emerald-400">R$ {{ number_format($p->valor*0.85/100,0,',','.') }}</td>
              <td class="text-right"><button wire:click="vender({{ $p->id }})" wire:loading.attr="disabled"
                class="text-xs px-2 py-1 rounded bg-slate-700 hover:bg-slate-600">Vender</button></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
