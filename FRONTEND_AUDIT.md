# DevRoots Academy — Frontend Audit Report
**Date:** 2026-03-16
**Environment:** Laravel 12, PHP 8.2, Bootstrap 5.3
**Scope:** All public-facing (frontend) pages

---

## Overall Status Summary

| Page | Status | Key Problem |
|---|---|---|
| Home | ⚠️ Working / Hardcoded | Stats, courses, testimonials, partners all static |
| About | ⚠️ Working / Incomplete | No team/instructors section, fully static |
| Partners | ⚠️ Working / Hardcoded | No DB table, everything hardcoded |
| Contact | ❌ Broken / Incomplete | No contact form, no POST route, T&C links land here with no policy content |
| Courses Index | ⚠️ Functional / Image Bug | Image path wrong for admin-uploaded images |
| Course Detail | ⚠️ Functional / Incomplete | No image shown, no level/duration/schedule, outline accessor may be broken |
| Apply Now | ✅ Working | Course dropdown hardcoded, no email notifications |
| Become Instructor | ✅ Working | Expertise hardcoded, no T&C checkbox, no emails |

---

## Priority Fix List

### 🔴 Critical — Breaks Functionality

**1. Course model accessor mismatch**
`Course.php` has "virtual alias" accessors that read from DB columns that may not exist:

| Property used in views | Accessor reads from | Original migration column |
|---|---|---|
| `$course->title` | `$this->attributes['name']` | `title` |
| `$course->image` | `$this->attributes['image_path']` | `image` |
| `$course->outline` | `$this->attributes['weekly_outline']` | `outline` |

No migration exists that renames these columns. If the DB still has the original schema, **all three return null silently** — courses show as empty cards everywhere.
→ **Fix:** Verify actual DB column names. Either create a migration to rename columns, or update the model to remove the alias layer and use real column names.

**2. Course image path bug on Courses Index**
`resources/views/frontend/courses/index.blade.php` uses:
```blade
asset('images/' . $course->image)
```
But admin uploads store to `storage/app/public/courses/` (accessible via `public/storage/`). The correct path is:
```blade
asset('storage/' . $course->image)
```
→ All admin-uploaded course images appear broken on the courses listing page.

---

### 🟠 High — Missing Features Users Expect

**3. Contact page has no contact form**
`resources/views/frontend/contact.blade.php` has no form — only quick-action links to other pages. Users cannot send any message.
→ **Fix needed:** Add name, email, subject, message fields. Create `ContactController@submit`, a POST route, and a `contact_messages` DB table (or email dispatch).

**4. Course dropdown on Apply Now is hardcoded**
`resources/views/frontend/apply-now.blade.php` has a static PHP array of 8 course names. New courses added via admin never appear here.
```php
// Current (hardcoded):
$courses = ['Programming Fundamentals', 'Web Development', ...]

// Should be:
Course::pluck('title')->toArray()
```

**5. Homepage featured courses are hardcoded**
`resources/views/frontend/index.blade.php` has 8 hardcoded `<x-frontend.course-card>` blocks with manually typed slugs, titles, and image filenames. If DB slugs differ, "View Details" links throw 404s.
→ **Fix:** Replace with `Course::take(8)->get()` loop (or `where('is_featured', true)` if a flag is added).

**6. Header has no auth state**
`resources/views/frontend/partials/header.blade.php` always shows "Login" — even for logged-in admin users.
→ **Fix:** Wrap in `@auth` / `@guest` to show "Dashboard" link for authenticated users.

---

### 🟡 Medium — Incomplete / Placeholder Content

**7. No Privacy Policy or Terms & Conditions pages**
Both the footer and Apply Now form link to these:
```php
route('contact')  // used as the T&C link everywhere
```
No policy pages or routes exist. Users clicking "Terms & Conditions" land on the Contact page.
→ **Fix:** Create `/privacy` and `/terms` routes with static views, update all links.

**8. Newsletter subscription is fake**
Footer newsletter form has no `action` attribute. `main.js` intercepts submit, shows `alert("Thank you for subscribing...")`, and resets the form. **No email is ever collected or stored.**
→ **Fix:** Add `POST /newsletter` route, `NewsletterController@store`, and a `newsletter_subscribers` table.

**9. Social media links all point to `#`**
Footer and Contact page social icons: Facebook, Twitter/X, LinkedIn, Instagram, YouTube — all `href="#"`. Five dead links shown on every page.
→ **Fix:** Set real URLs (or hide icons until profiles exist).

