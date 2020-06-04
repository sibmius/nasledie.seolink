<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);

if (isset($_REQUEST['recalc'])) {
	\Nasledie\SeoLink\NAgent::Calc();
}

$sTableID = "tbl_stats"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

global $DB;
$sql = "SELECT * FROM `n_seolink_url`;";
$res = $DB->Query($sql, false, $err_mess . __LINE__);
while ($row = $res->Fetch()) {
	$URL[$row['ID']] = $row;
}
$sql = "SELECT * FROM `n_seolink_request`;";
$res = $DB->Query($sql, false, $err_mess . __LINE__);
while ($row = $res->Fetch()) {
	$KEY[$row['ID']] = $row;
}
$sql = "SELECT * FROM `n_seolink_group`;";
$res = $DB->Query($sql, false, $err_mess . __LINE__);
while ($row = $res->Fetch()) {
	$GROUP[$row['ID']] = $row;
}



$sql = "SELECT * FROM `n_seolink_stats`";

$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);

$rsData = new CAdminResult($rsData, $sTableID);


$sql = "SELECT `ID`,`NAME` FROM `n_seolink_group`";
$arH = array(
	array(
		"id" => "TYPE",
		"content" => GetMessage("TBL_TYPE"),
		//"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "KEY",
		"content" => GetMessage("TBL_KEY"),
		//"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "URL",
		"content" => GetMessage("TBL_URL"),
		//"sort" => "NAME",
		"default" => true,
	),
);
$res = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);
while ($row = $res->Fetch()) {
	$arH[] = array(
		"id" => $row['ID'],
		"content" => '<a href="nasledie.seolink_group_edit.php?ID=' . $row['ID'] . '&lang=' . LANG . '">' . $row['NAME'] . '</a>',
		//"sort" => "NAME",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arH);
$arList = array();
while ($arRow = $rsData->Fetch()) {
	if (!isset($arList[$arRow['KEY'] . '_' . $arRow['URL']])) {

		$arList[$arRow['KEY'] . '_' . $arRow['URL']] = array(
			'KEY' => $arRow['KEY'],
			'URL' => $arRow['URL'],
			'TYPE' => $arRow['TYPE'],
		);
	}
	$arList[$arRow['KEY'] . '_' . $arRow['URL']][$arRow['GROUP']] = $arRow['COUNT'];
}
foreach ($arList as $n => $arRow) {
	$row = & $lAdmin->AddRow($n, $arRow);
	if ($arRow['TYPE'] == 'KEY') {
		$row->AddViewField("TYPE", GetMessage('TYPE_KEY'));
		$row->AddViewField("KEY", '<a href="nasledie.seolink_url_edit.php?ID=' . $KEY[$arRow['KEY']]['URL'] . '&lang=' . LANG . '">' . $KEY[$arRow['KEY']]['TEXT'] . '</a>');
		$row->AddViewField("URL", '===');
		
	} elseif ($arRow['TYPE'] == 'URL') {
		$row->AddViewField("TYPE", GetMessage('TYPE_URL'));
		$row->AddViewField("KEY", '===');
		$row->AddViewField("URL", '<a href="nasledie.seolink_url_edit.php?ID=' . $arRow['URL'] . '&lang=' . LANG . '">' . $URL[$arRow['URL']]['URL'] . '</a>');
	}elseif ($arRow['TYPE'] == 'KU') {
		$row->AddViewField("TYPE", GetMessage('TYPE_KU'));
		$row->AddViewField("KEY", '<a href="nasledie.seolink_url_edit.php?ID=' . $arRow['URL'] . '&lang=' . LANG . '">' . $KEY[$arRow['KEY']]['TEXT'] . '</a>');
		$row->AddViewField("URL", '<a href="nasledie.seolink_url_edit.php?ID=' . $arRow['URL'] . '&lang=' . LANG . '">' . $URL[$arRow['URL']]['URL'] . '</a>');
	}

	
	
	$row->AddViewField($arRow['GROUP'], $arRow['COUNT']);
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
		"LINK" => "nasledie.seolink_stats.php?recalc&lang=" . LANG,
		"TITLE" => GetMessage("RECALC_TITLE"),
		"ICON" => "btn_new",
	),
);
$lAdmin->CheckListMode();


$lAdmin->AddAdminContextMenu($aContext);


$APPLICATION->SetTitle(GetMessage("STATIC_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>