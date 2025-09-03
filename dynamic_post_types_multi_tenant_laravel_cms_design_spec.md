# Post Types — Simplified (Programs & Jobs)

> Scope: keep it powerful without going overboard. We support **two structured families**:
>
> 1) **Programs** = scholarships / opportunities / internships (same columns, one table)
> 2) **Jobs** = hiring posts (different columns, separate table)
>
> Everything else (static pages, news, guides) uses the core `posts` table only.

---

## 0) Tenancy & Guardrails
- **Single DB, single codebase, per‑host tenant resolution.**
- All tenant‑scoped tables include `tenant_id` with a global scope `TenantAware`.
- Tenant staff accounts are per‑tenant; network superadmin sits outside tenant scope.

---

## 1) Core Tables (Universal)

### `tenants`
Branding, SEO defaults, analytics IDs, ads, email sender, compliance flags. (As in previous spec.)

### `users` (tenant staff) & `admins` (network)
Role matrix: `TenantOwner`, `Admin`, `Editor`, `Author`, `SEO`, `Moderator`, `Analyst`.

### `posts`
Universal editorial/SEO, minimal business fields.

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| tenant_id | uuid | scope |
| kind | enum | `page`, `news`, `guide`, `program`, `job` |
| title | string | |
| slug | string | unique within tenant (or per kind) |
| excerpt | text | |
| content | longtext | HTML/MD |
| status | enum | draft/review/scheduled/published/archived |
| cover_image_url | string | |
| meta_title | string | SEO |
| meta_description | text | SEO |
| og_image_url | string | SEO |
| canonical_url | string | SEO |
| published_at | datetime | |
| scheduled_at | datetime | |
| created_by / updated_by | uuid | staff |
| created_at / updated_at | timestamps | |

**Indexes**: `(tenant_id, kind, status, published_at)`, `(tenant_id, slug)`.

### Taxonomy & Media (unchanged)
- `terms`, `term_post` for categories/tags/locations
- `media` for uploads (variants), all tenant‑scoped
- `seo_landings`, `ads`, `redirects` as before

---

## 2) Program Family (Scholarships/Opportunities/Internships)
One table, one editor form, a **`program_type`** column distinguishes subtypes.

### `pt_program`
| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| tenant_id | uuid | scope |
| post_id | uuid | unique FK → posts.id |
| program_type | enum | `scholarship`, `opportunity`, `internship` |
| organizer_name | string | e.g., university/org |
| location_text | string | freeform city/venue |
| country_code | string(2) | ISO‑3166‑1 alpha‑2 |
| deadline_at | datetime nullable | application close |
| is_rolling | boolean | no hard deadline |
| funding_type | enum | `fully_funded`, `partially_funded`, `unfunded` |
| stipend_amount | decimal(18,2) nullable | |
| fee_amount | decimal(18,2) nullable | app/registration fee |
| program_length_text | string | e.g., `6 months` |
| eligibility_text | text | requirements summary |
| apply_url | string | external link |
| extra | json | room for small extras |
| created_at / updated_at | timestamps | |

**Common facets**: `program_type`, `country_code`, `deadline_at` (range), `funding_type`, `is_rolling`.

**Expiry rule**: nightly job marks items with `deadline_at < now()` as **past** (banner + archive sections) but keeps them indexable.

---

## 3) Jobs Family (Dedicated Table)
Jobs need different sorting/filters. Separate table.

### `pt_job`
| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| tenant_id | uuid | scope |
| post_id | uuid | unique FK → posts.id |
| company_name | string | |
| employment_type | enum | `full_time`, `part_time`, `contract`, `internship` |
| workplace_type | enum | `onsite`, `hybrid`, `remote` |
| title_override | string nullable | if different from post title |
| location_city | string nullable | |
| country_code | string(2) | |
| min_salary | decimal(18,2) nullable | |
| max_salary | decimal(18,2) nullable | |
| salary_currency | string(3) nullable | ISO‑4217 |
| salary_period | enum nullable | `year`, `month`, `day`, `hour` |
| experience_level | enum nullable | `junior`, `mid`, `senior`, `lead` |
| responsibilities | longtext nullable | |
| requirements | longtext nullable | |
| benefits | json nullable | list of strings |
| deadline_at | datetime nullable | application close |
| apply_url | string | external link |
| extra | json | misc |
| created_at / updated_at | timestamps | |

**Facets**: `workplace_type`, `employment_type`, `country_code`, `experience_level`, salary ranges, `deadline_at`.

---

## 4) Ads & Monetization

### `ads`
| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| tenant_id | uuid | scope |
| slot_key | string | e.g., `header_banner`, `sidebar_1`, `infeed_1`, `footer` |
| type | enum | `adsense`, `gam`, `html_snippet` |
| config_json | json | provider IDs, targeting, snippet |
| is_active | boolean | |
| created_at / updated_at | timestamps | |

### Placement Rules
- Slots can be placed globally (header, footer, sidebar) or in content flows (infeed after N cards).
- **Program pages**: can insert banners above/below deadlines & eligibility sections.
- **Job pages**: can insert banners next to company/requirements.
- Newsletter digests support ad slots (inline text/image ads).

### Management
- Ads configurable per tenant in admin dashboard.
- `ads.txt` automatically generated per tenant.
- Superadmin can override global ads (network‑wide deals).

---

## 5) Routing & URLs
- **No category in path** (per requirement). Use **family paths**:
  - Programs: `/opportunities/{slug}` (covers scholarships/opportunities/internships)
  - Jobs: `/jobs/{slug}`
  - News/Guides/Pages: `/{slug}`
