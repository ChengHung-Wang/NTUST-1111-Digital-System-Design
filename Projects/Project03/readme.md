# State Minimization
#### Digital System Design Project03

## 注意事項:
1. 需要具備root權限
2. 本資料夾所有檔案、資料夾皆應具備可讀、可寫、可執行之權限
3. 應具有php執行環境，且php版本應在7.3.6以上
4. 應安裝「graphviz」，且於終端機中輸入「dot」可以執行對應程序
5. 本專案使用php撰寫，且已經獲得教授許可

## 安裝依賴(Ubuntu)：
#### 更新安裝包:
```shell
sudo apt-get update -y
```
#### PHP
```shell
sudo apt-get install php -y
```

## 執行方式:
````shell
php B11015020.php <input_kiss_file> <output_kiss_file> <output_dot_file>
````
### Example：
````shell
php B11015020.php input.kiss outout.kiss output.dot
php B11015020.php ./tests/input1.kiss ./tests/outout1.kiss ./tests/output1.dot
php B11015020.php ./tests/input2.kiss ./tests/outout2.kiss ./tests/output2.dot
php B11015020.php ./tests/input3.kiss ./tests/outout3.kiss ./tests/output3.dot
````
##### 說明：
- input.kiss 輸入的kiss檔案。
- output.kiss 化簡後所輸出的kiss檔案，如只輸入檔名則表示將會輸出至檔案B11015020.php的同層目，如檔案不存在則創建之。
- output.dot 化簡後所輸出的dot檔案，原則與output.kiss一樣。

## 檔案、目錄說明:
- /tests: 所有的測試檔案、輸出檔案、圖片
- /DataBase: 自動創建的目錄，用於儲存資料庫運作數據
- /DB-Engine: 資料庫引擎（SleekDB）
- /Models: DataTables
- Cli.php: 用於串列輸出增強
- Configurations.php: 該專案所有的配置
- Encoder.php: 用於轉換輸出格式的類
  - KissEncoder.php: Kiss格式編碼器
  - DotEncoder.php: Dot格式編碼器
- KissParser.php: 用於處理Kiss檔案
- StateMinimization.php: State Minimization 主程序
- B11015020.php: State Minimization 入口程序