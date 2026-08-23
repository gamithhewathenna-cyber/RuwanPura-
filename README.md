# Ruwanpura Gems — Website (Phase 1)

A custom PHP/MySQL gemstone website with a fully manageable **Home Page** and a
secure **Admin Backend**. Built with plain PHP (PDO), MySQL, HTML5, CSS3, and
JavaScript. No frameworks, no build step — fully **cPanel compatible**.

---

## 1. What's included

```
ruwanpura/
├── index.php                 # The public Home Page
├── config/
│   └── config.php            # ← EDIT: database credentials & base URL
├── includes/                 # DB connection, helpers, header/footer
├── admin/                    # Admin panel (login + section editors + settings)
├── assets/
│   ├── css/                  # style.css (site) + admin.css (panel)
│   ├── js/                   # main.js (sliders, menu)
│   ├── images/               # bundled placeholder images + logo
│   └── uploads/              # ← uploaded images are stored here (writable)
├── sql/
│   └── database.sql          # Import this to create all tables + content
└── README.md
```

---

## 2. Installation on cPanel (step by step)

### Step 1 — Upload the files
1. Zip is already provided. In cPanel open **File Manager**.
2. Upload the zip into `public_html` (or a subfolder) and **Extract** it.
   - For a root install, move the contents of the `ruwanpura/` folder directly
     into `public_html`.
   - For a subfolder install (e.g. `public_html/gems/`), keep them in the folder.

### Step 2 — Create the database
1. In cPanel open **MySQL® Databases**.
2. Create a new database, e.g. `youruser_ruwanpura`.
3. Create a new database user and a password.
4. **Add the user to the database** and grant **ALL PRIVILEGES**.

### Step 3 — Import the tables & content
1. Open **phpMyAdmin** from cPanel.
2. Select your new database on the left.
3. Go to the **Import** tab, choose `sql/database.sql`, and click **Go**.
   This creates every table and fills in the default home-page content.

### Step 4 — Configure the connection
Open `config/config.php` and edit the top section:

```php
define('DB_HOST', 'localhost');            // usually 'localhost' on cPanel
define('DB_NAME', 'youruser_ruwanpura');   // the database you created
define('DB_USER', 'youruser_dbuser');      // the database user
define('DB_PASS', 'your-strong-password'); // the user's password

// '/' if installed at the domain root, or '/gems/' for a subfolder
define('BASE_URL', '/');
```

> For production, also set `ini_set('display_errors', '0');` in the same file.

### Step 5 — Make the uploads folder writable
Ensure `assets/uploads/` has permission **755** (or **775** if needed) so the
admin can save uploaded images.

### Step 6 — Done!
- Website:  `https://yourdomain.com/`
- Admin:    `https://yourdomain.com/admin/`

---

## 3. Admin login (default)

| Field    | Value                          |
|----------|--------------------------------|
| URL      | `/admin/`                      |
| Email    | `gamithhewathenna@gmail.com`   |
| Password | `Admin@123`                    |

**⚠️ Change the password immediately** after your first login:
go to **My Account → Change Password**.

---

## 4. Managing the Home Page

Every section of the home page is editable from the sidebar. No code editing
is ever required.

| Admin page              | Controls on the home page                                   |
|-------------------------|-------------------------------------------------------------|
| **Header & Navigation** | The menu labels (Home, Our Gemstones, About us, etc.)       |
| **Hero Slider**         | Headline, description, button text/link, and slider images  |
| **Journey Section**     | "A Journey Defined by Vision & Trust" text + sapphire image |
| **Gemstones Collection**| Add / edit / delete gemstones (name + image)                |
| **Factory & Labs**      | Production text and the three images                        |
| **Our Branches**        | Heading, description, map image, and branch list            |
| **Exhibitions & Logos** | Heading, description, and partner/exhibition logos          |
| **Testimonials**        | Add / edit / delete client reviews (quote, name, avatar)    |
| **Call To Action**      | The gold "Discover the Beauty of Fine Gems" banner          |
| **Footer**              | About text, address, email, phones, social links, copyright |

For each editable image you can upload a replacement; if you never upload one,
a bundled placeholder is shown automatically.

---

## 5. Website Settings

**Website Settings** (sidebar) lets you:
- Upload / change the **website logo**
- Change the **colour theme** (gold accent + dark base) — applied site-wide
- Change the **website name**
- Change the **admin email address**

**My Account** lets you:
- Change your **login email** and name
- **Change your password**

**Forgot password?** On the login screen, use the *Forgot password?* link. A
secure, time-limited reset link is generated (and emailed if your server has
mail enabled).

---

## 6. Security notes

- Passwords are stored using PHP's `password_hash()` (bcrypt).
- All admin forms are protected against CSRF.
- All database queries use prepared statements (PDO).
- `.htaccess` files block direct access to `config/`, `includes/`, `sql/`, and
  prevent PHP execution inside `assets/uploads/`.

---

## 7. Technology

Custom PHP · MySQL · HTML5 · CSS3 · JavaScript · Responsive · cPanel compatible.

Fonts: **Cormorant Garamond** (display) and **Poppins** (body), loaded from
Google Fonts.

---

## 8. Phase 1 scope

This delivery covers the **Home Page** and the **Admin backend to manage it**.
Other pages, the gemstone catalogue, product management, customer accounts,
e-commerce, and payment gateways are intended for future phases.
