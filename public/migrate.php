<?php

declare(strict_types=1);

use App\Infrastructure\Database\Migrator;

require __DIR__ . '/../bootstrap/app.php';

Migrator::run();
echo "Migrations complete.\n";
