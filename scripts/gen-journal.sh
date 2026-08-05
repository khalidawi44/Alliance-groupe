#!/usr/bin/env bash
# gen-journal.sh — Génère le journal public « Fait par l'IA » à partir de git log.
#
# Sort : alliance-groupe-theme/assets/data/journal-ia.json
# Chaque entrée = { date, hash, titre } (les commits internes/HANDOFF sont filtrés).
# À relancer après une salve de commits pour rafraîchir la page /fait-par-lia.
#
# Usage : bash scripts/gen-journal.sh [nombre_max]
set -euo pipefail
cd "$(dirname "$0")/.."

MAX="${1:-200}"
OUT="alliance-groupe-theme/assets/data/journal-ia.json"
mkdir -p "$(dirname "$OUT")"

TMP_LOG="$(mktemp)"
TMP_PY="$(mktemp)"
trap 'rm -f "$TMP_LOG" "$TMP_PY"' EXIT

git log --pretty=format:'%h%x1f%ad%x1f%s' --date=format:'%Y-%m-%d' -n "$MAX" > "$TMP_LOG"

cat > "$TMP_PY" <<'PY'
import sys, json, re

log_path, out_path = sys.argv[1], sys.argv[2]
rows = []
# Motifs de bruit interne à NE PAS montrer au public.
skip = re.compile(r'^(handoff|backlog|wip|merge|revert|fix typo|typo|bump|release)\b', re.I)

with open(log_path, encoding='utf-8') as f:
	for line in f:
		line = line.rstrip('\n')
		if not line:
			continue
		parts = line.split('\x1f')
		if len(parts) != 3:
			continue
		h, d, subj = parts
		subj = subj.strip()
		if not subj or skip.search(subj):
			continue
		rows.append({'hash': h, 'date': d, 'titre': subj})

data = {'genere_le': rows[0]['date'] if rows else '', 'total': len(rows), 'entrees': rows}
with open(out_path, 'w', encoding='utf-8') as f:
	json.dump(data, f, ensure_ascii=False, indent='\t')
	f.write('\n')
print('Journal généré : %d entrées → %s' % (len(rows), out_path))
PY

python3 "$TMP_PY" "$TMP_LOG" "$OUT"
