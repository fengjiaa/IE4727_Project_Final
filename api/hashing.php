<?php
echo "<pre>\n";
echo "Admin:  " . password_hash("admin123", PASSWORD_DEFAULT) . "\n\n";
echo "Member1: " . password_hash("member1", PASSWORD_DEFAULT) . "\n\n";
echo "Member2: " . password_hash("member2", PASSWORD_DEFAULT) . "\n\n";
echo "</pre>\n";
?>