# Automotive Parts — B2B E-Commerce & ERP Platform

> **Showcase / vitrin reposu.** Otomotiv yedek parça toptancıları için geliştirdiğim,
> çok-kiracılı **B2B sipariş + cari + stok + fiyatlandırma** platformunun mimarisini ve
> seçilmiş kod örneklerini sergiler. Çalışan ürünün tamamı değildir; amaç çözülen
> mühendislik problemlerini ve kod kalitesini göstermektir. Kaynak kod özeldir
> (bkz. [LICENSE](LICENSE)).

**Stack:** PHP 8.2 · Laravel 11 · Livewire 4 · MySQL 8 · Meilisearch · Redis · Laravel Horizon · Soketi (WebSocket) · Docker

---

## TL;DR (for reviewers)

Geleneksel bir masaüstü ERP'nin modern, web tabanlı yeni nesli. Tek geliştirici olarak
**modüler (DDD esinli) bir Laravel mimarisi** ile kurdum: 20+ bağımsız modül (Catalog,
Pricing, Order, Finance, Stock, Search, Invoicing, Ocr, Legacy…), her biri kendi
Action / Service / DTO / Exception katmanlarıyla.

Bu repo bir "tutorial projesi" değil; **para hesabında kuruş hassasiyeti, gelişmiş
B2B fiyatlandırma, kredi-risk kontrolü ve <50ms full-text arama** gibi gerçek üretim
problemlerinin nasıl çözüldüğüdür.

| Ne kanıtlıyor | Nerede |
|---------------|--------|
| Finansal hassasiyet (bcmath) + kredi-risk maruziyeti hesabı | [CreditLimitChecker](code-highlights/credit-limit-checker.php) |
| Meilisearch tam-metin arama + highlight'lı autocomplete | [PartSearchService](code-highlights/part-search-meilisearch.php) |
| Modüler mimari + tam stack | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |

---

## Öne çıkan özellikler

- **Akıllı katalog & arama** — Meilisearch + Livewire ile sayfa yenilenmeden anlık arama;
  OEM kodu, parça adı, çapraz referans üzerinden; yazım hatası toleransı; araç bazlı filtre
  (Marka → Model → Motor → Yıl).
- **B2B fiyatlandırma** — Müşteri segmentine göre çok katmanlı fiyatlandırma ve indirim
  motoru; tüm para hesapları `bcmath` ile (float yok).
- **Sipariş & sepet** — Canlı slide-over sepet, otomatik iskonto önizleme, sipariş durum
  stepper'ı, gerçek-zamanlı bildirim (WebSocket).
- **Cari & finans** — Cari mizan, yaşlandırma, **dinamik kredi limiti** (mevcut risk =
  açık siparişler + bakiye), limit aşımında onay akışı; müşteri risk skorlaması.
- **AI/OCR fatura okuma** — Kağıt/PDF fatura → kalem kalem ayrıştır → stok & cari girişi.
- **E-Fatura / E-Arşiv (GİB)** — UBL-TR altyapısı.
- **Legacy köprü** — Eski masaüstü ERP ile delta senkronizasyon.
- **Roller & yetki** — Spatie Permission ile granular yetki; sistem + bayi rolleri.
- **Çok kiracılı (tenant)** + bayi self-servis portalı + PWA.

---

## Mimari

Kısa özet → [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## Notlar

- Katalogdaki marka adları (Bosch, Mann vb.) bir yedek parça bayisinin meşru sattığı
  ürün markalarıdır; nominatif/tanımlayıcı kullanımdır.
- Kaynak kodun tamamı özeldir; tam koda erişim **talep üzerine** (ör. teknik mülakat)
  sağlanabilir.

---

## İletişim

**Yavuz Selim Canpolat** · [LinkedIn](https://www.linkedin.com/in/yavuz-selim-canpolat-/) · [GitHub @yavuzscnplt](https://github.com/yavuzscnplt) · yavuz7500@gmail.com
