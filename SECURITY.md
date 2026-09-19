# Security Policy — WM Modular Scale

Wender Media takes the security and legal compliance of its software products seriously. This document outlines our vulnerability disclosure policy in accordance with **EU Cyber Resilience Act (CRA Art. 14)** and **BSI Guidelines**.

---

## 🛡️ Supported Versions

Only the latest release of each minor branch receives security updates.

| Version | Supported |
|---|---|
| 1.1.0 | :white_check_mark: Supported |
| < 1.1.0 | :x: End of Life (Upgrade Immediately) |

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability, please do NOT create a public issue. Send your report directly to the security team:

- **Security Contact:** [arnold.wender@gmail.com](mailto:arnold.wender@gmail.com)
- **Corporate Website:** [https://www.wendermedia.com](https://www.wendermedia.com)
- **Responsible Lead:** Arnold Wender

### What to Include:
1. Proof of Concept (PoC) script or detailed reproduction steps.
2. Affected components (REST API, Shortcode, Admin screen).
3. Potential impact assessment (e.g., Privilege Escalation, SQL Injection, CWE-1236 Formula Injection).

### Response SLA:
- **Acknowledgement:** Within 24 hours.
- **Triage & Patch:** Within 72 hours for Critical/High vulnerabilities.
- **CVE Attribution:** Coordinated disclosure with reporter credit.
