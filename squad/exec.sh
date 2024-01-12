#!/bin/bash

exec=`cat /var/www/html/squad/exec`

case $exec in
1) echo '' > /var/www/html/squad/exec; 
	st=`pgrep SquadGameServer`;
	if [[ "$st" == "" ]]; then
		/bin/bash /home/kuadm/SquadGameServer/startserver1.sh > /var/www/html/squad/status ;
	else echo "no";
	fi
	;;
2) echo '' > /var/www/html/squad/exec; /bin/bash /home/kuadm/SquadGameServer/stopserver1.sh > /var/www/html/squad/status ;;
3) echo '' > /var/www/html/squad/exec; /bin/bash /home/kuadm/SquadGameServer/stopserver1.sh > /var/www/html/squad/status 
sleep 5
/bin/bash /home/kuadm/SquadGameServer/startserver1.sh
;;
#*) pgrep SquadGameServer > /var/www/html/squad/status ;;
esac
exit;