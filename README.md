# クライミングジム向け入退館管理システム

## 概要

QRコードを利用した入退館管理システムです。

会員証のQRコードを読み取り、
入館・退館を簡単に記録できます。

---

## 開発背景

受付業務の効率化を目的として開発しました。

---

## 使用技術

- PHP 8
- Laravel 12
- MySQL
- Docker（Laravel Sail）
- Tailwind CSS
- html5-qrcode

---

## 主な機能

- ログイン機能
- 会員管理
- QRコード読取
- 入館処理
- 退館処理
- 来館履歴表示

---

## 工夫した点

- Serviceクラスによる責務分離
- QRコードによる高速受付
- 業務フローを意識した画面設計

---

## ER図

┌─────────────────────┐
│      members        │
├─────────────────────┤
│ id (PK)             │
│ member_code         │
│ barcode             │
│ last_name           │
│ first_name          │
│ birth_date          │
│ gender              │
│ phone               │
│ email               │
│ address             │
│ climbing_level      │
│ caution_flag        │
│ guardian_name       │
│ emergency_name      │
│ emergency_phone     │
│ created_at          │
│ updated_at          │
└─────────┬───────────┘
          │ 1
          │
          │ N
┌─────────▼───────────┐
│       visits        │
├─────────────────────┤
│ id (PK)             │
│ member_id (FK)      │
│ check_in_at         │
│ check_out_at        │
│ visit_type          │
│ visit_source        │
│ checked_in_by (FK)  │
│ checked_out_by(FK)  │
│ staff_note          │
│ created_at          │
│ updated_at          │
└─────────┬───────────┘
          │
          │ N
          │
          │ 1
┌─────────▼───────────┐
│       staffs        │
├─────────────────────┤
│ id (PK)             │
│ name                │
│ ...                 │
└─────────────────────┘

---

## 画面イメージ
<img width="1919" height="912" alt="image" src="https://github.com/user-attachments/assets/1121837e-892a-41eb-8585-614e1ea80cc0" />
<img width="1919" height="912" alt="image" src="https://github.com/user-attachments/assets/e7c25373-03c7-402a-bd9d-c8334237d023" />
<img width="1919" height="911" alt="image" src="https://github.com/user-attachments/assets/7aea9b8e-0b0f-4c17-a37b-827ec27b957c" />
<img width="1917" height="911" alt="image" src="https://github.com/user-attachments/assets/2eb877c4-b8d6-4473-9566-d59ed0ce24ca" />
<img width="1919" height="909" alt="image" src="https://github.com/user-attachments/assets/e280b4c6-f091-439f-a38d-174cb7f6cc78" />
<img width="1917" height="907" alt="image" src="https://github.com/user-attachments/assets/d8107c96-7e9b-4bb0-b78a-036d17190f1b" />
<img width="1919" height="911" alt="image" src="https://github.com/user-attachments/assets/a6933a7e-9ff4-4177-b204-beb451c073db" />

