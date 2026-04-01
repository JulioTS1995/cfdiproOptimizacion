@ECHO OFF

cd C:\TSRunCFDiWEB

echo -d 5 -v %1 -dbi %2 >> salhoy.txt

C:\TSRunCFDiWEB\TSRuncfdiWEB.exe -d 5 -v %1 -dbi %2

