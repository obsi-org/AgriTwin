<?php
namespace AgriTwin\OpenAgri;

use AgriTwin\OpenAgri\Clients\FarmCalendarClient;
use AgriTwin\OpenAgri\Clients\GatekeeperClient;
use AgriTwin\OpenAgri\Clients\IrrigationServiceClient;
use AgriTwin\OpenAgri\Clients\PestDiseaseServiceClient;
use AgriTwin\OpenAgri\Clients\ReportingServiceClient;
use AgriTwin\OpenAgri\Clients\WeatherServiceClient;
use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Support\SyncQueue;

final class OpenAgriConnectorFactory
{
    public function __construct(private OpenAgriConfig $config)
    {
    }

    public static function fromEnv(): self
    {
        return new self(OpenAgriConfig::fromEnv());
    }

    public function gatekeeper(): GatekeeperClient
    {
        return new GatekeeperClient($this->config);
    }

    public function weather(): WeatherServiceClient
    {
        return new WeatherServiceClient($this->config);
    }

    public function irrigation(): IrrigationServiceClient
    {
        return new IrrigationServiceClient($this->config);
    }

    public function pestDisease(): PestDiseaseServiceClient
    {
        return new PestDiseaseServiceClient($this->config);
    }

    public function farmCalendar(): FarmCalendarClient
    {
        return new FarmCalendarClient($this->config);
    }

    public function reporting(): ReportingServiceClient
    {
        return new ReportingServiceClient($this->config);
    }

    public function syncQueue(): SyncQueue
    {
        return new SyncQueue($this->config->queueDir);
    }
}

