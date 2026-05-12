<?php
namespace AgriTwin\OpenAgri\Clients;

use AgriTwin\OpenAgri\Config\OpenAgriConfig;
use AgriTwin\OpenAgri\Support\Env;
use AgriTwin\OpenAgri\Support\Response;

final class GatekeeperClient extends BaseOpenAgriClient
{
    public function __construct(OpenAgriConfig $config)
    {
        parent::__construct($config, 'gatekeeper');
    }

    public function login(?string $username = null, ?string $password = null): Response
    {
        $username ??= Env::get('AGRITWIN_OPENAGRI_GATEKEEPER_USERNAME');
        $password ??= Env::get('AGRITWIN_OPENAGRI_GATEKEEPER_PASSWORD');
        if ($username === null || $password === null) {
            return new Response(false, null, 0, 'missing_gatekeeper_credentials', false, false, ['service' => 'gatekeeper']);
        }

        return $this->post('/api/login/', [
            'username' => $username,
            'password' => $password,
        ], 'gatekeeper_login', fn (array $d): bool => isset($d['access']) || isset($d['success']));
    }

    public function refresh(string $refreshToken): Response
    {
        return $this->post('/api/token/refresh/', ['refresh' => $refreshToken], 'gatekeeper_refresh', fn (array $d): bool => isset($d['access']));
    }

    public function me(): Response
    {
        return $this->get('/api/me/', [], 'gatekeeper_me');
    }

    public function serviceDirectory(): Response
    {
        return $this->get('/api/service_directory/', [], 'gatekeeper_service_directory');
    }
}

