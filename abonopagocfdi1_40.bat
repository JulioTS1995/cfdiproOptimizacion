@ECHO OFF

cd C:\TSRunCFDiWEB40


C:\TSRunCFDiWEB40\TSRuncfdiWEB.exe -d 6 -v %1 -dbi %2

# -d 2 -v 3432062 -dbi Logistic24H_

echo "-v" %1 "-dbi" %2 >> abonopagocfdi1bat.log