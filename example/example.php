<?php
require __DIR__ . '/vendor/autoload.php';

$id = Tanker\Identity\create_provisional_identity("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", "test@tanker.io");
echo($id);
