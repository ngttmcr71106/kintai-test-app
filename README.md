# unlock-kintai 開発環境セットアップガイド

CodeIgniter4 + TailwindCSS + MySQL の開発環境です。
Dev Containers を使用しており、**Docker Desktop は不要**です。

---

## 目次

1. [この環境の仕組み](#1-この環境の仕組み)
2. [ファイル構成](#2-ファイル構成)
3. [Mac の事前準備（Lima のセットアップ）](#3-mac-の事前準備limaのセットアップ)
4. [Windows の事前準備（WSL2 のセットアップ）](#4-windows-の事前準備wsl2-のセットアップ)
5. [セットアップ手順](#5-セットアップ手順)
6. [よく使うコマンド](#6-よく使うコマンド)
7. [DBデータについて](#7-dbデータについて)
8. [トラブルシューティング](#8-トラブルシューティング)

---

## 1. この環境の仕組み

### 全体像

```
【Mac】
macOS
└── Lima（軽量 Linux VM）
    └── Docker
        └── Dev Container（= VSCode の作業部屋 = /var/www/html）
            ├── PHP 8.4 + Apache + Composer + Node.js
            ├── MySQL 9.0
            └── phpMyAdmin

【Windows】
Windows
└── WSL2（Ubuntu 22.04）
    └── Docker
        └── Dev Container（= VSCode の作業部屋 = /var/www/html）
            ├── PHP 8.4 + Apache + Composer + Node.js
            ├── MySQL 9.0
            └── phpMyAdmin
```

### なぜ Lima / WSL2 が必要なのか

Docker はもともと **Linux 専用の技術**です。
Mac も Windows も Linux ではないため、そのままでは Docker を動かせません。

| OS | 問題 | 解決方法 |
|----|------|---------|
| Mac | macOS は Linux ではない | **Lima** で軽量 Linux VM を用意する |
| Windows | Windows は Linux ではない | **WSL2** で Linux 環境を用意する |

### Dev Container に入ると何が変わるのか

Dev Container に入ると、VSCode のターミナルが **PHP コンテナの中**になります。

```
通常のターミナル             Dev Container 内のターミナル
Mac/Windows のシェル  →    root@xxxxxxxxxx:/var/www/html#
composer が使えない   →    composer が使える
php が使えない        →    php が使える
```

---

## 2. ファイル構成

```
unlock-kintai/
├── .devcontainer/
│   └── devcontainer.json   ← Dev Container 設定
├── docker/
│   ├── php/
│   │   ├── Dockerfile       ← PHP 8.4 + Apache + Composer + Node.js
│   │   └── apache.conf      ← Apache 設定（DocumentRoot = src/public/）
│   └── mysql/
│       └── my.cnf           ← MySQL 文字コード設定（utf8mb4）
├── src/                     ← CI4 プロジェクト本体（ここで開発する）
│   ├── app/
│   ├── public/
│   ├── spark
│   └── ...
├── docker-compose.yml       ← PHP / MySQL / phpMyAdmin の定義
├── .env.example             ← .env のひな形
├── .gitattributes           ← 改行コード統一（Mac/Windows 対応）
├── .gitignore
└── README.md
```

---

## 3. Mac の事前準備（Limaのセットアップ）

> ℹ️ **Windows の方はこのセクションをスキップして [4. Windows の事前準備](#4-windows-の事前準備wsl2-のセットアップ) へ進んでください。**

---

### ステップ1：Docker Desktop をアンインストールする

すでに Docker Desktop がインストールされている場合は先に削除します。

```bash
sudo /Applications/Docker.app/Contents/MacOS/uninstall
```

削除できたか確認します。

```bash
docker version
# → "zsh: command not found: docker" と表示されればOK
```

> ℹ️ Docker Desktop が入っていない場合はこのステップをスキップしてください。

---

### ステップ2：Homebrew をインストールする

Homebrew は Mac 用のパッケージマネージャーです。すでに入っている場合はスキップしてください。

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

**Apple Silicon（M1/M2/M3/M4）の場合**、インストール後に PATH を設定します。

```bash
echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
eval "$(/opt/homebrew/bin/brew shellenv)"
```

確認します。

```bash
brew --version
# → Homebrew 4.x.x と表示されればOK
```

---

### ステップ3：Lima をインストールする

Lima は Mac 上に軽量な Linux 仮想マシンを作るツールです。

```bash
brew install lima
```

確認します。

```bash
limactl --version
# → limactl version 1.x.x と表示されればOK
```

---

### ステップ4：Linux VM（Ubuntu 22.04）を作成・起動する

Lima で Ubuntu 22.04 の仮想マシンを作成します。
**初回は 5〜10 分かかります。**

```bash
limactl create --name=kintai-dev --vm-type=vz --rosetta=false template://ubuntu-22.04
limactl start kintai-dev
```

VM が起動したか確認します。

```bash
limactl list
# NAME         STATUS
# kintai-dev   Running   ← Running になっていればOK
```

---

### ステップ5：Lima の書き込み権限を設定する

LimaのデフォルトではMacのホームディレクトリへの書き込みが制限されています。
以下の設定をしないと Dev Container 内でファイルを作成できません。

```bash
# VMを停止
limactl stop kintai-dev

# 設定ファイルを開く
code ~/.lima/kintai-dev/lima.yaml
```

ファイルの中から `mounts:` という項目を探して、`writable: false` を `writable: true` に変更します。

```yaml
# 変更前
mounts:
  - location: "~"
    writable: false

# 変更後
mounts:
  - location: "~"
    writable: true
```

保存したら VM を再起動します。

```bash
limactl start kintai-dev
```

---

### ステップ6：VM 内に Docker をインストールする

```bash
limactl shell kintai-dev -- bash -c "
  curl -fsSL https://get.docker.com | sudo sh &&
  sudo usermod -aG docker \${USER} &&
  sudo systemctl enable docker &&
  sudo systemctl start docker
"
```

---

### ステップ7：VM のソケットを Mac に公開する

```bash
# VMを停止
limactl stop kintai-dev

# 設定ファイルを開く
code ~/.lima/kintai-dev/lima.yaml
```

ファイルの**末尾**に以下を追記して保存します。

```yaml
portForwards:
  - guestSocket: "/var/run/docker.sock"
    hostSocket: "{{.Dir}}/sock/docker.sock"
```

VM を再起動してソケットが作成されたか確認します。

```bash
limactl start kintai-dev

ls ~/.lima/kintai-dev/sock/
# → docker.sock と表示されればOK
```

---

### ステップ8：Mac に docker / docker-compose CLI をインストールする

```bash
# docker CLI をインストール
brew install docker

# docker compose プラグインをインストール
brew install docker-compose
```

`docker compose` を認識させるための設定をします。

```bash
mkdir -p ~/.docker
cat > ~/.docker/config.json << 'EOF'
{
  "cliPluginsExtraDirs": [
    "/opt/homebrew/lib/docker/cli-plugins"
  ]
}
EOF
```

> ⚠️ この設定をしないと `docker compose` コマンドが認識されません。

---

### ステップ9：docker context を Lima に設定する

```bash
# Lima 用の context を作成
docker context create lima-kintai \
  --docker "host=unix://${HOME}/.lima/kintai-dev/sock/docker.sock"

# Lima 用の context をデフォルトに設定
docker context use lima-kintai
```

---

### ステップ10：動作確認

```bash
docker version
```

以下のように表示されれば Lima のセットアップは完了です。

```
Client:
 Context: lima-kintai        ← Lima 経由になっている

Server:
 Engine:
  OS/Arch: linux/arm64       ← Linux（Lima VM）で動いている
```

```bash
docker compose version
# → Docker Compose version v2.x.x と表示されればOK
```

---

## 4. Windows の事前準備（WSL2 のセットアップ）

> ℹ️ **Mac の方はこのセクションをスキップして [5. セットアップ手順](#5-セットアップ手順) へ進んでください。**

---

### ステップ1：PowerShell を管理者として起動する

1. スタートメニューで「PowerShell」を検索
2. 右クリック →「管理者として実行」

---

### ステップ2：Git をインストールする

以下の URL からインストーラーをダウンロードしてインストールしてください。
インストール時の設定はすべて**デフォルトのままでOK**です。

```
https://git-scm.com/download/win
```

インストール後、PowerShell を**一度閉じて開き直して**から確認します。

```powershell
git --version
# → git version 2.x.x と表示されればOK
```

---

### ステップ3：WSL2 と Ubuntu 22.04 をインストールする

PowerShell（管理者）で実行します。

```powershell
wsl --install --distribution Ubuntu-22.04
```

インストール完了後、**PC を再起動**してください。

---

### ステップ4：Ubuntu の初期設定をする

再起動後、以下のいずれかの方法で Ubuntu を起動します。

- スタートメニューで「Ubuntu 22.04」を検索して起動
- PowerShell で `wsl -d Ubuntu-22.04` を実行

ユーザー名とパスワードの設定を求められるので入力してください。

> ⚠️ パスワードは入力しても画面に表示されません。正常な動作です。

> ℹ️ スタートメニューに「Ubuntu 22.04」が表示されない場合は PowerShell で以下を実行してください。
> ```powershell
> wsl -d Ubuntu-22.04
> ```

---

### ステップ5：WSL2 のデフォルト設定をする

PowerShell（管理者）で実行します。

```powershell
wsl --set-default-version 2
wsl --set-default Ubuntu-22.04
```

---

### ステップ6：VSCode と拡張機能をインストールする

1. https://code.visualstudio.com/ から VSCode をインストール
   （インストール時「**PATH に追加**」のチェックを必ず入れる）
2. VSCode を起動して以下の拡張機能をインストール：
   - `ms-vscode-remote.remote-containers`（Dev Containers）
   - `ms-vscode-remote.remote-wsl`（WSL）

---

## 5. セットアップ手順

> ℹ️ Mac の方は [3. Mac の事前準備](#3-mac-の事前準備limaのセットアップ) を、Windows の方は [4. Windows の事前準備](#4-windows-の事前準備wsl2-のセットアップ) を完了させてから進んでください。

---

### ステップ1：リポジトリをクローンする

```bash
git clone https://github.com/ngttmcr71106/kintai-test-app
cd kintai-test-app
```

---

### ステップ2：VSCode で Dev Container を開く

1. VSCode で `kintai-test-app/` フォルダを開く
2. 右下のポップアップ **「Reopen in Container」** をクリック
   （または `Cmd+Shift+P` / `Ctrl+Shift+P` → `Dev Containers: Reopen in Container`）
3. **初回はビルドに 10〜20 分かかります**（Docker イメージのダウンロードとビルド）

Dev Container に入ると以下のようになります。

```
root@xxxxxxxxxx:/var/www/html#   ← この表示になればOK
```

---

### ステップ3：srcディレクトリに移動し、CI4 をインストールする

Dev Container 内のターミナル（`Ctrl+@`）で実行します。

```bash
cd src

composer install
```

---

### ステップ4：`.env` を作成する

```bash
cp env .env
```

VSCode のエクスプローラーから `src/.env` を開いて以下の内容に編集してください。

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = db
database.default.database = unlock_kintai_db
database.default.username = user
database.default.password = password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

> ⚠️ `hostname` は `localhost` ではなく `db` です。
> Docker コンテナ間の通信はサービス名（`db`）で行います。

---

### ステップ5：暗号化キーを生成する

```bash
php spark key:generate
```

実行すると `.env` の `encryption.key` に自動で値が書き込まれます。

> ⚠️ このキーは各自の `.env` で個別に生成します。Git にコミットしないでください。

---

### ステップ6：動作確認

ブラウザで以下の URL にアクセスしてください。

| URL | 内容 | 確認すること |
|-----|------|------------|
| http://localhost:8080 | CI4 アプリ | Welcome ページが表示される |
| http://localhost:8888 | phpMyAdmin | ログイン画面が表示される |

---

## 7. よく使うコマンド

### コンテナ操作（Macのターミナル または Dev Container内のターミナル）

```bash
# 起動中のコンテナ一覧を確認
docker compose ps

# コンテナを起動する
docker compose up -d

# コンテナを停止する（DBデータは保持される）
docker compose down

# コンテナのログをリアルタイムで確認（Ctrl+C で終了）
docker compose logs -f

# PHPコンテナのログだけ確認
docker compose logs -f php
```

---

### CI4（Dev Container 内のターミナルで実行）

```bash
php spark migrate            # マイグレーション実行
php spark migrate:rollback   # マイグレーションを1つ戻す
php spark db:seed MainSeeder # シーダー実行
php spark cache:clear        # キャッシュクリア
php spark routes             # ルート一覧表示
php spark key:generate       # 暗号化キー生成
```

### TailwindCSS（Dev Container 内のターミナルで実行）

```bash
npm install       # パッケージインストール（初回のみ）
npm run dev       # ファイル変更を監視してビルド
npm run build     # 本番用ビルド
```

---

## 7. DBデータについて

DB データは**名前付きボリューム**で永続化されています。

```bash
docker compose down      # ✅ コンテナ停止。DBデータは保持される
docker compose down -v   # ❌ DBデータも削除。リセットしたいときだけ使う
```

初期データは CI4 の Seeder で管理します。

```bash
php spark migrate             # テーブルを作成
php spark db:seed MainSeeder  # 初期データを投入
```

---

## 8. トラブルシューティング

### `src/` フォルダがなくてエラーになる

```
Error: mkdir /path/to/src: read-only file system
```

Dev Container 起動前に `src/` フォルダを作成してください。

```bash
mkdir -p src
```

### Lima の書き込みエラー（`Read-only file system`）

`~/.lima/kintai-dev/lima.yaml` の `mounts` 設定を確認してください。

```bash
grep -A 3 "mounts" ~/.lima/kintai-dev/lima.yaml
```

`writable: false` になっている場合は `true` に変更してVMを再起動します。

```bash
limactl stop kintai-dev
# lima.yaml の writable を true に変更後
limactl start kintai-dev
```

### `docker compose` が使えない（Mac）

```bash
brew install docker-compose

mkdir -p ~/.docker
cat > ~/.docker/config.json << 'EOF'
{
  "cliPluginsExtraDirs": [
    "/opt/homebrew/lib/docker/cli-plugins"
  ]
}
EOF

docker compose version
```

### Docker のソケットエラー（`no such file or directory`）

Lima VM を再起動するとソケットが復活します。

```bash
limactl stop kintai-dev
limactl start kintai-dev

ls ~/.lima/kintai-dev/sock/
# → docker.sock と表示されればOK
```

### DB に接続できない

- `.env` の `hostname` が `db` になっているか確認
- `kintai_db` コンテナが `healthy` になっているか確認

```bash
docker compose ps
```

### DB データをリセットしたい

```bash
docker compose down -v
docker compose up -d
php spark migrate
php spark db:seed MainSeeder
```

### Lima の VM が起動しない

```bash
# VM の状態確認
limactl list

# VM を再起動
limactl stop kintai-dev
limactl start kintai-dev

# それでもダメなら VM を作り直す
limactl delete kintai-dev
limactl create --name=kintai-dev --vm-type=vz --rosetta=false template://ubuntu-22.04
limactl start kintai-dev
```

### Dev Container のビルドが失敗する（`moby` エラー）

`php:8.4-apache` は Debian trixie ベースのため `moby` オプションが非対応です。
`devcontainer.json` に `"moby": "false"` が設定されているか確認してください。

```json
"features": {
  "ghcr.io/devcontainers/features/docker-in-docker:2": {
    "version": "latest",
    "dockerDashComposeVersion": "v2",
    "moby": "false"
  }
}
```
