# 專案執行計畫書：GoFun 管理員數據中心 (Admin Data Center)

## 1. 專案目標 (Project Objectives)
*   **獨立部署**：建立獨立於玩家前台的管理端應用，部署於 `admin.gofun.cloud`。
*   **數據驅動**：強化後端數據聚合能力，提供即時營收、玩家行為與遊戲績效報表。
*   **高效管理**：提供直觀的會員管理、帳務審核與遊戲監控介面。

## 2. 系統架構 (System Architecture)
*   **Frontend (管理端)**: Vue 3 + TypeScript + Vite + Element Plus (Admin Dashboard)
    *   **圖表庫**: ECharts 或 Chart.js (用於數據可視化)
    *   **狀態管理**: Pinia (管理 Admin Auth & Report Filters)
*   **Backend (API)**: Laravel 11 (現有專案擴充)
    *   **新增模組**: `ReportsController`, `AnalyticsService`
    *   **資料庫**: PostgreSQL (利用現有 Schema 進行聚合查詢)

## 3. 後端數據增強規劃 (Backend Data Enhancements)
為了支援高價值的數據報表，我們將在後端實作以下數據聚合接口：

### A. 營運總覽 (Dashboard Analytics)
*   **今日關鍵指標 (KPIs)**:
    *   總投注額 (Total Bet)
    *   總派彩 (Total Payout)
    *   毛營收 (GGR = Total Bet - Total Payout)
    *   今日活躍人數 (DAU)
*   **實時趨勢圖**: 每小時投注/營收走勢。

### B. 遊戲績效報表 (Game Performance)
*   **RTP 監控**: 計算各遊戲的實際 RTP (Return to Player) = `Total Payout / Total Bet`。
*   **遊戲熱度**: 各遊戲類型的遊玩場次與人數排名。

### C. 財務報表 (Financial Reports)
*   **存提分析**: 每日存款總額 vs 提款總額 (Net Cashflow)。
*   **玩家餘額總覽**: 系統總浮動籌碼 (System Float)。

### D. 玩家分析 (Player Insights)
*   **贏家/輸家排行**: 依據 GGR 排序。
*   **大額投注監控**: 過濾出高於特定金額的注單。

## 4. 前端功能規劃 (Frontend Features)

| 模組 | 功能細項 | 優先級 | 狀態 |
| :--- | :--- | :--- | :--- |
| **登入/權限** | 管理員登入、Token 管理、權限驗證 | P0 | ✅ 已完成 |
| **儀表板 (Dashboard)** | 關鍵數據卡片 (Cards)、營收趨勢折線圖 | P0 | ✅ 已完成 (整合 ECharts) |
| **會員管理** | 會員列表 (搜尋/過濾)、餘額調整、封鎖/解鎖 | P1 | ✅ 已完成 |
| **遊戲報表** | 遊戲紀錄查詢 (Game Rounds)、詳細注單檢視 (JSON Result) | P1 | ✅ 已完成 |
| **財務管理** | 充值/提現紀錄查詢、手動餘額調整 | P1 | ✅ 已完成 |
| **系統設定** | 管理員帳號管理、修改密碼 | P2 | ✅ 已完成 |
| **系統監控** | Health Check (DB/API/Nginx 狀態) | P2 | ✅ 已完成 |

## 5. 執行階段 (Execution Phases)

### Phase 1: 基礎建設與登入 (Foundation & Auth) - ✅ 完成
*   [Backend] 完善 `AdminMiddleware` 與 `AdminAuthController` (Login/Logout/Me)。 ✅
*   [Frontend] 完成 `Login.vue` 與 `Layout.vue` (側邊欄/頂部導航)。 ✅
*   [Frontend] 實作 `HealthCheck.vue` 系統監控頁面。 ✅
*   [DevOps] 確認 `admin-frontend` 本地開發環境正常運作。 ✅

### Phase 2: 會員與基礎數據 (Users & Basic Data) - ✅ 完成
*   [Backend] 實作會員列表 API (含分頁) (`AdminController::users`)。 ✅
*   [Frontend] 實作會員管理介面 (User Table) (`Users.vue`)。 ✅
*   [Frontend] 實作系統設定介面 (`Settings.vue`)。 ✅
*   [Backend] 實作會員搜尋與狀態過濾。 ✅
*   [Backend] 實作會員餘額調整與封鎖 API。 ✅
*   [Frontend] 在 User Table 加入「編輯餘額」與「封鎖」按鈕。 ✅
*   [Backend] 建立 `ReportsController`，提供基礎統計 API (Count, Sum)。 ✅
*   [Backend] 實作遊戲紀錄 API (`AdminController::gameRounds`)。 ✅
*   [Frontend] 實作遊戲紀錄介面 (`GameRounds.vue`)。 ✅
*   [Backend] 實作交易紀錄 API (`AdminController::transactions`)。 ✅
*   [Frontend] 實作交易紀錄介面 (`Transactions.vue`)。 ✅
*   [Backend/Frontend] 實作修改密碼功能。 ✅

### Phase 3: 進階數據報表 (Advanced Reporting) - ✅ 完成
*   [Backend] 編寫複雜 SQL 聚合查詢 (Group By Date/Game)。 ✅
*   [Frontend] 整合 ECharts，製作營收趨勢圖與遊戲分佈圓餅圖。 ✅
*   [Frontend] 實作詳細注單查詢頁面 (檢視 `result_data` JSON)。 ✅

## 6. 技術架構總結 (Technical Summary)

### 模組關係圖 (Module Relationship)
*   **Admin Frontend** -> **Admin API** -> **PostgreSQL** (Read/Write)
*   **Game Frontend** -> **Game API** -> **PostgreSQL** (Read/Write)
*   **Admin Panel** 與 **Game Platform** 共享同一個資料庫實例，確保數據即時一致。

### 部署配置 (Deployment)
*   **Backend**: Docker Container (PHP 8.2 + Laravel 11), Port 8000 (Mapped to 8003)
*   **Database**: Docker Container (PostgreSQL 15), Port 5432
*   **Frontend**: Vite Dev Server (Local), Port 5173 (Proxied to Backend)