**10. Course detail page is missing key info**
`resources/views/frontend/courses/show.blade.php` does not display:
- Course image (no `<img>` tag in the hero or body)
- Level (e.g., Beginner / Intermediate)
- Duration in weeks
- Schedule (e.g., weekdays/weekends)
- Mode (online / in-person / hybrid)

All of these are DB columns in the `courses` table but are never shown.

**11. About page has no instructors/team section**
The `instructors` table exists with an `approved` status column. The About page shows nothing from it.
→ **Fix:** Add a "Meet Our Instructors" section: `Instructor::where('status', 'approved')->get()`.

---

### 🟢 Low — Cleanup / Minor Issues

**12. Hardcoded stats appear on three pages**
"500+ Students", "12+ Courses", "8+ Industry Partners", "95% Satisfaction" are hardcoded copy-pasted on:
- Home page hero/stats bar
- Apply Now page left panel
- Become Instructor page left panel

→ **Fix:** Either compute from DB (`Student::count()`, `Course::count()`) or centralise in a config/service so one change updates all three pages.

**13. Live chat widget is a simulation**
`main.js` chat bot always replies `"DevRoots: Thanks! We'll get back to you soon."` regardless of input. Real authenticated `/chat` routes exist in the app but the widget does not connect to them.

**14. Partners page has no DB backing**
Adding or removing a partner requires editing `resources/views/frontend/partners.blade.php` directly. No `partners` table exists.

**15. Expertise dropdown on Become Instructor is hardcoded**
7 static strings in the Blade template. Should reflect available course categories from DB.

**16. Orphaned asset files (unused)**

| File | Issue |
|---|---|
| `public/images/paypal.png` | Not referenced anywhere in any view |
| `public/images/courses/mtn-momo.png` | Wrong directory; correct one is `public/images/mtn-momo.png` |
| `public/images/partners/minict.png` | Not referenced in Partners page or Home page |

---

## Page-by-Page Detail

### HOME (`/`)
**File:** `resources/views/frontend/index.blade.php`
**Route:** `Route::view('/', 'frontend.index')`
**Status:** ⚠️ Working but almost entirely hardcoded

| Section | Dynamic? | Notes |
|---|---|---|
| Stats bar | ❌ Hardcoded | "500+ Students", "12+ Courses" etc. |
| Featured Courses | ❌ Hardcoded | 8 static `<x-frontend.course-card>` blocks |
| Testimonials | ❌ Hardcoded | 3 fake testimonials (Gloria K., James M., Sarah N.) — no `testimonials` table |
| Partners | ❌ Hardcoded | 6 logos — no `partners` table |
| Hero | ✅ Static OK | Static copy — acceptable |
| Why Choose Us | ✅ Static OK | Icon cards — acceptable as static |
| CTA banner | ✅ Links correct | `apply.now`, `courses.index` named routes |

---

### ABOUT (`/about`)
**File:** `resources/views/frontend/about.blade.php`
**Route:** `Route::view('/about', 'frontend.about')`
**Status:** ⚠️ Working — fully static

- Mission, vision, values, contact info: all hardcoded — acceptable as long as content is stable
- **Missing:** An "Our Team" / "Meet Our Instructors" section using `instructors` table

---

### PARTNERS (`/partners`)
**File:** `resources/views/frontend/partners.blade.php`
**Route:** `Route::view('/partners', 'frontend.partners')`
**Status:** ⚠️ Working — fully hardcoded

- All 6 partner cards hardcoded (name, description, logo, category)
- "Become a Partner" CTA → `/contact` which has no form
- Orphaned image in directory: `minict.png`

---

### CONTACT (`/contact`)
**File:** `resources/views/frontend/contact.blade.php`
**Route:** `Route::view('/contact', 'frontend.contact')`
**Status:** ❌ Incomplete — no contact form

- Page shows contact info (address, phone, email, hours) and quick-action links only
- **No form. No POST route. Users cannot send a message.**
- Social icons all link to `#`
- T&C links across the site land here — no policy content exists on this page

---

### COURSES INDEX (`/courses`)
**File:** `resources/views/frontend/courses/index.blade.php`
**Controller:** `FrontendCourseController@index`
**Status:** ⚠️ Functional with image bug

- Fully DB-driven with pagination (9 per page) ✅
- `?category=` filter works ✅
- Sidebar categories built dynamically from DB ✅
- **Image path bug:** `asset('images/' . $course->image)` → should be `asset('storage/' . $course->image)`
- `level`, `duration_weeks`, `schedule`, `mode` columns exist but not displayed on cards

---

### COURSE DETAIL (`/courses/{slug}`)
**File:** `resources/views/frontend/courses/show.blade.php`
**Controller:** `FrontendCourseController@show`
**Status:** ⚠️ Functional but incomplete

