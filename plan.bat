@echo off  

start iexplore.exe http://www.6mili.com/plan
ping -n 10 127.1>nul
taskkill /im iexplore.exe /f