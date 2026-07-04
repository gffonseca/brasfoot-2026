# Brasfoot 2026 — Projeto Laravel completo

Recriação do clássico manager de futebol. **Este é o projeto Laravel 11 completo** (esqueleto do
framework já incluído + nosso código), pronto para `composer install` e deploy. A engine de
simulação é um porte fiel do protótipo JS validado (28.000 jogos, invariantes OK).

▶️ **Protótipo jogável online:** https://brasfoot-2026-web-production.up.railway.app

## Rodar localmente

```bash
composer install
cp .env.example .env
php artisan key:generate

# banco local em SQLite
touch database/database.sqlite
php artisan migrate --seed

# testes (Unit + Feature em SQLite :memory:)
php artisan test

# subir
php artisan serve      # http://127.0.0.1:8000
```

Fluxo: escolher clube → **Escalação** → **Partida** (joga a rodada) → **Classificação** →
**Mercado** → **Finanças** → ao encerrar, **avançar temporada** (acesso/rebaixamento + evolução dos jogadores).

## Deploy no Railway

1. Suba este projeto para um repositório GitHub (`git init && git add . && git commit && git push`).
2. No Railway: **New Project → Deploy from GitHub repo** (ou `railway up`).
3. Adicione o plugin **PostgreSQL** e configure as variáveis `DB_*` (ver `.env.railway.example`),
   e `APP_KEY` (`php artisan key:generate --show`).
4. O `railway.json` roda `migrate --force && db:seed --force` no start.

## O que tem

```
app/Game/            Engine pura (Rng, SimulationEngine, LeagueScheduler, LeagueTable, PlayerValue)
app/Game/Services/   StartSeason, StartEstadual, PlayRound, AdvanceSeason, Transfer, Finance
app/Livewire/        Escalacao, Partida, Mercado, Financas
app/Models/          Club, Player, Season, Round, GameMatch, Lineup, Transfer, FinanceLedger
database/migrations  Schema completo (16 clubes em 2 divisões)
database/seeders     ClubSeeder (16 clubes fictícios + elencos)
tests/Unit           SimulationEngineTest (invariantes)
tests/Feature        Transfer, PlayRound, AdvanceSeason (acesso/rebaixamento), EvolvePlayers
```

## Funcionalidades

- Escalação com formação e campo tático; simulação de partidas (Poisson + mando de campo)
- Classificação por pontos corridos (returno duplo)
- Mercado de transferências (compra/venda, razão financeiro)
- Finanças com bilheteria, folha e gráfico (Chart.js)
- **Duas divisões** (Série A/B) com **acesso e rebaixamento** (2 sobem / 2 descem)
- **Campeonato Estadual** (ex.: Paraense com Remo, Paysandu, Águia, Tuna)
- **Evolução de jogadores** entre temporadas (jovens crescem, veteranos declinam, aposentadoria, base)

## Propriedade intelectual

Obra independente de fã. "Brasfoot"® é marca de Emmanuel dos Santos; dados, jogadores e escudos
aqui são **fictícios/genéricos**. Ver o Documento Técnico do projeto para as ressalvas de IP.
