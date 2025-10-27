# QRIS Public Generator - Flow Diagram

## 🔄 System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER ACCESS                              │
│                                                                  │
│  Browser → http://localhost:8000/qris-generator                 │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      ROUTE HANDLER                               │
│                                                                  │
│  routes/web.php                                                  │
│  Route::get('/qris-generator', QrisPublicGenerator::class)       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                   LIVEWIRE COMPONENT                             │
│                                                                  │
│  app/Livewire/QrisPublicGenerator.php                           │
│  - Mount component                                               │
│  - Load saved QRIS list                                          │
│  - Initialize f