# 專案運作狀態全面檢查報告
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "專案運作狀態全面檢查報告" -ForegroundColor Cyan
Write-Host "檢查時間: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Docker容器狀態
Write-Host "📦 Docker容器狀態" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
docker-compose ps
Write-Host ""

# 2. 容器資源使用情況
Write-Host "💻 容器資源使用情況" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
docker stats --no-stream --format "table {{.Name}}	{{.CPUPerc}}	{{.MemUsage}}	{{.NetIO}}	{{.BlockIO}}"
Write-Host ""

# 3. 後端API健康檢查
Write-Host "🔧 後端API健康檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green

$endpoints = @(
    @{Name = "Ping"; Url = "http://localhost/api/ping"},
    @{Name = "Health"; Url = "http://localhost/api/health"},
    @{Name = "Database Health"; Url = "http://localhost/api/health/db"}
)

foreach ($endpoint in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri $endpoint.Url -Method GET -TimeoutSec 5
        Write-Host "✅ $($endpoint.Name): HTTP $($response.StatusCode) - 成功" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ $($endpoint.Name): 連接失敗" -ForegroundColor Red
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
    Write-Host "❌ 前端服務: 連接失敗" -ForegroundColor Red
}
Write-Host ""

# 5. 端口監聽檢查
Write-Host "🔍 端口監聽檢查" -ForegroundColor Green
Write-Host "-------------------" -ForegroundColor Green
$ports = @(
    @{Port = 80; Service = "HTTP"},
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
Write-Host "檢查完成時間: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan