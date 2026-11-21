# Contributing to WM Modular Scale

Thank you for your interest in contributing to **WM Modular Scale**. As part of the **WM Enterprise Suite**, this repository adheres to high standards of security, accessibility, and legal compliance.

---

## Zero Support & Issue Scope

**Zero technical, configuration, or private support is provided.** See [SUPPORT.md](SUPPORT.md):

- Report reproducible code defects **exclusively** via public [GitHub Issues](https://github.com/arnoldwender/wm-wp-modularscale/issues).
- **Do not** contact the author privately via email, `arnoldwender.com`, or direct messages — private inquiries are never answered.
- Issues and pull requests carry no response or maintenance SLA, and the software is provided strictly **"AS IS"** without warranty or liability.

---

## 🛡️ Core Development Standards

1. **PHP 8.1+ Strict Typing:** Every PHP file must start with `declare(strict_types=1);`.
2. **Zero-CDN Architecture:** No external resources (CDNs, Google Fonts, external telemetry) may be loaded at runtime.
3. **GoBD Cryptographic Invariants:** State-changing records must maintain `HMAC-SHA256` linear hash-chain integrity verified with `hash_equals()`.
4. **CWE-1236 Mitigation:** Any tabular or CSV output containing user-supplied strings must be passed through `sanitize_formula()`.
5. **BFSG 2025 / WCAG 2.2 AAA:** All user interfaces must achieve a minimum 4.5:1 contrast ratio and provide 48px minimum touch targets.

---

## 🚀 Development Workflow

```bash
# 1. Clone the repository
git clone https://github.com/arnoldwender/wm-wp-modularscale.git
cd wm-modularscale

# 2. Run deterministic in-memory unit tests
php tests/test-suite.php

# 3. Verify coding standards
composer check-cs # or phpcs --standard=WordPress .
```

---

## 📜 Commit Conventions

- Use conventional format: `[Feature] ...`, `[Fix] ...`, `[Security] ...`, `[Docs] ...`.
- All pull requests must include updated assertions in `tests/test-suite.php`.

For support and liability rules, read [SUPPORT.md](SUPPORT.md).
