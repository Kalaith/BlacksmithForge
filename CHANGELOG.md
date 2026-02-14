# Changelog

## 2026-02-14
- Repo structure: Converted `frontend` from a gitlink/submodule entry to normal tracked files so `blacksmith_forge` is fully self-contained.
- CI: Reverted `actions/checkout@v4` submodule fetching in `.github/workflows/ci.yml` after normalizing repository structure.
