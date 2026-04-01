@ECHO OFF

cd C:\TSMIWEB_XTest

echo "L1:" -tipdoc 8 -t 4 -r atprimavera_resmantto1.fr3 -dbi %1 -p1 %3 -p2 %5 -p3 %7 >> sal.txt

C:\TSMIWEB_XTest\TSMIWEB.exe -tipdoc 8 -t 4 -r atprimavera_resmantto1.fr3 -dbi %1 -p1 %3 -p2 %5 -p3 %7

