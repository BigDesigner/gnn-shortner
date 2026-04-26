# Architectural Decisions: GNN Shortner

## 1. Database Implementation
- **Decision:** Use a custom table (`wp_gnn_shorturls`) instead of Post Meta or Options API.
- **Rationale:** High-volume URL mapping requires efficient lookup. A custom table allows for better indexing on the `short_url` column and keeps the `wp_options` table clean.

## 2. Redirection Method
- **Decision:** Use `template_redirect` hook for link resolution.
- **Rationale:** This is the most "WordPress native" way to handle custom URL structures without interfering with global rewrite rules excessively. It allows for early exit and 301 redirection.

## 3. Security (reCAPTCHA)
- **Decision:** Integrate Google reCAPTCHA v2 on the frontend form.
- **Rationale:** Shortener forms are high-value targets for spam bots. reCAPTCHA provides a balance between security and user experience.

## 4. Design Philosophy
- **Decision:** "GNN Premium" Glassmorphism.
- **Rationale:** Maintain a consistent brand identity across all GNN plugins (Whois, IPinfo, Shortner). Minimalist, dark/light mode compatible, and visually stunning.

## 5. Plugin Updates
- **Decision:** GitHub-based manual/automatic updater.
- **Rationale:** Allows for direct distribution and version control without the constraints of the official WordPress.org repository, while maintaining a seamless update experience for users.
