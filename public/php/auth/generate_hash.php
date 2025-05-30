<?php
$password = 'john123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash for 'john123': " . $hash; 