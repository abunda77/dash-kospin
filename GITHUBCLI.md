# Panduan Perintah CLI GitHub CLI (gh) yang Sering Digunakan

GitHub CLI (`gh`) adalah alat command line untuk berinteraksi dengan GitHub langsung dari terminal. Berikut adalah perintah yang sering digunakan:

## Dasar-dasar

-   `gh --version`
    Melihat versi GitHub CLI yang terpasang.
-   `gh auth login`
    Login ke akun GitHub melalui CLI.
-   `gh help`
    Melihat bantuan perintah gh.

## Repository

-   `gh repo clone <owner>/<repo>`
    Mengkloning repository dari GitHub.
-   `gh repo create`
    Membuat repository baru di GitHub.
-   `gh repo view`
    Melihat detail repository.
-   `gh repo fork <owner>/<repo>`
    Melakukan fork repository.

## Issue & Pull Request

-   `gh issue list`
    Melihat daftar issue di repository.
-   `gh issue create`
    Membuat issue baru.
-   `gh pr list`
    Melihat daftar pull request.
-   `gh pr create`
    Membuat pull request baru.
-   `gh pr checkout <number>`
    Checkout ke branch pull request tertentu.

## Lain-lain

-   `gh gist create <file>`
    Membuat gist dari file.
-   `gh alias set <alias> <perintah>`
    Membuat alias custom untuk perintah gh.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi GitHub CLI](https://cli.github.com/manual/).
