# 🚤 Boatrace Open API for Hub

[![cron](https://github.com/lamrongol/BoatraceOpenAPIhub/actions/workflows/cron.yml/badge.svg)](https://github.com/lamrongol/BoatraceOpenAPIhub/actions/workflows/cron.yml)
[![pages-build-deployment](https://github.com/lamrongol/BoatraceOpenAPIhub/actions/workflows/pages/pages-build-deployment/badge.svg)](https://github.com/lamrongol/BoatraceOpenAPIhub/actions/workflows/pages/pages-build-deployment)
[![license](https://img.shields.io/badge/license-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## ⚠️ 注意事項

> ⚡ 本 API は**非公式**であり、BOATRACE 公式サイト・団体とは一切関係ありません。<br>
> 🕒 データはリアルタイム更新ではなく、**約30分間隔で更新**されます。（ GitHub Actions のスケジュールは cron.yml を参照 ）<br>
> 🔍 データの正確性・完全性を保証するものではありません。<br>
> 🙇‍♂️ 利用は自己責任でお願いします。

## 📌 概要

この API では、ボートレース（ 競艇 ）のデータをまとめて取得できます。<br>
データは GitHub Pages 上で公開されており、JSON 形式で提供されます。

## 🌐 エンドポイント

### [v3](https://github.com/lamrongol/BoatraceOpenAPIhub/tree/gh-pages/docs/v3)

```bash
https://lamrongol.github.io/BoatraceOpenAPIhub/v3/YYYY/YYYYMMDD.json
```

📅 YYYY → 年<br>
📅 YYYYMMDD → 年月日<br>
（ 日付は日本標準時 JST〔UTC+9〕基準 ）

## 🧩 サンプル

### [v3](https://github.com/lamrongol/BoatraceOpenAPIhub/tree/gh-pages/docs/v3)

- 2026年05月18日の出走表
  - [https://raw.githubusercontent.com/lamrongol/BoatraceOpenAPIhub/refs/heads/gh-pages/docs/v3/2026/20260518.json](https://raw.githubusercontent.com/lamrongol/BoatraceOpenAPIhub/refs/heads/gh-pages/docs/v3/2026/20260518.json)

## 🔗 関連リポジトリ

| 🏷️ 対象 | 📂 リポジトリ |
|:--|:--|
| 🐆 出走表 | [Boatrace Open API for Programs](https://github.com/BoatraceOpenAPI/programs) |
| ⏱️ 直前情報 | [Boatrace Open API for Previews](https://github.com/BoatraceOpenAPI/previews) |
| 🏆 結果 | [Boatrace Open API for Results](https://github.com/BoatraceOpenAPI/results) |
| オッズ | [Boatrace Open API for Results](https://github.com/lamrongol/BoatraceOdds) |

## 📄 ライセンス

Boatrace Open API for Hub は [MITライセンス](LICENSE) の元で公開されています。
