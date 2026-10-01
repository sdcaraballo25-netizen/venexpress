#!/bin/bash
# Deja la sesión de Claude Code trabajando sobre la rama principal (main),
# actualizada con origin/main. Se ejecuta al iniciar cada sesión
# (hook SessionStart, ver .claude/settings.json) y también se puede correr
# a mano: bash .claude/hooks/ensure-main-branch.sh
#
# Nunca borra ni pisa trabajo: si hay cambios sin commitear o la rama
# main local divergió de origin/main, no cambia nada y solo avisa.
# Siempre termina con código 0 para no bloquear el inicio de la sesión.
# Lo que imprime queda visible para Claude al empezar la sesión.

set -uo pipefail

MAIN_BRANCH="main"
TAG="[rama-main]"

cd "${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}" 2>/dev/null || exit 0

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    exit 0
fi

if ! git fetch origin "$MAIN_BRANCH" --quiet 2>/dev/null; then
    echo "$TAG Aviso: no se pudo traer origin/$MAIN_BRANCH (¿sin conexión?). Se usa la copia local."
fi

current="$(git rev-parse --abbrev-ref HEAD 2>/dev/null)"

# Cambios en archivos versionados: cambiar de rama podría mezclarlos o
# fallar, así que no se toca nada.
if [ -n "$(git status --porcelain --untracked-files=no 2>/dev/null)" ]; then
    if [ "$current" = "$MAIN_BRANCH" ]; then
        echo "$TAG Trabajando en '$MAIN_BRANCH' con cambios sin commitear; no se actualizó desde origin/$MAIN_BRANCH."
    else
        echo "$TAG Hay cambios sin commitear en '$current'; NO se cambió de rama."
        echo "$TAG Revisa esos cambios y llévalos a '$MAIN_BRANCH' antes de seguir (ver 'Always work on main' en CLAUDE.md)."
    fi
    exit 0
fi

if [ "$current" != "$MAIN_BRANCH" ]; then
    if git show-ref --verify --quiet "refs/heads/$MAIN_BRANCH"; then
        switched="$(git checkout --quiet "$MAIN_BRANCH" 2>&1)"
    else
        switched="$(git checkout --quiet -b "$MAIN_BRANCH" --track "origin/$MAIN_BRANCH" 2>&1)"
    fi

    if [ "$(git rev-parse --abbrev-ref HEAD 2>/dev/null)" != "$MAIN_BRANCH" ]; then
        echo "$TAG No se pudo cambiar de '$current' a '$MAIN_BRANCH': $switched"
        exit 0
    fi

    echo "$TAG Se cambió de '$current' a '$MAIN_BRANCH'."
fi

# Solo avance rápido: si main local tiene commits que origin no tiene
# (o al revés con historia distinta), no se fusiona ni se descarta nada.
if git rev-parse --verify --quiet "origin/$MAIN_BRANCH" >/dev/null; then
    if ! git merge --ff-only --quiet "origin/$MAIN_BRANCH" >/dev/null 2>&1; then
        echo "$TAG Aviso: '$MAIN_BRANCH' local y origin/$MAIN_BRANCH divergieron; no se actualizó automáticamente."
    fi
fi

echo "$TAG Trabajando en '$MAIN_BRANCH' @ $(git log -1 --format='%h %s' 2>/dev/null)"

exit 0
