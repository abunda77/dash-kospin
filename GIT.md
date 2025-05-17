# Panduan Perintah CLI Git yang Sering Digunakan

Git adalah sistem kontrol versi terdistribusi yang sangat populer. Berikut adalah daftar perintah Git yang sering digunakan beserta penjelasannya.

## Konfigurasi Awal

-   `git config --global user.name "Nama Anda"`  
    Mengatur nama pengguna global.
-   `git config --global user.email "email@domain.com"`  
    Mengatur email global.
-   `git config --list`  
    Melihat semua konfigurasi git.

## Inisialisasi & Clone

-   `git init`  
    Membuat repository git baru.
-   `git clone <url>`  
    Mengkloning repository dari remote.

## Status & Log

-   `git status`  
    Melihat status file di working directory dan staging area.
-   `git log`  
    Melihat riwayat commit.
-   `git log --oneline`  
    Melihat log singkat.

## Menambah & Menghapus File

-   `git add <file>`  
    Menambah file ke staging area.
-   `git add .`  
    Menambah semua file yang berubah ke staging area.
-   `git rm <file>`  
    Menghapus file dari repository dan working directory.
-   `git mv <file_lama> <file_baru>`  
    Mengganti nama/memindahkan file.

## Commit

-   `git commit -m "pesan commit"`  
    Membuat commit dengan pesan.
-   `git commit -am "pesan commit"`  
    Menambah dan commit file yang sudah pernah di-track.

## Branch

-   `git branch`  
    Melihat daftar branch.
-   `git branch <nama_branch>`  
    Membuat branch baru.
-   `git checkout <nama_branch>`  
    Berpindah ke branch tertentu.
-   `git checkout -b <nama_branch>`  
    Membuat dan langsung berpindah ke branch baru.
-   `git branch -d <nama_branch>`  
    Menghapus branch lokal.

## Merge & Rebase

-   `git merge <nama_branch>`  
    Menggabungkan branch ke branch aktif.
-   `git rebase <nama_branch>`  
    Mengaplikasikan commit dari branch lain ke branch aktif.

## Remote

-   `git remote -v`  
    Melihat daftar remote.
-   `git remote add <nama> <url>`  
    Menambah remote baru.
-   `git remote remove <nama>`  
    Menghapus remote.

## Pull & Push

-   `git pull`  
    Mengambil dan menggabungkan perubahan dari remote.
-   `git push`  
    Mengirim perubahan ke remote.
-   `git push -u origin <nama_branch>`  
    Push branch baru ke remote dan set upstream.

## Stash

-   `git stash`  
    Menyimpan perubahan sementara.
-   `git stash pop`  
    Mengambil kembali perubahan yang di-stash.
-   `git stash list`  
    Melihat daftar stash.

## Reset & Revert

-   `git reset --hard <commit>`  
    Mengembalikan repository ke commit tertentu (menghapus perubahan setelahnya).
-   `git revert <commit>`  
    Membuat commit baru untuk membatalkan commit tertentu.

## Tag

-   `git tag`  
    Melihat daftar tag.
-   `git tag <nama_tag>`  
    Membuat tag baru.
-   `git push origin <nama_tag>`  
    Push tag ke remote.

## Lain-lain

-   `git diff`  
    Melihat perbedaan file yang belum di-commit.
-   `git show <commit>`  
    Melihat detail commit tertentu.
-   `git blame <file>`  
    Melihat siapa yang mengubah setiap baris pada file.

## Alias Git Interaktif

Agar dapat menggunakan perintah seperti `git graph` yang menampilkan log commit secara visual dan interaktif seperti pada gambar, Anda bisa menambahkan alias berikut ke konfigurasi git Anda:

```bash
git config --global alias.graph "log --oneline --decorate --all --graph"
```

Setelah menjalankan perintah di atas, Anda bisa menggunakan:

```bash
git graph
```

Perintah ini akan menampilkan riwayat commit dengan tampilan grafis cabang dan label branch seperti pada contoh gambar.

---

Panduan ini hanya mencakup perintah dasar dan sering digunakan. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi Git](https://git-scm.com/docs).
