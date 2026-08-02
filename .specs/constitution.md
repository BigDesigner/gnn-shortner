# Constitution

This document contains the core engineering standards, coding guidelines, AI safety rules, and collaboration conventions for GNN Shortner development.

## 1. Naming & Namespace Standards

### Global Prefixing (MANDATORY)
Every PHP function, global variable, and CSS class MUST be prefixed with `gnn_shortner_` or project-scoped equivalents to prevent namespace collisions.
* **PHP Functions:** `gnn_shortner_my_function_name`
* **PHP Classes:** `GNN_Shortner_My_Class_Name`
* **CSS Classes:** BEM methodology starting with `gnn-shortner-` (e.g., `.gnn-shortner__button--active`)
* **Text Domain:** `gnn-shortner`

### File Naming Conventions
* **PHP Files:** `lowercase-kebab-case.php`
* **Assets:** Logical kebab-case matching the component name (e.g., `gnn-shortner.js`).

---

## 2. Code Quality & Formatting
* **PHP Compliance:** Adhere to PSR-12 coding standard.
* **JavaScript:** Strict standard ES6+ with clean, modular structures.
* **CSS Rules:** Use vanilla CSS. Never hardcode colors to solid values like `#fff` or `#000`. Use transparent/semi-transparent layer settings and CSS variables (`color: inherit`) to ensure Light/Dark theme compatibility.
* **PHPDoc & Comments:** All PHP classes and functions must include PHPDoc header comments. Internal logic blocks require concise inline explanations.

---

## 3. UI/UX Standards
* **Aesthetic Philosophy:** "GNN Premium" Glassmorphism UI utilizing backdrop blur (`backdrop-filter`) and visual transparency.
* **Responsiveness:** Form scaling must dynamically adjust for small mobile screens.
* **WordPress Native:** Respect WordPress Admin panel components but apply custom Glassmorphism touches for premium settings feel.

---

## 4. Verification & Testing
* **Syntax Checks:** Run syntax validation on edited files (e.g., `php -l`) before staging.
* **Verification Loop:** If a change breaks tests or fails syntax validation, it MUST be rolled back or fixed immediately. Do not propose broken changes.

---

## 5. Agent Behavior & Workflow
* **Roles:**
  - **Architect:** System structure, ADR maintenance, architectural design.
  - **Developer:** Implementation of features, WordPress best practice usage.
  - **Reviewer:** Code standards and security validation.
  - **Librarian:** Memory bank and status documentation.
* **Workflow Pipeline:**
  1. **Research:** Reference official WP handbook or guides.
  2. **Plan:** Propose changes with implementation details.
  3. **Execute:** Run atomical steps.
  4. **Verify:** Check syntax and logic.
  5. **Log:** Update active session states and worklogs.
  6. **Clean Up:** Ensure clean worktree conditions.

---

## 6. Commit Hygiene
* **Atomic Commits:** Propose small, single-purpose commits.
* **Message Standards:** Prefix with conventional commits (e.g., `feat(ui): ...`, `fix(security): ...`, `chore(config): ...`).
* **Staging Approval:** Never stage or commit automatically in Interactive Mode.

---

## 7. GNN Product Family Admin Menu Registration
* **Menu Position:** Must conform to the GNN Product Family Admin Menu Position Registry (ADR 0009).
* **Bands:** Themes ('58.xyz'–'59.xyz'), Plugins ('78.xyz'–'79.xyz').
* **Format:** Quoted string literal with 3-digit suffix (e.g., '79.102').
* **Nesting:** Products with multiple screens must register ONE top-level menu and nest others.
* **Registry Update:** Update the ADR if assigning a new position.
