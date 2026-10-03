# AutoNova

A modern vehicle marketplace for the Balkan market — cars, motorcycles, vans, trucks,
machinery and trailers. Built with **Laravel 13 + Inertia + Vue 3 + MySQL**, styled with a
custom editorial "Modernist" design system.

Inspired by the Reklama-5 / Avtopazar mockups, rebranded as **AutoNova** and designed to
feel sharper than AutoScout24 or mobile.de.

## Features

- **6 vehicle categories** — Cars, Motorcycles, Vans, Trucks, Machinery, Trailers
  (130 makes / 1,200+ models seeded).
- **Rich search & filtering** — make, model, price, year, mileage, fuel, transmission,
  body type, drivetrain, power, seller type, city, equipment, keyword; sort + grid/list views.
- **Listing detail** — gallery, full specifications, grouped equipment, seller/dealer card,
  in-app messaging, similar vehicles.
- **5-step "Sell your vehicle" wizard** with a drag-and-drop image uploader.
- **Automatic image normalisation** — every uploaded photo is cropped-to-fill an identical
  **1600×1200** canvas (+ **800×600** thumbnail) via Intervention Image, so every card and
  gallery lines up perfectly regardless of what was uploaded.
- **Roles** — `admin`, `moderator`, `client` (client = private seller or dealer).
  New listings enter a **moderation queue**; moderators approve/reject; admins manage users
  and categories.
- **Dealers** — profile pages, inventory dashboard, packages (START / PRO / MAX).
- **Members** — favorites, saved searches with match alerts, messaging inbox.
- **Demo data** — 51 listings with 213 generated studio-style photos, dealers, conversations.

## Requirements

PHP 8.4+, Composer, Node 20+, MySQL 8+.

## Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment (already configured for MySQL database "autonova")
cp .env.example .env   # if needed
php artisan key:generate

# 3. Create the database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS autonova CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Migrate + seed (generates demo listings and photos — takes ~90s)
php artisan migrate:fresh --seed
php artisan storage:link

# 5. Build assets and serve
npm run build           # or: npm run dev  (Vite dev server)
php artisan serve
```

Visit http://localhost:8000

## Demo accounts

All passwords are `password`.

| Role | Email | Notes |
|------|-------|-------|
| Admin | `admin@autonova.test` | Full admin panel (users, categories, moderation) |
| Moderator | `moderator@autonova.test` | Listing moderation queue |
| Dealer | `vardar@autonova.test` | Auto Centar Vardar (PRO) — inventory dashboard |
| Dealer | `premium@autonova.test` | Premium Motors (MAX) |
| Private | `marko@autonova.test` | Has favorites + saved searches |
| Private | `ana@autonova.test` | |

The sign-in page also lists these accounts with one-click fill.

## Architecture notes

- **Design system** — `resources/css/app.css` holds all Modernist tokens/components
  (Archivo type, `#ec3013` accent, sharp corners, 2px dividers). No Tailwind.
- **Vue** — every page is a single-file component using `<script setup>` + `<template>` +
  `<style scoped>`. Shared pieces in `resources/js/Components`, layout in
  `resources/js/Layouts/MarketplaceLayout.vue`.
- **Image pipeline** — `App\Services\ImageService` (uploads) and
  `App\Services\DemoImageGenerator` (seed photos) both funnel through the same 1600×1200 crop.
- **Options** — filter/enum lists live in `config/marketplace.php`, shared to the frontend.
- **Tests** — `php artisan test` (roles, moderation, and the image-normalisation guarantee).
