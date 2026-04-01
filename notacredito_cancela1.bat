@ECHO OFF

cd C:\TSRunCFDiWEB

C:\TSRunCFDiWEB\TSRuncfdiWEB.exe -d 4 -v %1 -c 1 -dbi %2

REM echo "notacredito_cancela:"%1 >> sal.txt