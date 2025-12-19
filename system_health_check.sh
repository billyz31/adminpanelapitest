#!/bin/bash
# 創建全面的系統狀態檢查報告

echo "=== 專案運作狀態全面檢查報告 ==="
echo "檢查時間: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="
echo ""

# 1. Docker容器狀態
echo "📦 Docker容器狀態"
echo "-------------------"
docker-compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
echo ""

# 2. 容器資源使用情況
echo "💻 容器資源使用情況"
echo "-------------------"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
echo ""

# 3. 後端API健康檢查
echo "🔧 後端API健康檢查"
echo "-------------------"
echo "測試端點: /api/ping"
curl -s -w "HTTP狀態碼: %{http_code}, 響應時間: %{time_total}s\n" -o /dev/null http://localhost/api/ping
echo ""

echo "測試端點: /api/health"
curl -s -w "HTTP狀態碼: %{http_code}, 響應時間: %{time_total}s\n" -o /dev/null http://localhost/api/health
echo ""

echo "測試端點: /api/health/db"
curl -s -w "HTTP狀態碼: %{http_code}, 響應時間: %{time_total}s\n" -o /dev/null http://localhost/api/health/db
echo ""

# 4. 前端服務檢查
echo "🌐 前端服務檢查"
echo "-------------------"
echo "前端端口: 5173"
curl -s -w "HTTP狀態碼: %{http_code}, 響應時間: %{time_total}s\n" -o /dev/null http://localhost:5173
echo ""

# 5. Nginx配置檢查
echo "🚀 Nginx配置檢查"
echo "-------------------"
docker-compose exec nginx nginx -t 2>&1 | grep -E "(test|successful|failed)"
echo ""

# 6. 數據庫連接檢查
echo "🗄️ 數據庫連接檢查"
echo "-------------------"
docker-compose exec mysql mysql -u root -proot -e "SELECT 'Database connection successful' as status;" 2>/dev/null | grep -v "mysql: [Warning]"
echo ""

# 7. 日誌錯誤檢查
echo "⚠️ 最近錯誤日誌檢查"
echo "-------------------"
echo "Nginx錯誤日誌 (最近5條):"
docker-compose logs --tail=5 nginx 2>&1 | grep -i error || echo "無錯誤日誌"
echo ""

echo "Laravel錯誤日誌 (最近5條):"
docker-compose logs --tail=5 laravel 2>&1 | grep -i error || echo "無錯誤日誌"
echo ""

# 8. 網絡連接測試
echo "🌐 網絡連接測試"
echo "-------------------"
echo "容器間網絡連接:"
docker-compose exec laravel ping -c 2 nginx 2>/dev/null | grep "packet loss" || echo "無法連接到nginx"
docker-compose exec laravel ping -c 2 mysql 2>/dev/null | grep "packet loss" || echo "無法連接到mysql"
echo ""

# 9. SSL證書檢查（如果有）
echo "🔒 SSL證書檢查"
echo "-------------------"
curl -s -I https://localhost 2>/dev/null | grep -i "certificate" || echo "無SSL證書或證書檢查失敗"
echo ""

echo "=========================================="
echo "檢查完成時間: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="