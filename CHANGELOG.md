# Changelog

## 2026-04-26
- Documentation: Added a UI mockup implementation guide for moving the current interface toward the dark forge dashboard concept with temporary assets.
- Documentation: Added a UI refresh implementation plan focused on physical forge interactions, workshop navigation, hierarchy, and animated feedback.
- Frontend: Added the first mockup implementation pass with a dark forge shell, temporary visual assets, reusable framed UI components, and a new Forge dashboard.
- Frontend: Restyled the Recipe Book and Customers detail screens to match the forge-themed panel and ledger system.

## 2026-02-14
- Repo structure: Converted `frontend` from a gitlink/submodule entry to normal tracked files so `blacksmith_forge` is fully self-contained.
- CI: Reverted `actions/checkout@v4` submodule fetching in `.github/workflows/ci.yml` after normalizing repository structure.
