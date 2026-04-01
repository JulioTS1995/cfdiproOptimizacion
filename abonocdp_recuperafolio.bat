@ECHO OFF

cd C:\TSRunCFDiWEB

echo "paso path1:"  %1 >> sa1.txt
echo "paso path1:"  %2 >> sa1.txt

C:\TSRunCFDiWEB\TSRuncfdiWEB.exe -d 6 -v %1 -dbi %2 -r2 1

echo "paso exe"


##-tipdoc 14 -t 4 -r %2 -v %1 -sm 0 -dbi %3
## C:\TSRunCFDiWEB\TSRuncfdiWEB.exe -d 6 -v 2631666 -dbi prueba_  -inidir 1 -r2 1

