<?php

/* 
 * JHISお薬手帳　フィールド定義
 */

$fields_lv1 = array(
    'format' => 'フォーマット', // JHIS電子お薬手帳の場合必須
    'version' => 'バージョン',
    'raw' => 'QRコードデータ',
    'user_id' => 'ユーザID', // 端末（スマホ、タブレット）が送出するID
    'data' => '薬剤情報詳細',
);

$fields_lv2 = array(
    'date_prepare' => '調剤等年月日',
    'pharmacy_name' => array(
        'grp' => '調剤_医療機関等',
        'item' => '医療機関等名称'
    ),
    'pharmacy_prefecture' => array(
        'grp' => '調剤_医療機関等',
        'item' => '医療機関都道府県'
    ),
    'pharmacy_table' => array(
        'grp' => '調剤_医療機関等',
        'item' => '医療機関点数表'
    ),
    'pharmacy_instcode' => array(
        'grp' => '調剤_医療機関等',
        'item' => '医療機関コード'
    ),
    'inst_prescript_name' => array(
        'grp' => '処方_医療機関',
        'item' => '医療機関名称'
    ),
    'inst_prescript_prefecture' => array(
        'grp' => '処方_医療機関',
        'item' => '医療機関都道府県'
    ),
    'inst_prescript_table' => array(
        'grp' => '処方_医療機関',
        'item' => '医療機関点数表'
    ),
    'inst_prescript_code' => array(
        'grp' => '処方_医療機関',
        'item' => '医療機関コード'
    ),
    'prescript_dr_name' => array(
        'grp' => '処方_医師',
        'item' => '医師氏名'
    ),
    'prescript_dr_clinic' => array(
        'grp' => '処方_医師',
        'item' => '診療科名'
    ),
    
    

);

$fields_lv3 = array(
    'drug_name' => array(
        'grp' => '薬品_用法',
        'item' => '薬品名称'
    ),
    'drug_dose' => array(
        'grp' => '薬品_用法',
        'item' => '用量'
    ),
    'drug_dose_unit' => array(
        'grp' => '薬品_用法',
        'item' => '単位名'
    ),
    'drug_id_type' => array(
        'grp' => '薬品_用法',
        'item' => '薬品コード種別'
    ),
    'drug_id' => array(
        'grp' => '薬品_用法',
        'item' => '薬品コード'
    ),
    'drug_dosage' => array(
        'grp' => '薬品_用法',
        'item' => '用法名称'
    ),
    'drug_dispense_amount' => array(
        'grp' => '薬品_用法',
        'item' => '調剤数量'
    ),
    'drug_dispense_unit' => array(
        'grp' => '薬品_用法',
        'item' => '調剤単位'
    ),
    'drug_form_code' => array(
        'grp' => '薬品_用法',
        'item' => '剤型コード'
    ),
    'drug_usage_type' => array(
        'grp' => '薬品_用法',
        'item' => '用法コード種別'
    ),
    'drug_usage_code' => array(
        'grp' => '薬品_用法',
        'item' => '用法コード'
    ),
);