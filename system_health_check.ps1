# 專案運作狀態全面檢查報告
# 檢查時間: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "專案運作狀態全面檢查報告" -ForegroundColor Cyan
Write-Host "檢查時間: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Docker容器狀態
Write-Host "📦 Docker容器狀態" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    docker-compose ps
} catch {
    Write-Host "錯誤: 無法執行docker-compose命令" -ForegroundColor Red
}
Write-Host ""

# 2. 容器資源使用情況
Write-Host "💻 容器資源使用情況" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
} catch {
    Write-Host "錯誤: 無法獲取容器統計信息" -ForegroundColor Red
}
Write-Host ""

# 3. 後端API健康檢查
Write-Host "🔧 後端API健康檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green

$apiEndpoints = @(
    @{Name = "Ping"; Url = "http://localhost/api/ping"},
    @{Name = "Health"; Url = "http://localhost/api/health"},
    @{Name = "Database Health"; Url = "http://localhost/api/health/db"}
)

foreach ($endpoint in $apiEndpoints) {
    try {
        $response = Invoke-WebRequest -Uri $endpoint.Url -Method GET -TimeoutSec 5
        Write-Host "✅ $($endpoint.Name): HTTP $($response.StatusCode) - 成功" -ForegroundColor Green
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "❌ $($endpoint.Name): HTTP $statusCode - 失敗" -ForegroundColor Red
    }
}
Write-Host ""

# 4. 前端服務檢查
Write-Host "🌐 前端服務檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    $frontendResponse = Invoke-WebRequest -Uri "http://localhost:5173" -Method GET -TimeoutSec 5
    Write-Host "✅ 前端服務: HTTP $($frontendResponse.StatusCode) - 運行正常" -ForegroundColor Green
}
catch {
    $statusCode = $_.Exception.Response.StatusCode.value__
    Write-Host "❌ 前端服務: HTTP $statusCode - 連接失敗" -ForegroundColor Red
}
Write-Host ""

# 5. Nginx配置檢查
Write-Host "🚀 Nginx配置檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    $nginxTest = docker-compose exec -T nginx nginx -t 2>&1
    if ($nginxTest -match "test is successful") {
        Write-Host "✅ Nginx配置: 有效" -ForegroundColor Green
    } else {
        Write-Host "❌ Nginx配置: 存在問題" -ForegroundColor Red
    }
}
catch {
    Write-Host "⚠️  Nginx配置: 無法測試配置" -ForegroundColor Yellow
}
Write-Host ""

# 6. 數據庫連接檢查
Write-Host "🗄️ 數據庫連接檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    $dbTest = docker-compose exec -T mysql mysql -u root -proot -e "SELECT 'Database connection successful' as status;" 2>$null
    if ($dbTest -match "successful") {
        Write-Host "✅ 數據庫連接: 正常" -ForegroundColor Green
    } else {
        Write-Host "❌ 數據庫連接: 失敗" -ForegroundColor Red
    }
}
catch {
    Write-Host "❌ 數據庫連接: 無法連接" -ForegroundColor Red
}
Write-Host ""

# 7. 日誌錯誤檢查
Write-Host "⚠️ 最近錯誤日誌檢查" -ForegroundColor Yellow
Write-Host "-------------------" -ForegroundColor Yellow

Write-Host "Nginx最近錯誤:" -ForegroundColor Yellow
try {
    $nginxErrors = docker-compose logs --tail=10 nginx 2>&1 | Select-String -Pattern "error|Error|ERROR" -First 5
    if ($nginxErrors) {
        $nginxErrors | ForEach-Object { Write-Host "  ⚠️  $_" -ForegroundColor Red }
    } else {
        Write-Host "  ✅ 無錯誤日誌" -ForegroundColor Green
    }
}
catch {
    Write-Host "  ⚠️  無法獲取Nginx日誌" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "Laravel最近錯誤:" -ForegroundColor Yellow
try {
    $laravelErrors = docker-compose logs --tail=10 laravel 2>&1 | Select-String -Pattern "error|Error|ERROR|Exception" -First 5
    if ($laravelErrors) {
        $laravelErrors | ForEach-Object { Write-Host "  ⚠️  $_" -ForegroundColor Red }
    } else {
        Write-Host "  ✅ 無錯誤日誌" -ForegroundColor Green
    }
}
catch {
    Write-Host "  ⚠️  無法獲取Laravel日誌" -ForegroundColor Yellow
}
Write-Host ""

# 8. 網絡連接測試
Write-Host "🌐 網絡連接測試" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    $networkTest = docker-compose exec -T laravel sh -c "ping -c 2 nginx && ping -c 2 mysql" 2>$null
    if ($networkTest -match "0% packet loss" -and $networkTest -match "mysql.*0% packet loss") {
        Write-Host "✅ 容器網絡: 連接正常" -ForegroundColor Green
    } else {
        Write-Host "⚠️  容器網絡: 部分連接問題" -ForegroundColor Yellow
    }
}
catch {
    Write-Host "⚠️  容器網絡: 無法測試" -ForegroundColor Yellow
}
Write-Host ""

# 9. SSL證書檢查
Write-Host "🔒 SSL證書檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
try {
    $sslResponse = Invoke-WebRequest -Uri "https://localhost" -Method GET -TimeoutSec 5 -SkipCertificateCheck
    Write-Host "✅ SSL服務: 運行中 (證書檢查已跳過)" -ForegroundColor Green
}
catch {
    Write-Host "ℹ️  SSL服務: 無HTTPS服務或證書問題" -ForegroundColor Blue
}
Write-Host ""

# 10. 端口監聽檢查
Write-Host "🔍 端口監聽檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
$ports = @(
    @{Port = 80; Service = "HTTP"},
    @{Port = 443; Service = "HTTPS"},
    @{Port = 5173; Service = "Frontend"},
    @{Port = 3306; Service = "MySQL"}
)

foreach ($portInfo in $ports) {
    try {
        $tcpClient = New-Object System.Net.Sockets.TcpClient
        $tcpClient.Connect("localhost", $portInfo.Port)
        $tcpClient.Close()
        Write-Host "✅ $($portInfo.Service)端口 $($portInfo.Port): 監聽中" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ $($portInfo.Service)端口 $($portInfo.Port): 未監聽" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "檢查完成時間: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan