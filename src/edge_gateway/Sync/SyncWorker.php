<?php
namespace AgriTwin\Edge\Sync;

use AgriTwin\Edge\Connectivity\ConnectivityDetector;
use AgriTwin\Edge\Storage\DurableQueue;

final class SyncWorker
{
    public function __construct(
        private DurableQueue $queue,
        private ConnectivityDetector $connectivity,
        private CloudDssClient $cloud,
        private int $backoffSeconds = 30,
        private int $maxRetries = 3
    ) {
    }

    public function run(int $limit = 25): array
    {
        $status = $this->connectivity->status();
        if (!($status['online'] ?? false)) {
            return ['synced' => 0, 'failed' => 0, 'remaining' => $this->queue->count(), 'online' => false, 'reason' => $status['reason'] ?? 'offline'];
        }

        $synced = 0;
        $failed = 0;
        foreach (array_slice($this->queue->due(), 0, $limit) as $record) {
            $response = $this->cloud->submit($record['payload'], $record['idempotency_key']);
            if (($response['ok'] ?? false) || ($response['status'] ?? 0) === 409) {
                $this->queue->ack($record['id']);
                $synced++;
                continue;
            }
            $failed++;
            $this->queue->markFailure($record['id'], (string) ($response['error'] ?? 'sync_failed'), $this->backoffSeconds, $this->maxRetries);
        }

        return ['synced' => $synced, 'failed' => $failed, 'remaining' => $this->queue->count(), 'online' => true];
    }
}
