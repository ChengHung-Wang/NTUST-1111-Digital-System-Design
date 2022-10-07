# Project 01
##### NTUST DSD

### 請確保您的環境已具備以下條件
- 能夠於終端機中輸入「g++」來編譯一個.c的c語言檔案，並且已更新至最新版
- 該目錄下所有檔案、目錄已具有「可讀、可寫、可執行」的權限
- 已安裝「graphviz」，且於終端機中輸入「dot」可以執行

#### 特點：
1. 支援UTF-8，經測試可使用中文、韓文、Emoji作為變量名稱或是Equation名稱
2. 精美的STDOUT表格輸出
3. 所有程式皆在單一.c檔中，單一.c檔案即可編譯，具極高的便攜性

#### 檔案說明：
- test2.pla 為測試用的pla檔案，可根據實際情況替換(本作業提供test1.pla與test2.pla作為測試檔案)
- output.out 為dot輸出檔案，可根據不同需求更改名稱（如不存在將自動創建該檔案）
- 副檔名為.c的檔案為本專案的「Ｃ語言原始碼」，編程語言為C

#### ROBDD 執行方式：
```shell
g++ ./robdd.c -w -o ./robdd
chmod +x ./robdd
./robdd test1.pla result_test1.dot
./robdd test2.pla result_test2.dot
dot -T png ./result_test1.dot > result_test1.png
dot -T png ./result_test2.dot > result_test2.png
```

#### OBDD 執行方式：
```shell
g++ ./obdd.c -w -o ./obdd
chmod +x ./obdd
./obdd test1.pla result_test1.dot
./obdd test2.pla result_test2.dot
dot -T png ./result_test1.dot > result_test1.png
dot -T png ./result_test2.dot > result_test2.png
```

