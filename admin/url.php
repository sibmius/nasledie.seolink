<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


$MODULE_ID = 'nasledie.seolink';

use \Bitrix\Main\Localization\Loc as Loc;

\Bitrix\Main\Loader::includeModule($MODULE_ID);

Loc::loadMessages(__FILE__);

$sTableID = "tbl_url"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

$FilterArr = Array(
	"find_id",
	"find_name",
	"find_url",
	"find_gurl",
	"find_lurl",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID" => $find_id,
	"NAME" => $find_name,
	"URL" => $find_url,
	"GLOBAL_URL" => $find_gurl,
	"LOCAL_URL" => $find_lurl,
);

global $DB;

$sql = "SELECT * FROM `n_seolink_url`";


if ($set_filter == 'Y') {

	$where = array();
	if ($find != '' && $find_type != '') {
		$where[] = "`" . $find_type . "` = '" . $DB->ForSql($find) . "' ";
	}

	foreach ($arFilter as $F => $V) {
		if ($V != '') {
			$where[] = "`" . $F . "` = '" . $DB->ForSql($V) . "' ";
		}
	}
	if (!empty($where)) {
		$sql .= " WHERE " . implode(' AND ', $where);
	}
}

if ($by != '' && $order != '') {
	$sql .= " ORDER BY " . $by . " " . $order;
}
$rsData = $DB->Query($sql, false, __FILE__ . " > " . __LINE__);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("URL_TITLE")));



$lAdmin->AddHeaders(array(
	array("id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true,
	),
	array("id" => "NAME",
		"content" => GetMessage("TBL_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array("id" => "URL",
		"content" => GetMessage("TBL_URL"),
		"sort" => "URL",
		"default" => true,
	),
	array("id" => "GLOBAL_URL",
		"content" => GetMessage("TBL_GURL"),
		"sort" => "GLOBAL_URL",
		"default" => true,
	),
	array("id" => "LOCAL_URL",
		"content" => GetMessage("TBL_LURL"),
		"sort" => "LOCAL_URL",
		"default" => true,
	),
));




while ($arRes = $rsData->NavNext(true, "f_")) {  // создаем строку. результат - экземпляр класса CAdminListRow
	$row = & $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("NAME", '<a href="nasledie.seolink_url_edit.php?ID=' . $f_ID . '&lang=' . LANG . '">' . $f_NAME . '</a>');

	$row->AddViewField("URL", $f_URL);
	$row->AddViewField("GLOBAL_URL", $f_GLOBAL_URL);
	$row->AddViewField("LOCAL_URL", $f_LOCAL_URL);

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => GetMessage("URL_EDIT"),
		"ACTION" => $lAdmin->ActionRedirect("nasledie.seolink_url_edit.php?ID=" . $f_ID)
	);

	$arActions[] = array(
		"ICON" => "delete",
		"TEXT" => GetMessage("URL_DEL"),
		"ACTION" => "if(confirm('" . GetMessage('DEL_CONFIRM') . "')) " . $lAdmin->ActionRedirect("nasledie.seolink_url_del.php?ID=" . $f_ID)
	);

	$row->AddActions($arActions);
}
$lAdmin->AddFooter(
	array(
		array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()), // кол-во элементов
		array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"), // счетчик выбранных элементов
	)
);

$aContext = array(
	array(
		"TEXT" => GetMessage("POST_ADD"),
		"LINK" => "nasledie.seolink_url_edit.php?lang=" . LANG,
		"TITLE" => GetMessage("POST_ADD_TITLE"),
		"ICON" => "btn_new",
	),
);
$lAdmin->CheckListMode();


$lAdmin->AddAdminContextMenu($aContext);

$APPLICATION->SetTitle(GetMessage("URL_TITLE"));


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


$oFilter = new CAdminFilter(
	$sTableID . "_filter", array(
	"ID",
	GetMessage("TBL_NAME"),
	GetMessage("TBL_URL"),
	GetMessage("TBL_GURL"),
	GetMessage("TBL_LURL"),
	)
);
?>
<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
<? $oFilter->Begin(); ?>
	<tr>
		<td><b><?= GetMessage("TBL_FIND") ?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<? echo htmlspecialchars($find) ?>">
<?
$arr = array(
	"reference" => array(
		"ID",
		GetMessage("TBL_NAME"),
		GetMessage("TBL_URL"),
		GetMessage("TBL_GURL"),
		GetMessage("TBL_LURL"),
	),
	"reference_id" => array(
		"ID",
		"NAME",
		"URL",
		"GLOBAL_URL",
		"LOCAL_URL",
	)
);
echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
?>
		</td>
	</tr>

	<tr>
		<td><?= "ID" ?>:</td>
		<td>
			<input type="text" name="find_id"  value="<? echo htmlspecialchars($find_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("TBL_NAME") . ":" ?></td>
		<td><input type="text" name="find_name"  value="<? echo htmlspecialchars($find_name) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_URL") . ":" ?></td>
		<td><input type="text" name="find_url"  value="<? echo htmlspecialchars($find_url) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_GURL") . ":" ?></td>
		<td><input type="text" name="find_gurl"  value="<? echo htmlspecialchars($find_gurl) ?>"></td>
	</tr>

	<tr>
		<td><?= GetMessage("TBL_LURL") . ":" ?></td>
		<td><input type="text" name="find_lurl"  value="<? echo htmlspecialchars($find_lurl) ?>"></td>
	</tr>

<?
$oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>