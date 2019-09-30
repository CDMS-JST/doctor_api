# 糖尿病アプリケーション（サーバ）
データベースとその管理用アプリケーション群（phpスクリプト）からなる。

[ベースURL https://suhtar.hospital.med.saga-u.ac.jp/doctor_api/api/](https://suhtar.hospital.med.saga-u.ac.jp/doctor_api/api/)

API|機能
---|---
store_user.php|ユニークIDを発生させ、利用者IDとした上で、利用者が、スマホの登録画面で氏名、生年月日、郵便番号、住所、電話番号を入力し、登録ボタンをタップすることにより、DBにinsert
update_userinfo.php|登録済みの利用者情報をスマホの修正画面で修正し、DBをupdate
get_userinfo.php|ユーザ情報更新のためにDBから登録済み情報を取得
store_drug.php|スマホで読み取った薬剤情報をJSONとして取得し、DBに追加。
update_latlng.php|スマホの災害時安否確認画面から現在位置情報と薬の保有状況について送信し、DBを更新。

>（APIs）※ここでAPIというのは正確な呼称ではないが、元の開発者がそのように読んでいるので踏襲
