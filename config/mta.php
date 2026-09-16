<?php

return [
    'baseUrl'        => env('MTA_API_BASE_URL', 'https://api.mta.or.id/api/v1'),
    'apiToken'       => env('MTA_API_TOKEN', 'dbw_9KyI8gRtg9WMlNrRL5WrtifxkoiGcJzIFg7uhJGHCirXJYCW'),
    'timeout'        => (int) env('MTA_API_TIMEOUT', 15),
    'enabled'        => filter_var(env('MTA_API_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'perwakilanUuid' => env('MTA_PERWAKILAN_UUID', '0190a61a-053a-73d7-8495-2fe9b50ae338'),
];
