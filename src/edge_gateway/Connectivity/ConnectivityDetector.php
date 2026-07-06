<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

namespace AgriTwin\Edge\Connectivity;

final class ConnectivityDetector
{
    public function __construct(
        private bool $forceOffline = false,
        private mixed $probe = null
    ) {
    }

    public function status(): array
    {
        if ($this->forceOffline) {
            return ['online' => false, 'reason' => 'forced_offline'];
        }
        if (is_callable($this->probe)) {
            $result = ($this->probe)();
            return is_array($result) ? $result : ['online' => (bool) $result, 'reason' => (bool) $result ? 'probe_ok' : 'probe_failed'];
        }
        return ['online' => true, 'reason' => 'no_probe_configured'];
    }

    public function online(): bool
    {
        return (bool) ($this->status()['online'] ?? false);
    }
}