- `Course::where('slug', $slug)->firstOrFail()` ✅
- Fee rendered with `number_format()` ✅
- Payment logos exist ✅
- Apply button links to `apply.now` ✅
- **Missing:** Course image, level, duration, schedule, mode display
- **Outline accessor bug:** `getOutlineAttribute()` reads from `$this->attributes['weekly_outline']` — if DB column is `outline`, always returns null → always shows "Course outline will be available soon."
- Fee table has hardcoded "Registration: Free" row
- No instructor attribution (no `instructor_id` FK on courses)
- No related courses section

---

### APPLY NOW (`/apply-now`)
**File:** `resources/views/frontend/apply-now.blade.php`
**Controller:** `FrontendStudentController@submitApplication`
**Status:** ✅ Working

- Form validates and saves to `students` table ✅
- Flash success/error messages work ✅
- `old()` re-populates fields on failure ✅
- Unique validation on email/phone/username ✅
- **Issue:** Course dropdown is a hardcoded PHP array — won't reflect DB courses
- **Issue:** No email notification to admin or applicant
- **Issue:** Terms link goes to `/contact` (no T&C page)
- Stats panel on left is hardcoded

---

### BECOME INSTRUCTOR (`/become-instructor`)
**File:** `resources/views/frontend/become-instructor.blade.php`
**Controller:** `FrontendInstructorController@submit`
**Status:** ✅ Working

- Form validates and saves to `instructors` table ✅
- `bio` min 50 chars, `portfolio` URL validation ✅
- Unique validation on email ✅
- `old()` helper works ✅
- **Issue:** Expertise area dropdown is hardcoded (7 static strings)
- **Issue:** No T&C agreement checkbox
- **Issue:** No email notifications sent
- Stats panel on left is hardcoded

---

## Route Map

```
GET  /                      → Route::view → frontend.index
GET  /about                 → Route::view → frontend.about
GET  /partners              → Route::view → frontend.partners
GET  /contact               → Route::view → frontend.contact  ← no form
GET  /courses               → FrontendCourseController@index
GET  /courses/{slug}        → FrontendCourseController@show
GET  /apply-now             → Route::view → frontend.apply-now
POST /apply-now             → FrontendStudentController@submitApplication
GET  /become-instructor     → Route::view → frontend.become-instructor
POST /become-instructor     → FrontendInstructorController@submit

MISSING:
POST /contact               → ContactController@submit        ← needed
GET  /privacy               → privacy policy view             ← needed
GET  /terms                 → terms & conditions view         ← needed
POST /newsletter            → NewsletterController@store      ← needed
```

---

## Asset Inventory

### ✅ All Assets Present
| Asset | Used In |
|---|---|
| `public/images/logo-horizontal.png` | Header, Footer |
| `public/images/logo-square.png` | Apply Now, Become Instructor |
| `public/images/courses/programming.png` | Home |
| `public/images/courses/web-development.png` | Home |
| `public/images/courses/hardware.png` | Home |
| `public/images/courses/ai.png` | Home |
| `public/images/courses/networking.png` | Home |
| `public/images/courses/mobile-apps.png` | Home |
| `public/images/courses/cloud-computing.png` | Home |
| `public/images/courses/iot.png` | Home |
| `public/images/partners/butende.png` | Home, Partners |
| `public/images/partners/mru.png` | Home, Partners |
| `public/images/partners/mahipso.png` | Home, Partners |
| `public/images/partners/adic.png` | Home, Partners |
| `public/images/partners/masakacity.png` | Home, Partners |
| `public/images/partners/nita.svg` | Home, Partners |
| `public/images/mtn-momo.png` | Course Detail |
| `public/images/airtel-money.png` | Course Detail |
| `public/images/visa.png` | Course Detail |

### ⚠️ Orphaned (file exists, never referenced)
| Asset | Notes |
|---|---|
| `public/images/paypal.png` | Not used anywhere |
| `public/images/courses/mtn-momo.png` | Duplicate in wrong directory |
| `public/images/partners/minict.png` | Not on Partners or Home page |

---

## JavaScript Functionality (`public/js/main.js`)

| Feature | Status | Notes |
|---|---|---|
| Back-to-top button | ✅ Working | `display: flex`, smooth scroll |
| Live chat toggle | ⚠️ Simulated | Bot always replies same generic text; not connected to `/chat` routes |
| Testimonial slider | ✅ Working | Auto 5s, prev/next, dots, timer reset on click |
| Newsletter form | ❌ Fake | `alert()` only — no backend, no data stored |
| Scroll fade-in | ✅ Working | IntersectionObserver with feature detection |
