<?php

return [
    'baseUrl'        => env('MTA_API_BASE_URL', 'https://api.mta.or.id/api/v1'),
    'apiToken'       => env('MTA_API_TOKEN', 'dbw_9KyI8gRtg9WMlNrRL5WrtifxkoiGcJzIFg7uhJGHCirXJYCW'),
    'timeout'        => (int) env('MTA_API_TIMEOUT', 15),
    'enabled'        => filter_var(env('MTA_API_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'perwakilanUuid' => env('MTA_PERWAKILAN_UUID', '3246792b-f0a7-48ca-95fa-379e3bee777d'),
];
