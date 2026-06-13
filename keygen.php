<?php

//demo key generator
//run once separately

$file = "secret.key";

$secret = bin2hex(random_bytes(32));

file_put_contents($file, $secret);

echo "Secret key created\n";