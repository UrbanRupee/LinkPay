#!/bin/bash

# Update dashboard view
sshpass -p 'R3l/fZ#MMOWKk7n/J#OL' ssh root@72.60.100.19 "cd /www/wwwroot/Xpaisa && sed -i 's/Today.*PayIn Wallet/Current PayIn Wallet/g' resources/views/user/dashboard.blade.php"

# Update controller logic
sshpass -p 'R3l/fZ#MMOWKk7n/J#OL' ssh root@72.60.100.19 "cd /www/wwwroot/Xpaisa && sed -i 's/\$tamount = \$lists->get()->sum(function (\$item) {/\$userWallet = Wallet::where(\"userid\", user(\"userid\"))->first();\n    \$tamount = \$userWallet ? \$userWallet->payin : 0;\n    \$lists = Payment_request::where(\"userid\", user(\"userid\"))->where(\"data3\",1)->whereBetween(\"created_at\", [\$Sdate, \$Tdate])->where(\"status\",1);\n    \$tamount_old = \$lists->get()->sum(function (\$item) {/g' app/Http/Controllers/pages.php"

# Clear cache
sshpass -p 'R3l/fZ#MMOWKk7n/J#OL' ssh root@72.60.100.19 "cd /www/wwwroot/Xpaisa && php artisan cache:clear && php artisan view:clear"

echo "Server updated successfully!"

