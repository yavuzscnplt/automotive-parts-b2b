# Mimari (üst düzey özet)

> Bu doküman, sistemin genel yaklaşımını ve tasarım ilkelerini anlatır.
> Uygulama detayları, veri modeli ve iş kuralları özeldir; talep üzerine
> (ör. teknik mülakat) paylaşılır.

## Genel yaklaşım

Laravel üzerinde, iş mantığının bağımsız **alanlara (domain)** ayrıldığı **modüler,
DDD-esinli** bir mimari. Her alan kendi servis ve veri-taşıma (DTO) katmanlarıyla izole
edilir; böylece her parça bağımsız test edilebilir, ekip büyüdüğünde çakışmadan
geliştirilebilir ve bağımlılıklar tek yönde (UI → Servis → Model) akar.

## Stack & altyapı

| Katman | Teknoloji |
|--------|-----------|
| Uygulama | PHP 8.2 · Laravel 11 · Livewire 4 |
| Veritabanı | MySQL |
| Arama | Meilisearch (full-text, typo-toleranslı) |
| Cache / kuyruk | Redis + kuyruk işçileri |
| Gerçek-zamanlılık | WebSocket |
| Paketleme | Docker |

## Tasarım ilkeleri

- **Finansal hassasiyet:** Para hesapları `float` ile değil, keyfi-hassasiyetli
  aritmetikle (bcmath) yapılır; yuvarlama hataları kabul edilmez.
- **Modülerlik:** Alanlar arası net sınırlar; her alan kendi içinde tutarlı ve
  test edilebilir.
- **Performans:** Arama, ilişkisel veritabanı yerine ona adanmış bir motora devredildi;
  ağır işler (mail, bildirim, içe aktarma) kuyruğa alınır; arayüz güncellemeleri
  gerçek-zamanlı push edilir.
- **Dayanıklılık:** Hatalar kullanıcıyı düşürmeyecek şekilde ele alınır (güvenli
  varsayılanlar, loglama).
- **Birlikte çalışabilirlik:** İşletmenin mevcut sistemlerinden kademeli geçişi
  destekleyecek şekilde tasarlandı.

---

*Daha ayrıntılı mimari ve örnek kod, mülakatta veya talep üzerine sunulabilir.*