- Uniqueness: `(tenant_id, slug)` for pages/news/guides, and `(tenant_id, kind, slug)` for program/job families if desired.

---

## 6) Controllers (Sketch)
- `ProgramController@index` — list + facets (filters map to `pt_program`)
- `ProgramController@show` — join `posts`→`pt_program`
- `JobController@index` — list + facets (filters map to `pt_job`)
- `JobController@show` — join `posts`→`pt_job`
- `PageController@show` / `NewsController@show`

**Query pattern** (programs):
```sql
SELECT p.*, pp.*
FROM posts p
JOIN pt_program pp ON pp.post_id = p.id AND pp.tenant_id = p.tenant_id
WHERE p.tenant_id = :tenant
  AND p.kind = 'program'
  AND p.status = 'published'
  AND (:program_type IS NULL OR pp.program_type = :program_type)
  AND (:country IS NULL OR pp.country_code = :country)
  AND (:funding IS NULL OR pp.funding_type = :funding)
  AND (:deadline_from IS NULL OR pp.deadline_at >= :deadline_from)
ORDER BY COALESCE(pp.deadline_at, '9999-12-31'), p.published_at DESC
LIMIT :limit OFFSET :offset;
```

---

## 7) Admin Editor UX
- **Post basics** tab (title, slug, content, cover, publish schedule, SEO, OG, canonical).
- **Programs tab** (visible when `kind=program`): program fields above.
- **Jobs tab** (visible when `kind=job`): jobs fields above.
- **Ads tab**: assign ad slots, preview banners.
- Status workflow: draft → review → scheduled/published → archived.

**Bulk tools**
- Mass retagging, archive past, fix broken `apply_url`, update `funding_type`.
- Rotate/refresh ads across pages.

---

## 8) SEO & Schema.org
- Programs map to **Scholarship**/**EducationalOccupationalProgram**/**Event** depending on `program_type`.
- Jobs map to **JobPosting**.
- Ads marked with `data-ad-slot` for tracking, compliant with providers.
- Sitemaps per tenant: `sitemap.xml` splits into: `sitemap-opportunities.xml`, `sitemap-jobs.xml`, `sitemap-pages.xml`.
- Redirects manager for slug changes; 410 for removals.

---

## 9) Search
- MySQL FULLTEXT on `posts(title, excerpt, content)`.
- Additional B‑tree indexes:
  - `pt_program(tenant_id, program_type, country_code, deadline_at)`
  - `pt_job(tenant_id, employment_type, workplace_type, country_code, deadline_at)`
- Ads can be targeted by search context (e.g., show certain ad slots for `funding_type=fully_funded`).

---

## 10) Ingestion (Optional, Scalable Later)
- RSS/CSV/API pipelines can target **Programs** or **Jobs** via simple mappers.
- Deduping keys:
  - Programs: `(domain(apply_url), normalized(title), deadline_at)`
  - Jobs: `(company_name, normalized(title), country_code, deadline_at)`
- Ads can also be attached to ingested posts (sponsored content).

---

## 11) Email & Monetization
- Transactional: notifications for review/publish/expiry.
- Marketing: weekly digest per family (`Top opportunities`, `Hot jobs`).
- Digests support **inline ad slots**.
- Ads monetization: banner, sponsored posts, newsletter ads.

---

## 12) Compliance & Analytics
- Cookie consent; GDPR/CCPA toggles.
- GA4/Matomo per tenant.
- Track events: search queries, apply clicks, outbound links, newsletter signups, ad impressions/clicks.

---

## 13) Migrations (Sketch)

**`posts`**, **`pt_program`**, **`pt_job`** tables (as before).

**`ads`**
```php
Schema::create('ads', function (Blueprint $t) {
  $t->uuid('id')->primary();
  $t->uuid('tenant_id')->index();
  $t->string('slot_key');
  $t->enum('type', ['adsense','gam','html_snippet']);
  $t->json('config_json');
  $t->boolean('is_active')->default(true);
  $t->timestamps();
});
```

---

## 14) Sitemaps & Robots
- `/sitemap.xml` → index of: `/sitemap-opportunities.xml`, `/sitemap-jobs.xml`, `/sitemap-pages.xml`.
- `robots.txt` per tenant, editable in admin; staging tenants disallow crawl.
- Ads not indexed (blocked by robots/meta where necessary).

---

## 15) Email & Digests
- Weekly **Opportunities Digest** (programs, sorted by nearest deadline).
- Weekly **Jobs Digest** (jobs, fresh first, salary/remote boosted).
- Digests include inline ads.
- Per‑tenant sender domains supported.

---

## 16) Backlog Plan
**v1**: Tenants, Posts, `pt_program`, `pt_job`, Ads slots, CRUD + editor tabs, listings, search, SEO basics, expiry jobs, sitemaps.

**v2**: Ingestion (RSS/CSV), newsletters/digests, redirects manager, analytics dashboard, ad impressions tracking.

**v3**: Image variants + CDN, Google Ad Manager integration, Meilisearch swap if needed, contextual ad targeting.

---

## 17) Why this split works
- Keeps code and DB **simple** while covering 90% of real use.
- **Programs** family captures scholarship/opportunity/internship without schema sprawl.
- **Jobs** gets the fields it needs for filtering/salary UX.
- **Ads** are integrated into all layers: pages, digests, listings, admin tools.
- Future families (e.g., `events`) can be added using the same pattern.

