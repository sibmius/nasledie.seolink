<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);

if (isset($_REQUEST['recalc'])) {
	\Nasledie\SeoLink\NAgent::Calc();
}

$sTableID = "tbl_lost"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

global $DB;
$sql = "SELECT * FROM `n_seolink_url`;";
$res = $DB->Query($sql, false, $err_mess . __LINE__);
while ($row = $res->Fetch()) {
	$URL[$row['ID']] = $row;
}


$sql = "SELECT * FROM `n_seolink_request` WHERE `ID` NOT IN (SELECT `KEY` AS `ID` FROM `n_seolink_stats` WHERE `KEY`>0 GROUP BY `KEY`) ";
if ($by != '' && $order != '') {
	$sql .= " ORDER BY " . $by . " " . $order;
}
$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);

$rsData = new CAdminResult($rsData, $sTableID);



$arH = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true,
	),
	array(
		"id" => "KEY",
		"content" => GetMessage("TBL_KEY"),
		"sort" => "TEXT",
		"default" => true,
	),
	array(
		"id" => "URL",
		"content" => GetMessage("TBL_URL"),
		"sort" => "URL",
		"default" => true,
	),
);


$lAdmin->AddHeaders($arH);
$arList = array();
while ($arRow = $rsData->Fetch()) {
	if (!isset($arList[$arRow['KEY'] . '_' . $arRow['URL']])) {

		$arList[$arRow['KEY'] . '_' . $arRow['URL']] = $arRow;
	}
}
foreach ($arList as $n => $arRow) {
	$row = & $lAdmin->AddRow($n, $arRow);

	$row->AddViewField("ID", $arRow['ID']);
	$row->AddViewField("KEY", '<a href="nasledie.seolink_url_edit.php?ID=' . $arRow['URL'] . '&lang=' . LANG . '">' . $arRow['TEXT'] . '</a>');
	$row->AddViewField("URL", $URL[$arRow['URL']]['URL']);
}

$lAdmin->AddFooter(
	array(
		array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
		array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
	)
);


$aContext = array(
	array(
		"TEXT" => GetMessage("RECALC"),
		"LINK" => "nasledie.seolink_lost.php?recalc&lang=" . LANG,
		"TITLE" => GetMessage("RECALC_TITLE"),
		"ICON" => "btn_new",
	),
);
$lAdmin->CheckListMode();


$lAdmin->AddAdminContextMenu($aContext);


$APPLICATION->SetTitle(GetMessage("LOST_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>