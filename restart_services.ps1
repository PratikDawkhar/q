# ================================
# AUTO RESTART WINDOWS SERVICES
# Every 63 Minutes
# ================================

$services = @(
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

    # 63 Minutes Wait
    Start-Sleep -Seconds 3780

    Write-Host ""
    Write-Host "Restart Process Started at $(Get-Date)"

    foreach ($svc in $services)
    {
        try
        {
            # Check service exists
            $service = Get-Service -Name $svc -ErrorAction Stop

            Write-Host ""
            Write-Host "Processing Service : $svc"

            # Stop Service
            if ($service.Status -eq 'Running')
            {
                Write-Host "Stopping $svc ..."
                Stop-Service -Name $svc -Force -ErrorAction Stop

                # Wait until stopped
                $service.WaitForStatus('Stopped','00:00:30')

                Write-Host "$svc stopped successfully"
            }
            else
            {
                Write-Host "$svc already stopped"
            }

            # Small Delay
            Start-Sleep -Seconds 5

            # Start Service
            Write-Host "Starting $svc ..."
            Start-Service -Name $svc -ErrorAction Stop

            # Refresh status
            $service.Refresh()

            Write-Host "$svc started successfully"
        }
        catch
        {
            Write-Host "ERROR while processing $svc"
            Write-Host $_.Exception.Message
        }
    }

    Write-Host ""
    Write-Host "All Services Restart Cycle Completed at $(Get-Date)"
}