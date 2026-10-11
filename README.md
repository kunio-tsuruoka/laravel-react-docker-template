# Laravel React Docker Template

Laravel + Inertia + React の認証付き開発テンプレートです。ログイン、登録、パスワード再設定、プロフィール編集を含みます。

## 構成

| 対象 | バージョン |
| --- | --- |
| PHP / Laravel | Docker: PHP 8.5 / Laravel 13 |
| Inertia | React adapter 3 / Laravel adapter 3 |
| React / React DOM | 19.3 |
| Tailwind CSS | 4.3（Vite plugin） |
| Vite / TypeScript | 8.3 / 7.0 |
| Node.js | 24 LTS |
| PHPUnit | 13.4 |
| PostgreSQL / Redis | 16 / 8 |

正確な依存バージョンは `src/composer.lock` と `src/package-lock.json` に固定しています。Composer は PHP 8.4.1 以上で解決し、Docker は PHP 8.5 を使用します。PostgreSQL は既存ボリュームの互換性を保つため16系を継続しています。

## 初回起動

Docker Engine / Docker Desktop と Docker Compose v2 が必要です。以下はリポジトリのルートで実行します。

```bash
cp .env.example .env
cp .env.example src/.env
docker compose build app
docker compose up -d --wait
docker compose exec app composer install
docker compose exec app npm ci
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app npm run build
```

http://localhost:8080 を開きます。ルート `.env` は Compose 用、`src/.env` は Laravel 用です。DB の接続情報は両方で一致させてください。サンプルのパスワードはローカル開発用です。

Linux でホストの UID が1000以外の場合は、ビルド時に合わせます。

```bash
LOCAL_UID=$(id -u) docker compose build app
```

ソースは `src/` から bind mount されます。この Compose 構成はローカル開発用で、本番公開には本番向けの環境変数・TLS・デプロイ構成を用意してください。

## 開発と検証

別ターミナルで Vite を起動すると HMR が使えます。画面は引き続き8080番ポートで開きます。

```bash
docker compose exec app npm run dev
```

```bash
docker compose exec app npm run typecheck
docker compose exec app npm run build
docker compose exec app composer test
docker compose exec app composer validate --strict
docker compose exec app composer check-platform-reqs
```

テストは SQLite のインメモリDBを使い、開発用 PostgreSQL のデータを変更しません。メールは標準で `src/storage/logs/laravel.log` に出力します。

CI では同じ Dockerfile をビルドし、依存関係のインストール、型チェック付きビルド、PHPテスト、PostgreSQLマイグレーション、HTTP応答を検証します。

## 旧テンプレートから更新する場合

既存の `.env` と `src/.env` は上書きせず、DB名・ユーザー名などを維持してください。Compose の固定コンテナ名を廃止し、サンプルのDB名を汎用化しています。新しいサンプルを既存DBに適用しても、DBユーザーは作り直されません。更新前に従来の Compose 構成で `docker compose down` し、DBバックアップを取得してください。`down -v` はデータを削除するため不要です。

```bash
docker compose build --pull app
docker compose up -d --wait
docker compose exec app composer install
docker compose exec app npm ci
docker compose exec app php artisan optimize:clear
docker compose exec app npm run build
docker compose exec app composer test
```

- Inertia のクライアント・サーバーを同時に3系へ更新してください。ページ設定は `src/config/inertia.php`、コンポーネントは `resources/js/Pages` です。SSRは無効です。
- Tailwind 4 は CSS 内の設定と Vite plugin を使います。旧 `tailwind.config.js` / `postcss.config.js` を使う拡張は移行が必要です。
- Inertia 3 自体がHTTP通信を提供するため、未使用の Axios 初期化を削除しています。派生アプリで `window.axios` を利用している場合は明示的に依存・初期化を追加してください。
- Laravel 13 の推奨に合わせてセッションを JSON 化しています。既存アプリへ適用すると再ログインが必要です。キャッシュへの PHP オブジェクト保存は明示的な許可が必要です。
- Web・DB・Redis・Viteの公開ポートはローカルホストに限定しています。旧構成の443番ポート公開はTLS設定がなかったため削除しています。

移行ガイド: [Laravel 13](https://laravel.com/framework/docs/13.x/upgrade) / [Inertia 3](https://inertiajs.com/docs/v3/getting-started/upgrade-guide) / [React 19](https://react.dev/blog/2024/04/25/react-19-upgrade-guide) / [Tailwind 4](https://tailwindcss.com/docs/upgrade-guide)
