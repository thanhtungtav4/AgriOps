#!/usr/bin/env bash
set -euo pipefail

cd /Users/macbook/Herd/ariops

opencode run "Execute the attached Agent A Planning prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-a-planning.md \
  --title "AgriOps Agent A Planning" \
  -m vps-103/claude_sonet_4.5 &

opencode run "Execute the attached Agent B DB/QA prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-b-db-qa.md \
  --title "AgriOps Agent B DB-QA" \
  -m vps-163/claude_sonet_4.5 &

opencode run "Execute the attached Agent C Security/API prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-c-security-api.md \
  --title "AgriOps Agent C Security-API" \
  -m vps-180/claude_sonet_4.5 &

wait
