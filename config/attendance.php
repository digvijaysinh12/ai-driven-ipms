<?php

return [
    'allowed_ips' => array_values(array_filter(array_map(
        static fn (string $ip): string => trim($ip),
        explode(',', env('OFFICE_ALLOWED_IPS', '127.0.0.1,::1,192.168.1.*'))
    ))),
];
