#!/usr/bin/env bash
# Sobe a app Brasfoot 2026 para o GitHub (repo já criado).
# Rode DENTRO da pasta brasfoot-2026-app:  bash subir-github.sh
set -e
git init -b main
git add .
git commit -m "Brasfoot 2026 — app Laravel completa (engine validada, Livewire, 2 divisões, estadual, evolução)"
git remote add origin https://github.com/gffonseca/brasfoot-2026.git 2>/dev/null || git remote set-url origin https://github.com/gffonseca/brasfoot-2026.git
git branch -M main
git push -u origin main --force
echo ""
echo "Pronto! Veja em: https://github.com/gffonseca/brasfoot-2026"
