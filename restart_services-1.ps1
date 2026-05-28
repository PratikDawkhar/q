# =========================================
# AUTO RESTART SERVICES EVERY 63 MINUTES
# =========================================

# Stop Order
$stopServices = @(
    "MACKDAAS",
    "AMPPS_Apache"
)

# Start Order
$startServices = @(
    "AMPPS_Apache",
    "MACKDAAS"
)

while ($true)
{
    Write-Host ""
    Write-Host "======================================="
    Write-Host "Waiting for 63 minutes..."
    Write-Host "Current Time: $(Get-Date)"
    Write-Host "======================================="

    # Wait 63 mins
    Start-Sleep -Seconds 3780

    Write-Host ""
    Write-Host "Restart Process Started at $(Get-Date)"

    # =====================================
    # STOP SERVICES
    # =====================================

    foreach ($svc in $stopServices)
    {
        try
        {
            $service = Get-Service -Name $svc -ErrorAction Stop

            Write-Host ""
            Write-Host "Stopping Service : $svc"

            if ($service.Status -eq 'Running')
            {
                Stop-Service -Name $svc -Force -ErrorAction Stop

                # Wait until fully stopped
                $service.WaitForStatus('Stopped','00:00:30')

                Write-Host "$svc stopped successfully"
            }
            else
            {
                Write-Host "$svc already stopped"
            }
        }
        catch
        {
            Write-Host "ERROR stopping $svc"
            Write-Host $_.Exception.Message
        }

        # Delay between services
        Start-Sleep -Seconds 5
    }

    # =====================================
    # START SERVICES
    # =====================================

    foreach ($svc in $startServices)
    {
        try
        {
            Write-Host ""
            Write-Host "Starting Service : $svc"

            Start-Service -Name $svc -ErrorAction Stop

            Write-Host "$svc started successfully"
        }
        catch
        {
            Write-Host "ERROR starting $svc"
            Write-Host $_.Exception.Message
        }

        # Delay between services
        Start-Sleep -Seconds 5
    }

    Write-Host ""
    Write-Host "All Services Restarted Successfully at $(Get-Date)"
}