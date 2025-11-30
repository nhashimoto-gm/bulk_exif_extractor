# Bulk EXIF/メタデータ抽出ツール

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/version-1.0-green.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)
[![Language](https://img.shields.io/badge/language-HTML%2FJavaScript-orange.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)
[![Browser](https://img.shields.io/badge/browser-Chrome%20%7C%20Edge%20%7C%20Safari%20%7C%20Firefox-brightgreen.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)

## 概要

**Bulk EXIF/メタデータ抽出ツール**は、数千枚規模の画像・動画ファイルから、包括的なメタデータを一括抽出するための高性能なクライアントサイドWebアプリケーションです。WebAssemblyとJavaScriptを活用し、ブラウザ内で完結する処理により、サーバーへのアップロードを一切行わず、**完全なデータプライバシー**を実現しています。

写真家、ビデオグラファー、アーキビスト、そして大量のメディアファイルのメタデータを効率的に抽出したいすべての方に最適なツールです。

## ✨ 主な機能

### 🚀 大規模バッチ処理
- **2000枚以上のファイル**を効率的に処理し、ブラウザをフリーズさせません
- 非同期チャンク処理により、UIの応答性を維持
- リアルタイムの進捗追跡と処理速度・残り時間の表示

### 📁 フォルダサポート
- ネストされたディレクトリ構造を含むフォルダ全体のドラッグ&ドロップに対応
- 出力結果に相対ファイルパスを保持
- フォルダ選択と個別ファイル選択の両方をサポート

### 📊 包括的なメタデータ抽出

**画像（HEIC、JPEG、PNG）:**
- [ExifReader](https://github.com/mattiasw/ExifReader)を使用した全EXIFデータの抽出
- GPS座標（緯度、経度、高度）
- 日時情報
- カメラ設定（ISO、絞り、シャッタースピード）
- レンズ情報
- その他多数...

**動画（MOV、MP4）:**
- [MediaInfo.js](https://github.com/buzz/mediainfo.js)（WebAssembly）による詳細な技術メタデータ
- 映像コーデック情報
- 音声トラック詳細
- 長さ、ビットレート、フレームレート
- 解像度とアスペクト比

### 🎯 スマートフィルタリング
- 大きなバイナリブロブを自動除外し、出力JSONを軽量化
- 読み取り可能なメーカー固有データ（例：Apple MakerNote）は可能な限り保持
- GPS座標を自動的に10進数形式に変換し、地図表示を容易に

### 🔒 プライバシー第一
- **100%クライアントサイド処理** - ファイルデータがデバイス外に出ることはありません
- サーバーへのアップロード不要
- すべての処理がブラウザ内で完結

## 🛠️ 技術アーキテクチャ

### なぜクライアントサイドなのか？
数千枚の高解像度メディアファイルをサーバーで処理するには、次のような問題があります：
- **リソース集約的**でコストがかかる
- アップロード帯域幅の制限により**処理が遅い**
- センシティブなメディアデータの扱いによる**プライバシーの懸念**

本ツールは、処理をクライアントのブラウザに完全に移行することで、これらの課題を解決します。

### パフォーマンス最適化

1. **非同期チャンキング戦略**
   - ファイルを小さなバッチ（一度に5ファイル）で処理
   - 処理ループが定期的にUIスレッドに制御を譲渡
   - 大量バッチ処理時の「ページ応答なし」エラーを防止

2. **効率的なライブラリ読み込み**
   - 適切なエラーハンドリングを備えた堅牢な初期化
   - 信頼性のためにMediaInfo.js WebAssemblyモジュールをローカルホスト
   - 画像処理用ExifReader CDN統合

3. **スマートフィルタリング**
   - バイナリブロブと大容量データを除去
   - 有用で読み取り可能なメタデータのみを保持
   - 出力JSONをコンパクトで管理しやすく維持

### テクノロジースタック

- **ExifReader v4.20.0** - 優れたHEIC/JPEG/PNGメタデータ解析
- **MediaInfo.js** - WebAssemblyによる動画メタデータ抽出
- **Bootstrap 5.3** - モダンでレスポンシブなUI
- **Vanilla JavaScript** - フレームワーク依存なし

## 📦 インストール＆セットアップ

### 必要要件

- **Webサーバー**: 任意の静的ファイルサーバー（Apache、Nginx、Python `http.server`等）
  - バックエンドロジックは不要（PHP/Python/Node.jsの実行環境は不要）
- **ブラウザ**: WebAssemblyサポート付きモダンブラウザ
  - Chrome/Edge 57+
  - Firefox 52+
  - Safari 11+

### セットアップ手順

1. **リポジトリのクローン**
   ```bash
   git clone https://github.com/nhashimoto-gm/bulk_exif_extractor.git
   cd bulk_exif_extractor
   ```

2. **MediaInfo.js 依存ファイルのダウンロード**

   [MediaInfo.js GitHub](https://github.com/buzz/mediainfo.js)から以下のファイルをダウンロード：
   - `mediainfo.min.js`
   - `MediaInfoModule.wasm`

   配置先: `assets/js/lib/mediainfo/`

3. **パス設定**

   `bulk_exif_extractor.php`（188行目）の`locateFile`パスを更新：
   ```javascript
   locateFile: (path, prefix) => `/assets/js/lib/mediainfo/${path}`
   ```

   サーバーのディレクトリ構造に合わせてパスを調整してください。

4. **Webサーバーへのデプロイ**

   ファイルをWebサーバーに配置し、ブラウザでアクセスします。

   **クイックテスト（Python使用）:**
   ```bash
   python3 -m http.server 8000
   ```
   その後、`http://localhost:8000/bulk_exif_extractor.php`を開きます。

## 🎯 使い方

### 基本的なワークフロー

1. Webブラウザで**アプリケーションを開く**
2. 初期化メッセージ（「MediaInfo initialized」）を待つ
3. **入力方法を選択:**
   - ファイルまたはフォルダをドラッグ&ドロップ
   - 「フォルダを選択」ボタンをクリック
   - 「ファイルを選択」ボタンをクリック
4. リアルタイム更新で**進捗を確認**
5. 完了したら**結果をJSON形式でダウンロード**

### コントロール

- **一時停止** - 処理を一時的に停止（UIは引き続き応答）
- **再開** - 中断したところから処理を再開
- **中止** - 処理をキャンセルし、現時点までの結果をダウンロード
- **リセット** - すべてクリアして最初からやり直し

### 出力フォーマット

結果は以下の構造のJSONファイルとしてエクスポートされます：

```json
{
  "path/to/image1.jpg": {
    "Make": "Apple",
    "Model": "iPhone 13 Pro",
    "DateTime": "2024:01:15 14:30:22",
    "GPSLatitudeDecimal": 35.6762,
    "GPSLongitudeDecimal": 139.6503,
    "GPSAltitude": 42.5,
    ...
  },
  "path/to/video1.mov": {
    "media": {
      "@ref": "path/to/video1.mov",
      "track": [...]
    }
  }
}
```

### 📸 Mac/iOSユーザーへの特別な注意事項

Apple「写真」アプリを使用する場合：
- 写真ライブラリを直接選択することはできません
- **推奨ワークフロー:**
  1. 写真アプリを開く
  2. 処理したいファイル/アルバムを選択
  3. **ファイル > 書き出す > 未編集のオリジナルを書き出す**を実行
  4. 書き出したフォルダを本ツールで使用

これにより、すべてのメタデータ（GPS、撮影日時等）が確実に保持されます。

## 🔧 開発ノート

### HEIC精度の課題

多くの汎用EXIFライブラリは、HEICファイルから正確なGPS座標を抽出できません。本ツールは**ExifReader**を使用し、以下において優れた解析精度を提供します：
- HEIC（高効率画像コンテナ）
- 正確なGPS座標
- 高度データ
- タイムスタンプ情報

### MediaInfo.js統合

標準的なブラウザAPIでは、詳細な動画メタデータを読み取ることができません。**MediaInfo.js**（WebAssembly）を統合することで、以下を提供します：
- デスクトップクラスのメタデータ抽出
- MOV/MP4形式のサポート
- 詳細なコーデックおよび技術情報

### ブラウザ互換性

本アプリケーションは以下でテスト済みです：
- ✅ Chrome 90+
- ✅ Edge 90+
- ✅ Safari 14+
- ✅ Firefox 88+

## 📄 ライセンス

本プロジェクトはMITライセンスの下でライセンスされています - 詳細は[LICENSE](LICENSE)ファイルをご覧ください。

## 🤝 コントリビューション

コントリビューションを歓迎します！プルリクエストをお気軽に送信してください。

## 📝 変更履歴

### v1.0 (2025-01-30)
- 初回リリース
- HEIC、JPEG、PNG画像のサポート
- MOV、MP4動画のサポート
- 2000枚以上のファイルのバッチ処理
- リアルタイム進捗追跡
- プライバシー重視のクライアントサイド処理

## 🙏 謝辞

- [ExifReader](https://github.com/mattiasw/ExifReader) - 優れたEXIF解析ライブラリ
- [MediaInfo.js](https://github.com/buzz/mediainfo.js) - WebAssembly動画メタデータ抽出
- [Bootstrap](https://getbootstrap.com/) - UIフレームワーク

## 📧 サポート

問題、質問、提案がある場合は、GitHub上で[issueを開いて](https://github.com/nhashimoto-gm/bulk_exif_extractor/issues)ください。

---

**プライバシーを大切にする写真家とビデオグラファーのために❤️を込めて制作**
