@ECHO OFF

cd C:\TSRunCFDiWEB

C:\TSRunCFDiWEB\TSRuncfdiWEB.exe -d 4 -v %1 -c 0 -dbi %2

echo "notacredito:"%1 >> sal.txt