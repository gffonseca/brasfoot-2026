# JogaFoot 2026 — Fase 0 (pacote drop-in Laravel)

Núcleo do jogo pronto para plugar num **Laravel 11** novo e subir no **Railway**.
A engine de simulação é um porte fiel do `engine.js` já validado (28.000 jogos, invariantes OK)
e vem com **testes PHPUnit** que replicam os mesmos invariantes.

## O que tem aqui

```
app/Game/                 Engine pura (sem framework, 100% testável)
  Rng.php                 RNG mulberry32 com seed (paridade com o JS)
  SimulationEngine.php    Poisson p/ gols, força por setor, curva de público
  LeagueScheduler.php     Calendário de returno duplo (round-robin)
  LeagueTable.php         Tabela: aplicar resultado + classificar
  Services/
    StartSeasonAction.php Cria temporada + calendário + partidas
    PlayRoundAction.php   Joga a próxima rodada (simula tudo, atualiza finanças/forma)
    FinanceService.php    Bilheteria e folha
app/Models/               Eloquent: Club, Player, Season, Round, GameMatch
app/Http/Controllers/     GameController (dashboard mínimo end-to-end)
database/migrations/      Schema completo (clubs, players, seasons, rounds, matches, transfers, ledger, game_states)
database/seeders/         8 clubes fictícios + elencos gerados
resources/views/game/     dashboard.blade.php (Tailwind via CDN)
routes/web.php            Rotas do jogo
tests/Unit/               SimulationEngineTest.php (invariantes)
railway.json, nixpacks.toml, Procfile, .env.example
```

## Instalação

```bash
# 1) Criar o Laravel novo
composer create-project laravel/laravel brasfoot-2026
cd brasfoot-2026

# 2) Copiar por cima as pastas app/, database/, routes/, resources/, tests/
#    e os arquivos railway.json, nixpacks.toml, Procfile, .env.example deste pacote.

# 3) Banco (local: SQLite é o mais rápido para começar)
touch database/database.sqlite
# no .env: DB_CONNECTION=sqlite  (e remova as outras DB_*)

php artisan key:generate
php artisan migrate --seed

# 4) Rodar os testes da engine (o mais importante)
php artisan test --filter=SimulationEngineTest

# 5) Subir o servidor
php artisan serve
# abra http://127.0.0.1:8000 → escolha o clube → jogue as rodadas
```

## Deploy no Railway

1. `railway init` (ou conecte o repo no dashboard).
2. Adicione o plugin **PostgreSQL**.
3. Em Variables, configure as `DB_*` referenciando `${{Postgres.PGHOST}}` etc. (ver `.env.example`), defina `APP_KEY` (`php artisan key:generate --show`).
4. Deploy. O `railway.json` roda `migrate` + `seed` no start.

## Como isto mapeia o protótipo

| Protótipo (JS)        | Produção (PHP)                          |
|-----------------------|-----------------------------------------|
| `BF` (engine.js)      | `App\Game\SimulationEngine` + `Rng`     |
| `gerarCalendario`     | `App\Game\LeagueScheduler`              |
| estado `G` + export   | tabelas Eloquent + `game_states` (JSON) |
| `render*()` (DOM)     | Blade/Livewire (Fase 3)                 |

## Notas

- Valores monetários são armazenados em **centavos** (inteiro) para evitar erro de ponto flutuante.
- Base de dados de clubes/jogadores é **fictícia** de propósito (ver seção de IP no Documento Técnico).
- Próximo passo (Fase 1→3): migrar o dashboard para **Livewire** e portar as telas ricas do protótipo.

## Telas ricas (Livewire) — adicionadas na Fase 1→3

Requer `composer require livewire/livewire`.

```
app/Livewire/Escalacao.php        Monta o XI, troca titulares, mostra campo e força
app/Livewire/Partida.php          Próximo jogo, tática, ingresso, joga a rodada, súmula
app/Models/Lineup.php             Escalação salva do usuário (formação, tática, ingresso, XI)
resources/views/livewire/*        Views dos componentes (Tailwind)
resources/views/layouts/app.blade.php   Layout base (@livewireStyles/@livewireScripts)
```

Rotas: `/` (classificação), `/escalacao`, `/partida`.
Fluxo: escolha o clube na home → **Escalação** (monte o time) → **Partida** (jogue a rodada) → volta pra **Classificação**.
A `PlayRoundAction` usa o XI salvo em `lineups`; a CPU usa o melhor XI automático.

## Mercado e Finanças (Fase 2→3)

```
app/Game/Services/TransferService.php   Compra/venda (85% na venda, mínimo 14 jogadores)
app/Livewire/Mercado.php + view          Contratar de outros clubes / vender do elenco
app/Livewire/Financas.php + view         Caixa, folha, valor do elenco + gráfico (Chart.js) + histórico
app/Models/Transfer.php, FinanceLedger.php
```

Rotas novas: `/mercado`, `/financas`. Cada rodada jogada registra **bilheteria** e **folha**
no `finance_ledger`, alimentando o gráfico de evolução do caixa. Compras/vendas também entram no razão.
Valores sempre em **centavos**.

## Divisões, acesso/rebaixamento e estadual (Fase 4)

- **16 clubes** em **Série A** e **Série B** (coluna `divisao`). Ao escolher o clube, você começa na divisão dele.
- **Acesso/rebaixamento**: ao encerrar a temporada, "Avançar temporada" aplica **2 sobem / 2 descem**
  (`AdvanceSeasonAction` — usa a tabela real da sua divisão e simula a outra divisão headless) e cria o próximo ano.
- **Campeonato Estadual**: diferencial histórico do JogaFoot. Com ≥4 clubes na UF do seu clube, dá para iniciar
  (ex.: **Paraense** com Remo, Paysandu, Águia e Tuna). `StartEstadualAction`.

```
app/Game/Services/StartSeasonAction.php     Liga nacional da divisão do clube (criarLiga genérico)
app/Game/Services/StartEstadualAction.php   Estadual por UF (>=4 clubes)
app/Game/Services/AdvanceSeasonAction.php   Acesso/rebaixamento + próxima temporada
```
Rotas novas: `POST /season/estadual`, `POST /season/advance`. A tabela mostra faixa de acesso (🟢) e rebaixamento (🔴).

## Evolução de jogadores + robustez (Fase 5)

**Evolução entre temporadas** (`EvolvePlayersAction`, chamada dentro de `AdvanceSeasonAction`):
- idade +1; `over` cresce (jovens ~+2/ano), estabiliza no auge (26-29) e declina (veteranos até ~-3/ano);
- valor/salário recalculados por `App\Game\PlayerValue` (fonte única, usada também pelo seeder);
- aposentadoria aos 38+ (ou 36+ com over baixo); cada clube é recompletado até 20 com jovens da base.

**Testes** (`php artisan test`):
```
tests/Unit/SimulationEngineTest.php     Engine pura: calendário, invariantes de tabela, público
tests/Feature/TransferServiceTest.php   Compra/venda, caixa, razão, mínimo de elenco
tests/Feature/PlayRoundTest.php         Rodada preenche placares + finanças; temporada encerra
tests/Feature/AdvanceSeasonTest.php     Acesso/rebaixamento (2 sobem / 2 descem) + próxima temporada
tests/Feature/EvolvePlayersTest.php     Jovem cresce, veterano declina, aposentadoria, base recompleta
```
> Os Feature tests usam `RefreshDatabase`. No `phpunit.xml` do Laravel, deixe
> `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:` (padrão do Laravel) para rodar rápido em memória.
