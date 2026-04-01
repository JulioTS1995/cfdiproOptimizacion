@ECHO OFF

cd C:\TSMIWEB

C:\TSMIWEB\TSMIWEB_CPT.exe -tipdoc 3 -t 4 -r %2 -v %1 -sm 0 -dbi %3

echo  "P1:" %1 %2 %3 >> sal.txt

