#!/usr/bin/env python3
"""
strip-unshipped-claims-from-translations.py — make the 24 translated READMEs stop describing
features this plugin never shipped.

The files in docs/translations/README.<lang>.md were generated from one template: translated
headings, English bodies. The template promised WP-CLI commands (`wp modular …`), a shortcode
`[wm_modular_scale_grid]`, GoBD/HMAC audit exports, REST endpoints and database schemas; none of
those exist in the code (measured 2026-09-14: no WP_CLI::add_command, register_rest_route,
add_shortcode, hash_hmac or dbDelta). The English README.md is the reference; this script edits the
English bodies of every translation in the same way and leaves the translated headings alone.

Usage:
    python3 scripts/strip-unshipped-claims-from-translations.py          # rewrite in place
    python3 scripts/strip-unshipped-claims-from-translations.py --check  # exit 1 if any claim remains

Exit code: 0 when every file is free of the claims, 1 otherwise.
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
TRANSLATIONS = sorted((ROOT / "docs" / "translations").glob("README.*.md"))

CLAIMS = re.compile(r"wp modular|wm_modular_scale_grid|GoBD|HMAC|REST API endpoints|database schemas|Audit Reports|Save & Apply", re.IGNORECASE)

LEVEL2_BODY = """1. **Settings:** open **Modular Scale** in the admin menu (**Wender Media › Modular Scale** with the WM Suite Hub, also **Settings › Modular Scale**): base size, ratio (presets or a custom value), unit, decimals, live sandbox, and the *apply to the whole site* toggle.
2. **Editor:** the *WM Modular Scale* block category (Fluid Heading, Fluid Lead, Fluid Container, Typographic Grid, Ratio Visualizer, APCA Contrast Badge, Theme.json Exporter) and the `.has-fluid-*` classes; with GeneratePress / GenerateBlocks the fluid steps appear in their own typography controls.
3. **Dashboard widget:** base size, ratio and whether the scale is applied.

### Not shipped
No WP-CLI commands, no REST endpoints, no shortcodes, no database tables, no audit exports, no external requests. The plugin stores ten options and prints CSS.
"""

LEVEL3_OLD = re.compile(r"Technical engineering documentation, hooks, filters, REST API endpoints, and database schemas are maintained in the English manual")
LEVEL3_NEW = "Classes, hooks, options, blocks, the theme bridges and the hub adapter are documented in the English manual"

COMPLIANCE_OLD = re.compile(r"^- \*\*GoBD[^\n]*$", re.MULTILINE)
COMPLIANCE_NEW = "- **No personal data, no database tables:** the plugin reads and writes its own options and prints CSS; nothing is logged or exported."

QUICKSTART_OLD = re.compile(r"3\. Select a mathematical ratio \(e\.g\. Major Third 1\.25\) and click \*\*Save & Apply\*\*\.")
QUICKSTART_NEW = "3. Select a ratio (e.g. Major Third 1.25), check the live sandbox and save; tick *apply to the whole site* to print the fluid tokens."

# Level 2 section: from the numbered "Configuration" item to the horizontal rule that closes the section.
LEVEL2_BLOCK = re.compile(r"1\. \*\*Configuration:\*\*.*?(?=\n---\n)", re.DOTALL)


def rewrite(text: str) -> str:
    text = LEVEL2_BLOCK.sub(LEVEL2_BODY.rstrip("\n") + "\n", text, count=1)
    text = LEVEL3_OLD.sub(LEVEL3_NEW, text)
    text = COMPLIANCE_OLD.sub(COMPLIANCE_NEW, text)
    text = QUICKSTART_OLD.sub(QUICKSTART_NEW, text)
    # a horizontal rule needs a blank line above it (markdownlint MD022/MD032)
    text = text.replace("prints CSS.\n---\n", "prints CSS.\n\n---\n")
    return text


def main() -> int:
    check_only = "--check" in sys.argv
    bad = 0
    changed = 0
    for path in TRANSLATIONS:
        src = path.read_text(encoding="utf-8")
        out = src if check_only else rewrite(src)
        if not check_only and out != src:
            path.write_text(out, encoding="utf-8")
            changed += 1
        left = CLAIMS.findall(out)
        if left:
            bad += 1
            print(f"{path.name}: still carries {sorted(set(left))}")
    print(f"{len(TRANSLATIONS)} translations · {changed} rewritten · {bad} with unshipped claims left")
    return 1 if bad else 0


if __name__ == "__main__":
    sys.exit(main())
