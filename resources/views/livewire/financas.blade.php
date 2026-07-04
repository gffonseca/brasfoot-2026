<div wire:ignore.self>
  <h2 class="font-bold text-lg mb-4">💰 Finanças — {{ $this->club->nome }}</h2>
  <div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="bg-slate-800 rounded-xl p-4"><div class="text-xs text-slate-400">Caixa atual</div>
      <div class="text-xl font-black text-emerald-400">R$ {{ number_format($this->club->caixa/100,2,',','.') }}</div></div>
    <div class="bg-slate-800 rounded-xl p-4"><div class="text-xs text-slate-400">Folha semanal</div>
      <div class="text-xl font-black text-red-300">R$ {{ number_format($this->folhaSemanal/100,2,',','.') }}</div></div>
    <div class="bg-slate-800 rounded-xl p-4"><div class="text-xs text-slate-400">Valor do elenco</div>
      <div class="text-xl font-black">R$ {{ number_format($this->valorElenco/100,2,',','.') }}</div></div>
  </div>

  <div class="bg-slate-800 rounded-xl p-4 mb-4">
    <h3 class="font-bold text-sm mb-2">Evolução do caixa</h3>
    <div wire:ignore>
      <canvas id="caixaChart" height="90"></canvas>
    </div>
  </div>

  <div class="bg-slate-800 rounded-xl p-4">
    <h3 class="font-bold text-sm mb-2">Histórico</h3>
    <div class="max-h-[320px] overflow-auto text-sm">
      @forelse($this->historico as $h)
        <div class="flex items-center gap-2 py-1 border-b border-dashed border-slate-700">
          <span class="text-emerald-400 font-bold text-xs w-10">R{{ $h->rodada }}</span>
          <span class="text-[11px] px-2 py-0.5 rounded bg-slate-700">{{ $h->tipo }}</span>
          <span>{{ $h->descricao }}</span>
          <span class="ml-auto {{ $h->valor>=0?'text-emerald-400':'text-red-300' }}">R$ {{ number_format($h->valor/100,2,',','.') }}</span>
        </div>
      @empty
        <p class="text-slate-400">Sem movimentações ainda. Jogue algumas rodadas.</p>
      @endforelse
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
  <script>
    (function(){
      const labels = @json($this->serie['labels']);
      const dados  = @json($this->serie['valores']);
      const el = document.getElementById('caixaChart');
      if (!el || !window.Chart) return;
      if (el._chart) el._chart.destroy();
      el._chart = new Chart(el, {
        type: 'line',
        data: { labels, datasets: [{ label: 'Caixa (R$)', data: dados,
          borderColor: '#2ee6a6', backgroundColor: 'rgba(46,230,166,.15)', fill: true, tension: .25, pointRadius: 2 }]},
        options: { plugins:{legend:{display:false}}, scales:{ x:{ticks:{color:'#94a3b8'}}, y:{ticks:{color:'#94a3b8'}} } }
      });
    })();
  </script>
</div>
